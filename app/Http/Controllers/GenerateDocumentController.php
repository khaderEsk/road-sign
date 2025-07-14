<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use PDF;
class GenerateDocumentController extends Controller
{
    public function generateContractPdf($bookingId)
    {
        $booking = Booking::with(['customer', 'roadSigns', 'roadSigns.template', 'user', 'roadSigns.city', 'roadSigns.region'])->findOrFail($bookingId);
            $pdf = PDF::loadView('contracts.booking_contract',$booking);
            $pdf->autoScriptToLang = true;
            $pdf->autoArabic = true;
            $pdf->autoLangToFont = true;
            return $pdf->download('booking_contract_{{$booking->customer->company_name}}.pdf');
    }
    public function generateContractView($bookingId)
    {
        $booking = Booking::with(['customer', 'roadSigns', 'roadSigns.template', 'user', 'roadSigns.city', 'roadSigns.region'])->findOrFail($bookingId);


        return View("contracts.booking_contract", compact('booking'));
    }
}
