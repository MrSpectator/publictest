<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your iSalesBook Password</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #DA612B 0%, #FF8C42 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .header p {
            margin: 10px 0 0 0;
            font-size: 16px;
            opacity: 0.9;
        }
        .content {
            padding: 40px 30px;
        }
        .reset-section {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 25px;
            margin: 30px 0;
            text-align: center;
        }
        .reset-section h3 {
            color: #DA612B;
            margin-bottom: 15px;
            font-size: 20px;
        }
        .reset-button {
            display: inline-block;
            background: linear-gradient(135deg, #DA612B 0%, #FF8C42 100%);
            color: white;
            text-decoration: none;
            padding: 15px 30px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 16px;
            margin: 15px 0;
            transition: transform 0.2s ease;
        }
        .reset-button:hover {
            transform: translateY(-2px);
        }
        .info-section {
            background-color: #e8f4fd;
            border-left: 4px solid #DA612B;
            padding: 20px;
            margin: 25px 0;
            border-radius: 0 8px 8px 0;
        }
        .info-section h4 {
            color: #DA612B;
            margin: 0 0 10px 0;
            font-size: 18px;
        }
        .info-section ul {
            margin: 0;
            padding-left: 20px;
        }
        .info-section li {
            margin-bottom: 5px;
            color: #555;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 25px 30px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }
        .footer p {
            margin: 5px 0;
            color: #666;
            font-size: 14px;
        }
        .expires-notice {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
            color: #856404;
        }
        .user-details {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .user-details h4 {
            color: #DA612B;
            margin: 0 0 15px 0;
            font-size: 18px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding: 5px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .detail-label {
            font-weight: 600;
            color: #555;
        }
        .detail-value {
            color: #333;
        }
        @media (max-width: 600px) {
            .container {
                margin: 10px;
                border-radius: 8px;
            }
            .header, .content, .footer {
                padding: 20px;
            }
            .detail-row {
                flex-direction: column;
            }
            .detail-value {
                margin-top: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Password Reset Request</h1>
            <p>iSalesBook Account Security</p>
        </div>
        
        <div class="content">
            <div class="user-details">
                <h4>Account Information</h4>
                <div class="detail-row">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value">{{ $user->email }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Name:</span>
                    <span class="detail-value">{{ $user->first_name ?? $user->company_name ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Account Type:</span>
                    <span class="detail-value">{{ $user->type == 1 ? 'Individual' : 'Company' }}</span>
                </div>
            </div>

            <div class="reset-section">
                <h3>Reset Your Password</h3>
                <p>We received a request to reset your password for your iSalesBook account. Click the button below to create a new password:</p>
                
                <a href="{{ $reset_url }}" class="reset-button">Reset Password</a>
                
                <p style="margin-top: 20px; font-size: 14px; color: #666;">
                    If the button doesn't work, copy and paste this link into your browser:<br>
                    <a href="{{ $reset_url }}" style="color: #DA612B; word-break: break-all;">{{ $reset_url }}</a>
                </p>
            </div>

            <div class="expires-notice">
                <strong>⚠️ Important:</strong> This password reset link will expire on {{ $expires_at }}. 
                If you don't reset your password by then, you'll need to request a new link.
            </div>

            <div class="info-section">
                <h4>Security Tips</h4>
                <ul>
                    <li>Choose a strong password with at least 8 characters</li>
                    <li>Include a mix of uppercase, lowercase, numbers, and symbols</li>
                    <li>Don't use the same password for multiple accounts</li>
                    <li>Never share your password with anyone</li>
                    <li>If you didn't request this reset, please ignore this email</li>
                </ul>
            </div>

            <div class="info-section">
                <h4>Need Help?</h4>
                <p>If you're having trouble resetting your password or have any questions, please contact our support team at <a href="mailto:support@isalesbook.com" style="color: #DA612B;">support@isalesbook.com</a>.</p>
            </div>
        </div>
        
        <div class="footer">
            <p><strong>iSalesBook</strong> - Your Complete Sales Management Solution</p>
            <p>This email was sent to {{ $user->email }} because a password reset was requested for your account.</p>
            <p>If you didn't request this reset, please ignore this email and your password will remain unchanged.</p>
            <p style="margin-top: 20px; font-size: 12px; color: #999;">
                © {{ date('Y') }} iSalesBook. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html> 