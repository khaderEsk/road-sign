<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>تفعيل حساب العميل</title>
</head>

<body>
    <h2>مرحباً {{ $data['name'] }}!</h2>
    <p>شكراً لتسجيلك في نظام {{ $data['company_name'] }}.</p>
    <p>كود التحقق الخاص بك هو: <strong>{{ $data['otp'] }}</strong></p>
    <p>هذا الكود صالح لمدة 10 دقائق فقط.</p>
    <p>إذا لم تطلب هذا الكود، يرجى تجاهل هذه الرسالة.</p>
</body>

</html>
