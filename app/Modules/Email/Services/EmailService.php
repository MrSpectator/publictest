<?php

namespace App\Modules\Email\Services;

use App\Modules\Email\Models\EmailLog;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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

        // Create email log entry
        $emailLog = EmailLog::create([
            'from_email' => $emailData['from'] ?? config('mail.from.address'),
            'to_email' => $emailData['to'],
            'cc' => $emailData['cc'] ?? [],
            'bcc' => $emailData['bcc'] ?? [],
            'subject' => $emailData['subject'],
            'body' => $emailData['body'],
            'status' => 'pending',
            'attempts' => 0,
            'attachments' => $attachments,
        ]);

        try {
            // Send email using Laravel's Mail facade
            Mail::send([], [], function ($message) use ($emailData, $attachments) {
                $message->to($emailData['to'])
                    ->subject($emailData['subject'])
                    ->setBody($emailData['body'], 'text/html');

                if (!empty($emailData['cc'])) {
                    $message->cc($emailData['cc']);
                }

                if (!empty($emailData['bcc'])) {
                    $message->bcc($emailData['bcc']);
                }

                // Attach files
                foreach ($attachments as $fileUrl) {
                    $filePath = Storage::disk('public')->path(str_replace('/storage/', '', parse_url($fileUrl, PHP_URL_PATH)));
                    $message->attach($filePath);
                }
            });

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

        } catch (\Exception $e) {
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
                        ->setBody($emailLog->body, 'text/html');

                    if (!empty($emailLog->cc)) {
                        $message->cc($emailLog->cc);
                    }

                    if (!empty($emailLog->bcc)) {
                        $message->bcc($emailLog->bcc);
                    }

                    // Attach files
                    if (!empty($emailLog->attachments)) {
                        foreach ($emailLog->attachments as $fileUrl) {
                            $filePath = Storage::disk('public')->path(str_replace('/storage/', '', parse_url($fileUrl, PHP_URL_PATH)));
                            $message->attach($filePath);
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