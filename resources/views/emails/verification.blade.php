<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to iSalesBook - </title>
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
        .welcome-section {
            text-align: center;
            margin-bottom: 30px;
        }
        .welcome-section h2 {
            color: #DA612B;
            margin-bottom: 10px;
            font-size: 24px;
        }
        .org-code-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 2px solid #DA612B;
            border-radius: 10px;
            padding: 25px;
            text-align: center;
            margin: 30px 0;
        }
        .org-code-label {
            font-size: 16px;
            color: #666;
            margin-bottom: 10px;
            font-weight: 500;
        }
        .org-code {
            font-size: 32px;
            font-weight: bold;
            color: #DA612B;
            letter-spacing: 3px;
            background: white;
            padding: 15px 25px;
            border-radius: 8px;
            display: inline-block;
            border: 2px dashed #DA612B;
            margin: 10px 0;
        }
        .org-name {
            font-size: 18px;
            color: #333;
            margin-top: 10px;
            font-weight: 600;
        }
        .verification-section {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 25px;
            margin: 30px 0;
            text-align: center;
        }
        .verification-section h3 {
            color: #DA612B;
            margin-bottom: 15px;
            font-size: 20px;
        }
        .verify-button {
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
        .verify-button:hover {
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
            .org-code {
                font-size: 24px;
                padding: 12px 20px;
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
            <h1>🎉 Welcome to iSalesBook!</h1>
            <p>Your complete sales management solution</p>
        </div>

        <div class="content">
            <div class="welcome-section">
                <h2>Hello {{ $user->first_name ?? $user->email }}!</h2>
                <p>Thank you for registering with iSalesBook. Your account has been created successfully!</p>
            </div>

            <!-- Organization Code Section -->
            <div class="org-code-section">
                <div class="org-code-label">Your Organization Code</div>
                <div class="org-code">{{ $organization->code }}</div>
                <div class="org-name">{{ $organization->name }}</div>
                <p style="margin-top: 15px; color: #666; font-size: 14px;">
                    <strong>Important:</strong> Save this code! You'll need it to log in to your account.
                </p>
            </div>

            <!-- User Details -->
            <div class="user-details">
                <h4>Account Details</h4>
                <div class="detail-row">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value">{{ $user->email }}</span>
                </div>
                @if($user->first_name)
                <div class="detail-row">
                    <span class="detail-label">Name:</span>
                    <span class="detail-value">{{ $user->first_name }} {{ $user->last_name }}</span>
                </div>
                @endif
                @if($user->phone_number)
                <div class="detail-row">
                    <span class="detail-label">Phone:</span>
                    <span class="detail-value">{{ $user->phone_number }}</span>
                </div>
                @endif
                <div class="detail-row">
                    <span class="detail-label">Account Type:</span>
                    <span class="detail-value">{{ $user->type == 1 ? 'Individual' : 'Company' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Registration Date:</span>
                    <span class="detail-value">{{ $user->created_at->format('M d, Y') }}</span>
                </div>
            </div>

            <!-- Important Information -->
            <div class="info-section">
                <h4>📋 What's Next?</h4>
                <ul>
                    <li><strong>Log in</strong> using your email and organization code</li>
                    <li><strong>Complete your profile</strong> with additional information</li>
                    <li><strong>Start managing</strong> your sales and customers</li>
                </ul>
            </div>
        </div>

        <div class="footer">
            <p><strong>iSalesBook</strong> - Your complete sales management solution</p>
            <p>If you didn't create this account, please ignore this email.</p>
            <p>Need help? Contact us at <a href="mailto:info@isalesbook.com" style="color: #DA612B;">support@isalesbook.com</a></p>
        </div>
    </div>
</body>
</html> 
