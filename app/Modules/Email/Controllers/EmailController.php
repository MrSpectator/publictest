<?php

namespace App\Modules\Email\Controllers;

use App\Modules\Email\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * @OA\Tag(
 *     name="Email",
 *     description="Email sending and management"
 * )
 */
class EmailController extends Controller
{
    protected EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * @OA\Post(
     *     path="/api/email/send",
     *     summary="Send an email",
     *     tags={"Email"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"to", "subject", "body"},
     *                 @OA\Property(property="to", type="string", format="email", example="recipient@example.com"),
     *                 @OA\Property(property="subject", type="string", example="Test Email Subject"),
     *                 @OA\Property(property="body", type="string", example="This is the email body content"),
     *                 @OA\Property(property="cc", type="string", example="cc1@example.com,cc2@example.com", description="Comma-separated email addresses"),
     *                 @OA\Property(property="bcc", type="string", example="bcc1@example.com,bcc2@example.com", description="Comma-separated email addresses"),
     *                 @OA\Property(property="attachments", type="array", @OA\Items(type="string", format="binary"), description="Email attachments")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="from_email", type="string", example="sender@example.com"),
     *             @OA\Property(property="to_email", type="string", example="recipient@example.com"),
     *             @OA\Property(property="subject", type="string", example="Test Email Subject"),
     *             @OA\Property(property="status", type="string", example="sent"),
     *             @OA\Property(property="created_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Email sending failed"
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/email/logs",
     *     summary="Get email logs",
     *     tags={"Email"},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending", "sent", "failed"})
     *     ),
     *     @OA\Parameter(
     *         name="to_email",
     *         in="query",
     *         description="Filter by recipient email",
     *         required=false,
     *         @OA\Schema(type="string", format="email")
     *     ),
     *     @OA\Parameter(
     *         name="from_date",
     *         in="query",
     *         description="Filter by start date",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="to_date",
     *         in="query",
     *         description="Filter by end date",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email logs retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="from_email", type="string", example="sender@example.com"),
     *                 @OA\Property(property="to_email", type="string", example="recipient@example.com"),
     *                 @OA\Property(property="subject", type="string", example="Test Email Subject"),
     *                 @OA\Property(property="status", type="string", example="sent"),
     *                 @OA\Property(property="attempts", type="integer", example=1),
     *                 @OA\Property(property="sent_at", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="error_message", type="string", nullable=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             )
     *         )
     *     )
     * )
     */
    public function logs(Request $request)
    {
        $logs = $this->emailService->getEmailLogs($request->all());
        return response()->json($logs);
    }
} 