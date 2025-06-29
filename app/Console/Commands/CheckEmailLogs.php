<?php

namespace App\Console\Commands;

use App\Modules\Email\Models\EmailLog;
use Illuminate\Console\Command;

class CheckEmailLogs extends Command
{
    protected $signature = 'email:check-logs';
    protected $description = 'Check email logs to debug email sending issues';

    public function handle()
    {
        $this->info('Checking email logs...');
        
        $totalLogs = EmailLog::count();
        $this->info("Total email logs: {$totalLogs}");
        
        if ($totalLogs > 0) {
            $this->info('Recent email logs:');
            $logs = EmailLog::latest()->take(10)->get();
            
            foreach ($logs as $log) {
                $this->line("ID: {$log->id} | To: {$log->to_email} | Subject: {$log->subject} | Status: {$log->status} | Created: {$log->created_at}");
                if ($log->status === 'failed') {
                    $this->error("Error: {$log->error_message}");
                }
            }
            
            $pendingCount = EmailLog::where('status', 'pending')->count();
            $sentCount = EmailLog::where('status', 'sent')->count();
            $failedCount = EmailLog::where('status', 'failed')->count();
            
            $this->info("Status breakdown:");
            $this->line("Pending: {$pendingCount}");
            $this->line("Sent: {$sentCount}");
            $this->line("Failed: {$failedCount}");
        } else {
            $this->warn('No email logs found. This might indicate that emails are not being created.');
        }
        
        return 0;
    }
} 