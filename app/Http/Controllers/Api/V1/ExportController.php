<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;

class ExportController extends Controller
{

    public function generateWord(int $booking_id)
    {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        $booking = Booking::with(['user', 'customer', 'roadsigns.template', 'roadsigns.city', 'roadsigns.region', 'roadsigns.bookings'])
            ->findOrFail($booking_id);


        $imagePath = public_path('images/logo.jpg');
        if (file_exists($imagePath)) {
            $section->addImage(
                $imagePath,
                [
                    'width' => 150,
                    'height' => 75,
                    'alignment' => Jc::CENTER
                ]
            );
        }

        $section->addText('');
        $section->addText('تاريخ إصدار التقرير: ' . now()->format('Y-m-d H:i'), ['bold' => true]);
        $section->addTextBreak();

        $table = $section->addTable();

        $table->addRow();
        $table->addCell()->addText('نوع النموذج');
        $table->addCell()->addText('القياس');
        $table->addCell()->addText('مكان التموضع');
        $table->addCell()->addText('المنطقة');
        $table->addCell()->addText('الاتجاه');
        $table->addCell()->addText('عدد الأوجه');
        $table->addCell()->addText('أجور العرض للوجه');
        $table->addCell()->addText('أجور العرض للأوجه المحددة');

        foreach ($booking->roadsigns as $roadsign) {
            $template = $roadsign->template;
            if (!$template) continue;

            $product = $template->products()->where('type', $booking->product_type)->first();
            $productPrice = $product ? $product->price : 0;

            $numberBookingFace = $roadsign->bookings->where('id', $booking->id)->sum('pivot.booking_faces');

            $table->addRow();
            $table->addCell()->addText($template->model ?? '-');
            $table->addCell()->addText($template->type ?? '-');
            $table->addCell()->addText($roadsign->place ?? '-');
            $table->addCell()->addText(($roadsign->city->name ?? '') . ' - ' . ($roadsign->region->name ?? ''));
            $table->addCell()->addText($roadsign->directions ?? '-');
            $table->addCell()->addText($numberBookingFace);
            $table->addCell()->addText($productPrice);
            $table->addCell()->addText($productPrice * $numberBookingFace);
        }

        $fileName = 'contract_' . now()->format('Ymd_His') . '.docx';
        $tempFile = storage_path($fileName);

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempFile);

        return response()->download($tempFile)->deleteFileAfterSend(true);
    }
}
