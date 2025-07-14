<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>عقد إعلاني</title>

    <style>
        @font-face {
            font-family: 'amiri';
            src: url('{{ asset('fonts/Amiri-Regular.ttf') }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        body {
            font-family: 'amiri', sans-serif;
            direction: rtl;
            text-align: right;
            line-height: 1.8;
            font-size: 14px;
            margin: 40px;
            background-color: #fff;
            color: #000;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .section {
            margin-bottom: 25px;
        }

        .section p {
            margin: 4px 0;
        }

        .table {
            width: 100%;
            border: 1px solid #444;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table th,
        .table td {
            border: 1px solid #444;
            padding: 8px;
            text-align: center;
            vertical-align: middle;
        }

        .table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        ul {
            margin: 0;
            padding-right: 20px;
        }

        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
        }

        .signature-section div {
            width: 45%;
            text-align: center;
        }

        .note-box {
            padding: 10px;
            border: 1px dashed #888;
            background-color: #f9f9f9;
        }
    </style>
</head>

<body>

    <h2>عقد إعلاني</h2>

    @php
        use Carbon\Carbon;
        $start = Carbon::parse($booking->start_date);
        $end = Carbon::parse($booking->end_date);
        $months = $start->diffInMonths($end) ?: 1;
        $total = $booking->total_price_per_month * $months;
    @endphp

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
        <p><strong>القيمة الكلية للعقد:</strong> {{ number_format($total) }} ل.س (لعدد {{ $months }} شهر)</p>
        <p><strong>مدة العقد:</strong> من {{ $start->format('Y-m-d') }} إلى {{ $end->format('Y-m-d') }}</p>
    </div>

    @php
        $allowedTags = '<p><br><strong><b><ul><ol><li>';
        $cleanNotes = strip_tags($booking->notes, $allowedTags);
    @endphp
    @if ($cleanNotes)
        <div class="section">
            <p><strong>ملاحظات:</strong></p>
            <div class="note-box">{!! $cleanNotes !!}</div>
        </div>
    @endif

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
