<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://fonts.googleapis.com/css2?family=Amiri&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('pdf.css') }}" type="text/css">
    <title>عقد إعلاني</title>

</head>

<body>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">

        <div style="text-align: left; font-size: 24px;">
            التاريخ : {{ \Carbon\Carbon::now()->format('Y-m-d') }}
        </div>

        <div style="text-align: right;">
            <img src="{{ asset('logo.png') }}" alt="شعار" style="width: 120px;">
        </div>
    </div>

    <h2>عقد إعلاني</h2>



    <div class="section">
        <p><strong>الطرف الأول:</strong> {{ $booking->user->full_name ?? '---' }} (الشركة السورية للإعلان)</p>
        <p><strong>الطرف الثاني:</strong> {{ $booking->customer->company_name ?? $booking->customer->full_name }}</p>
        <p><strong>رقم السجل التجاري:</strong> {{ $booking->customer->number ?? '---' }}</p>
        <p><strong>العنوان:</strong> {{ $booking->customer->address ?? '---' }}</p>
    </div>

    <div class="section">
        <p>
            يرغب الطرف الثاني في الترويج لمنتجاته باستخدام لوحات إعلانية تابعة للطرف الأول، بموجب ترخيص المؤسسة العربية
            للإعلان.
        </p>
        <p>وتم الاتفاق على ما يلي وفقاً للجدول التالي:</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>النموذج</th>
                <th>المقاس</th>
                <th>مكان التمركز</th>
                <th>المدينة</th>
                <th>الاتجاه</th>
                <th>عدد الأوجه</th>
                <th>أجر الوجه</th>
                <th>السعر الكلي للأوجه</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($booking->roadsigns as $sign)
                <tr>
                    <td>{{ $sign->template->model ?? '---' }}</td>
                    <td>{{ $sign->template->size ?? '---' }}</td>
                    <td>{{ $sign->place }}</td>
                    <td>{{ $sign->city->name ?? '---' }}</td>
                    <td>{{ $sign->directions ?? '---' }}</td>
                    <td>{{ $sign->pivot->booking_faces }}</td>
                    <td>{{ number_format($sign->pivot->face_price) }}</td>
                    <td>{{ number_format($sign->pivot->total_faces_price) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section">
        <p><strong>القيمة الكلية للعقد قبل الحسم:</strong> {{ number_format($booking->total_price_befor_discount) }}
            ل.س</p>
        <p><strong>القيمة الكلية للعقد بعد الحسم:</strong> {{ number_format($booking->total_price) }} ل.س</p>
        <p><strong>مدة العقد:</strong> من {{ $booking->start_date->format('Y-m-d') }} إلى {{ $booking->end_date->format('Y-m-d') }}</p>
    </div>




        <div class="section">
            <p><strong>ملاحظات:</strong></p>
            <div class="note-box">{!! $booking->notes !!}</div>
        </div>


    <div class="signature-section">
        <div>
            <p><strong>الطرف الأول</strong></p>
            <p>{{ $booking->user->full_name ?? '---' }}</p>
        </div>
        <div>
            <p><strong>الطرف الثاني</strong></p>
            <p>{{ $booking->customer->company_name ?? $booking->customer->full_name }}</p>
        </div>
    </div>

</body>

</html>
