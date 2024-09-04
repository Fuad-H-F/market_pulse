<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade as PDF;
use App\Models\GeoLocation;
use App\Models\UserManager;

class ReportDownloadController extends Controller
{
    public function donwloadAllReportAsOnePDF()
    {
        $userIds    = UserManager::select('UserID')->pluck('UserID');
        $start_date = date('Y-m-d') . " 00:00:00";
        $end_date   = date('Y-m-d') . " 23:59:59";
        $locationQuery = GeoLocation::whereIn('UserID', $userIds)->whereBetween('AttendanceTime', [$start_date, $end_date]);

        $locations = $locationQuery->orderBy('AttendanceTime')->get();

        $data = [
            'locations' => $locations
        ];

        $pdf = PDF::loadView('all_report_pdf', $data)->setPaper('a4', 'landscape');
        $path = public_path('all_pdf');
        $fileName =  time() . '.' . 'pdf';
        $pdf->save($path . '/' . $fileName);

        $pdf = public_path('all_pdf/' . $fileName);
        return response()->download($pdf)->deleteFileAfterSend(true);
    }

    public function donwloadAllReportAsOneXlsx()
    {
        $userIds    = UserManager::select('UserID')->pluck('UserID');
        $start_date = date('Y-m-d') . " 00:00:00";
        $end_date   = date('Y-m-d') . " 23:59:59";
        $locationQuery = GeoLocation::whereIn('UserID', $userIds)->whereBetween('AttendanceTime', [$start_date, $end_date]);

        $locations = $locationQuery->orderBy('AttendanceTime')->get();

        return $locations;
    }
}
