<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

class TestEmailConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email configuration by sending a test email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info("Testing email configuration...");
        $this->info("Sending test email to: {$email}");
        
        try {
            Mail::raw('This is a test email from iSalesBook Laravel application.', function($message) use ($email) {
                $message->to($email)
                        ->subject('iSalesBook - Email Configuration Test')
                        ->from(config('mail.from.address'), config('mail.from.name'));
            });
            
            $this->info('✅ Email sent successfully!');
            $this->info('Check your inbox for the test email.');
            
        } catch (\Exception $e) {
            $this->error('❌ Email sending failed:');
            $this->error($e->getMessage());
            
            $this->info('Current mail configuration:');
            $this->info('Host: ' . config('mail.mailers.smtp.host'));
            $this->info('Port: ' . config('mail.mailers.smtp.port'));
            $this->info('Encryption: ' . config('mail.mailers.smtp.encryption'));
            $this->info('Username: ' . config('mail.mailers.smtp.username'));
        }
    }
} 