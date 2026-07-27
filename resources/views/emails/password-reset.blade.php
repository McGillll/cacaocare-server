<!DOCTYPE html>
<html>
<head>
    <title>Password Reset</title>
    <meta charset="UTF-8">
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #F7F1DE;
            font-family: 'Segoe UI', Tahoma, sans-serif;
        }

        .email-container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #FDFBF6;
            border: 1px solid #EFE4C3;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 8px 20px rgba(57, 36, 20, 0.08);
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo img {
            width: 120px;
            border-radius: 8px;
        }

        h1 {
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 24px;
            color: #1E0D06;
            margin-bottom: 16px;
            text-align: center;
        }

        p {
            font-size: 16px;
            color: #632F19;
            line-height: 1.6;
            margin-bottom: 20px;
            text-align: center;
        }

        .btn {
            display: block;
            width: fit-content;
            margin: 0 auto;
            background-color: #9D6638;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 8px;
        }

        .btn span {
            color: #FDFBF6 !important;
            font-size: 16px;
            font-weight: 600;
        }

        .alt-link {
            margin-top: 30px;
            text-align: center;
            font-size: 14px;
            color: #7A4327;
        }

        .alt-link a {
            color: #85552F;
            word-break: break-all;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #A0843D;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="logo">
            <img src="{{ $message->embed(public_path('images/cacao_care_logo1.jpg')) }}" alt="CacaoCare Logo">
        </div>
        <h1>Reset Your Password</h1>
        <p>You are receiving this email because we received a password reset request for your account. Click the button below to reset your password.</p>
        <a href="{{ $url }}" class="btn" style="color:#FDFBF6 !important;"><span>Reset Password</span></a>

        <div class="alt-link">
            If the button above doesn't work, copy and paste this link into your browser:
            <br>
            <a href="{{ $url }}">{{ $url }}</a>
        </div>

        <p style="text-align: center; margin-top: 20px;">If you did not request a password reset, no further action is required.</p>

        <div class="footer">
            © {{ date('Y') }} CacaoCare. All rights reserved.
        </div>
    </div>
</body>
</html>
