<?php

namespace App\Modules\Email\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="EmailLog",
 *     title="Email Log",
 *     description="Email log entry model"
 * )
 */
class EmailLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_email',
        'to_email',
        'cc',
        'bcc',
        'subject',
        'body',
        'status',
        'attempts',
        'sent_at',
        'error_message',
        'attachments',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'cc' => 'array',
        'bcc' => 'array',
        'attachments' => 'array',
    ];

    /**
     * @OA\Property(property="id", type="integer", example=1)
     * @OA\Property(property="from_email", type="string", example="sender@example.com")
     * @OA\Property(property="to_email", type="string", example="recipient@example.com")
     * @OA\Property(property="cc", type="array", @OA\Items(type="string"), example={"cc1@example.com", "cc2@example.com"})
     * @OA\Property(property="bcc", type="array", @OA\Items(type="string"), example={"bcc1@example.com", "bcc2@example.com"})
     * @OA\Property(property="subject", type="string", example="Test Email Subject")
     * @OA\Property(property="body", type="string", example="This is the email body content")
     * @OA\Property(property="status", type="string", example="sent", enum={"pending", "sent", "failed"})
     * @OA\Property(property="attempts", type="integer", example=1)
     * @OA\Property(property="sent_at", type="string", format="date-time", example="2024-01-01T12:00:00Z")
     * @OA\Property(property="error_message", type="string", nullable=true, example="Connection timeout")
     * @OA\Property(property="attachments", type="array", @OA\Items(type="string"), example={"/storage/attachments/file1.pdf"})
     * @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T12:00:00Z")
     * @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T12:00:00Z")
     */
} 