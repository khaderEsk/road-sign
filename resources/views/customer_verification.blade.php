<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفعيل حساب العميل</title>
    <style>
        /* أنماط عامة */
        body {
            font-family: 'Tajawal', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        /* بطاقة البريد الإلكتروني */
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .header {
            background: #2563eb;
            padding: 20px;
            text-align: center;
            position: relative;
        }

        .logo-container {
            display: flex;
            justify-content: center;
            margin-bottom: 15px;
        }

        .logo {
            max-height: 80px;
            max-width: 200px;
            height: auto;
        }

        .header h1 {
            color: white;
            margin: 0;
            font-size: 24px;
            padding-top: 10px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            margin-top: 15px;
            display: inline-block;
        }

        .content {
            padding: 30px;
        }

        /* أنماط النص */
        h2 {
            color: #2563eb;
            margin-top: 0;
            font-size: 22px;
        }

        p {
            margin-bottom: 15px;
            font-size: 16px;
            color: #444;
        }

        /* كود OTP */
        .otp-code {
            display: inline-block;
            background: #f0f7ff;
            color: #2563eb;
            font-size: 28px;
            font-weight: bold;
            padding: 15px 25px;
            margin: 20px 0;
            border-radius: 6px;
            border: 2px dashed #2563eb;
            letter-spacing: 3px;
        }

        /* التذييل */
        .footer {
            background: #f0f0f0;
            padding: 15px;
            text-align: center;
            font-size: 14px;
            color: #666;
        }

        /* التجاوب مع الأجهزة الصغيرة */
        @media only screen and (max-width: 600px) {
            .content {
                padding: 20px;
            }

            .logo {
                max-height: 60px;
            }

            h2 {
                font-size: 20px;
            }

            .otp-code {
                font-size: 24px;
                padding: 12px 20px;
            }
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="header">
            <div class="logo-container">
            </div>
            <h1>تفعيل الحساب</h1>
        </div>

        <div class="content">
            <h2>مرحباً {{ $data['name'] }},</h2>

            <p>شكراً لتسجيلك في نظام {{ $data['company_name'] }}. يرجى استخدام كود التحقق التالي لتفعيل حسابك:</p>
            <div class="otp-code">{{ $data['otp'] }}</div>
            <p>هذا الكود صالح لمدة <strong>10 دقائق</strong> فقط.</p>
            <p>إذا لم تطلب هذا الكود، يرجى تجاهل هذه الرسالة أو تغيير كلمة المرور لحماية حسابك.</p>
        </div>
        <div class="footer">
            <p>© {{ date('Y') }} . جميع الحقوق محفوظة. لدى الشركة السورية للإعلان</p>
            <p>هذه رسالة تلقائية، يرجى عدم الرد عليها.</p>
        </div>
    </div>
</body>

</html>
