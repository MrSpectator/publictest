<?php

namespace App\Modules\Email\Controllers;

use App\Modules\Email\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class EmailController extends Controller
{
    protected EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    // POST /api/email/send
    public function send(Request $request)
    {
        $validated = $request->validate([
            'to' => 'required|email',
            'subject' => 'required|string',
            'body' => 'required|string',
            'cc' => 'nullable|string',
            'bcc' => 'nullable|string',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx,txt|max:10240',
        ]);
        $data = $validated;
        $data['attachments'] = $request->file('attachments', []);
        $log = $this->emailService->sendEmail($data);
        return response()->json($log);
    }

    // GET /api/email/logs
    public function logs(Request $request)
    {
        $logs = $this->emailService->getEmailLogs($request->all());
        return response()->json($logs);
    }
} 