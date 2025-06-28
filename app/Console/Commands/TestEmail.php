<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TestEmail extends Command
{
    protected $signature = 'email:test {email}';
    protected $description = 'Test email configuration by sending a test email';

    public function handle()
    {
        $testEmail = $this->argument('email');
        
        $this->info("Testing email configuration...");
        $this->info("Sending test email to: {$testEmail}");
        
        try {
            Mail::raw('This is a test email from your Laravel application. If you receive this, your email configuration is working correctly!', function ($message) use ($testEmail) {
                $message->to($testEmail)
                        ->subject('Test Email - Laravel Application')
                        ->from(config('mail.from.address'), config('mail.from.name'));
            });
            
            $this->info('✅ Test email sent successfully!');
            $this->info('Check your inbox (and spam folder) for the test email.');
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to send test email:');
            $this->error($e->getMessage());
            
            Log::error('Email test failed', [
                'error' => $e->getMessage(),
                'email' => $testEmail
            ]);
            
            $this->info('');
            $this->info('Troubleshooting tips:');
            $this->info('1. Check your .env file email settings');
            $this->info('2. Verify your email credentials');
            $this->info('3. Check if your email provider requires app passwords');
            $this->info('4. Try a different port (587 or 465)');
            $this->info('5. Check your firewall/network settings');
        }
    }
} 