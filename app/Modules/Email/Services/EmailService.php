<?php

namespace App\Modules\Email\Services;

use App\Modules\Email\Models\EmailLog;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class EmailService
{
    /**
     * Send an email and log the attempt
     *
     * @param array $emailData
     * @return EmailLog
     */
    public function sendEmail(array $emailData): EmailLog
    {
        // Handle attachments upload if present
        $attachments = [];
        if (!empty($emailData['attachments'])) {
            foreach ($emailData['attachments'] as $file) {
                if ($file->isValid()) {
                    $path = $file->store('attachments', 'public');
                    $attachments[] = Storage::disk('public')->url($path);
                }
            }
        }

        // Prepare cc and bcc fields
        $cc = $this->prepareRecipients($emailData['cc'] ?? null);
        $bcc = $this->prepareRecipients($emailData['bcc'] ?? null);

        // Create email log entry
        $emailLog = EmailLog::create([
            'from_email' => $emailData['from'] ?? config('mail.from.address'),
            'to_email' => $emailData['to'],
            'cc' => $cc,
            'bcc' => $bcc,
            'subject' => $emailData['subject'],
            'body' => $emailData['body'],
            'status' => 'pending',
            'attempts' => 0,
            'attachments' => $attachments,
        ]);

        try {
            // Send email using PHPMailer
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = config('mail.mailers.smtp.host');
            $mail->SMTPAuth = true;
            $mail->Username = config('mail.mailers.smtp.username');
            $mail->Password = config('mail.mailers.smtp.password');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = config('mail.mailers.smtp.port', 587);

            $mail->setFrom($emailData['from'] ?? config('mail.from.address'), config('mail.from.name'));
            $mail->addAddress($emailData['to']);
            $mail->Subject = $emailData['subject'];
            $mail->isHTML(true);
            $mail->Body = $emailData['body'];

            // Add CC
            if (!empty($cc)) {
                foreach ($cc as $ccEmail) {
                    $mail->addCC($ccEmail);
                }
            }
            // Add BCC
            if (!empty($bcc)) {
                foreach ($bcc as $bccEmail) {
                    $mail->addBCC($bccEmail);
                }
            }
            // Attach files
            if (!empty($attachments)) {
                foreach ($attachments as $fileUrl) {
                    $relativePath = str_replace(Storage::disk('public')->url(''), '', $fileUrl);
                    $filePath = Storage::disk('public')->path($relativePath);
                    if (file_exists($filePath)) {
                        $mail->addAttachment($filePath);
                    }
                }
            }
            $mail->send();

            // Update log with success
            $emailLog->update([
                'status' => 'sent',
                'attempts' => $emailLog->attempts + 1,
                'sent_at' => now(),
            ]);

            Log::info('Email sent successfully', [
                'email_log_id' => $emailLog->id,
                'to' => $emailData['to'],
                'subject' => $emailData['subject']
            ]);
        } catch (PHPMailerException $e) {
            // Update log with failure
            $emailLog->update([
                'status' => 'failed',
                'attempts' => $emailLog->attempts + 1,
                'error_message' => $e->getMessage(),
            ]);

            Log::error('Email sending failed', [
                'email_log_id' => $emailLog->id,
                'error' => $e->getMessage(),
                'to' => $emailData['to'],
                'subject' => $emailData['subject']
            ]);
        }

        return $emailLog->fresh();
    }

    /**
     * Prepare recipients by converting string to array if necessary
     *
     * @param string|array|null $recipients
     * @return array
     */
    private function prepareRecipients($recipients): array
    {
        if (is_string($recipients)) {
            // Split by comma and trim whitespace
            return array_filter(array_map('trim', explode(',', $recipients)));
        }
        
        if (is_array($recipients)) {
            return $recipients;
        }

        return [];
    }

    /**
     * Get email logs with optional filtering
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getEmailLogs(array $filters = [])
    {
        $query = EmailLog::query();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['to_email'])) {
            $query->where('to_email', 'like', '%' . $filters['to_email'] . '%');
        }

        if (isset($filters['from_date'])) {
            $query->where('created_at', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date'])) {
            $query->where('created_at', '<=', $filters['to_date']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Retry failed emails
     *
     * @param int $maxAttempts
     * @return int
     */
    public function retryFailedEmails(int $maxAttempts = 3): int
    {
        $failedEmails = EmailLog::where('status', 'failed')
            ->where('attempts', '<', $maxAttempts)
            ->get();

        $retriedCount = 0;

        foreach ($failedEmails as $emailLog) {
            try {
                Mail::send([], [], function ($message) use ($emailLog) {
                    $message->to($emailLog->to_email)
                        ->subject($emailLog->subject)
                        ->html($emailLog->body);

                    if (!empty($emailLog->cc)) {
                        $message->cc($emailLog->cc);
                    }

                    if (!empty($emailLog->bcc)) {
                        $message->bcc($emailLog->bcc);
                    }

                    // Attach files
                    if (!empty($emailLog->attachments)) {
                        foreach ($emailLog->attachments as $fileUrl) {
                            // Get the relative path from the URL
                            $relativePath = str_replace(Storage::disk('public')->url(''), '', $fileUrl);
                            $filePath = Storage::disk('public')->path($relativePath);
                            if (file_exists($filePath)) {
                                $message->attach($filePath);
                            }
                        }
                    }
                });

                $emailLog->update([
                    'status' => 'sent',
                    'attempts' => $emailLog->attempts + 1,
                    'sent_at' => now(),
                    'error_message' => null,
                ]);

                $retriedCount++;
            } catch (\Exception $e) {
                $emailLog->update([
                    'attempts' => $emailLog->attempts + 1,
                    'error_message' => $e->getMessage(),
                ]);
            }
        }

        return $retriedCount;
    }
} 