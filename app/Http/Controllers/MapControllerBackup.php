<?php

namespace App\Http\Controllers;

use App\Models\HitCount;
use App\Models\UserManager;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use http\Env\Response;
use Illuminate\Http\Request;

use App\Models\GeoLocation;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class MapController extends Controller
{
    public function getMultiLocations(Request $request)
    {
        $userIds = json_decode($request['userIds']);
        $from = $request['from'];
        $to = $request['to'];

        $to = $to . " 23:59:59";

        $locations = GeoLocation::whereIn('UserID', $userIds)->whereBetween('AttendanceTime', [$from, $to])->with('user')
            ->orderBy('AttendanceTime')->get();
        return response()->json([
            'data' => $locations,
            'status' => 200
        ], 200);
    }

    public function getHashtags()
    {
        $hashtags = DB::table('HashTags')->select(DB::raw("distinct HashTag"))->get()->toArray();
        $hashtags = array_map(function ($hashTag) {
            return $hashTag->HashTag;
        }, $hashtags);

        return response()->json([
            'hashtags' => $hashtags,
            'status' => 200
        ], 200);
    }

    public function getLocations($user_id, $start_date, $end_date, $hash_tag = "*")
    {
        $end_date = $end_date . " 23:59:59";

        $locationQuery = GeoLocation::where('UserID', $user_id)->whereBetween('AttendanceTime', [$start_date, $end_date])
            ->orderBy('AttendanceTime');

        if (!is_null($hash_tag) && $hash_tag !== "*") {
            $locationQuery = $locationQuery->where('TagID', $hash_tag);
        }

        $locations = $locationQuery->get();

        return response()->json([
            'data' => $locations,
            'status' => 200
        ], 200);
    }

    public function generateReport($user_id, $start_date, $end_date, $hash_tag = null)
    {
        $end_date = $end_date . " 23:59:59";
        $locationQuery = GeoLocation::where('UserID', $user_id)->whereBetween('AttendanceTime', [$start_date, $end_date]);

        if (!is_null($hash_tag)) {
            $locationQuery = $locationQuery->where('Comment', 'like', '%' . $hash_tag . '%');
        }

        $locations = $locationQuery->orderBy('AttendanceTime')->get();

        $user = UserManager::where('UserID', $user_id)->first();

        $data = [
            'user' => $user,
            'locations' => $locations
        ];
        $pdf = PDF::loadView('report', $data)->setPaper('a4', 'landscape');
        $path = public_path('pdf');
        $fileName =  time() . '.' . 'pdf';
        $pdf->save($path . '/' . $fileName);

        $pdf = public_path('pdf/' . $fileName);
        return response()->download($pdf)->deleteFileAfterSend(true);
    }

    public function saveGpsLocation(Request $request)
    {
        $area = "";
        try {
            $addressApi = "https://maps.googleapis.com/maps/api/geocode/json?latlng=" . $request["lat"] . "," . $request["lng"] . "&key=AIzaSyBUhJ0UIrVq_uS-2BlSjgmQvu_IMSfd5WI";

            $response = Http::get($addressApi);

            $address = json_decode($response->body());

            $addressResult = $address->results[0];

            $addressComponents = $addressResult->address_components;

            $locality = "";

            $subLocality = "";

            for ($i = 0; $i < count($addressComponents) && ($locality === "" || $subLocality === ""); $i++) {
                $component = $addressComponents[$i];
                if (in_array("sublocality", $component->types)) {
                    $subLocality = $component->long_name;
                } else if (in_array("locality", $component->types)) {
                    $locality = $component->long_name;
                }
            }

            $area = $subLocality . ", " . $locality;
        } catch (\Exception $e) {
        }

        $imageName = time() . '.' . $request->image->extension();
        try {
            $request->image->move(public_path('images'), $imageName);
        } catch (\Exception $e) {
            return response()->json(['data' => null, 'status' => 500], 200);
        }


        $imagePath = env("APP_URL") . '/public/images/' . $imageName;


        $result = GeoLocation::create([
            'Latitude' => $request['lat'],
            'Longitude' => $request['lng'],
            'Comment' => $request['comment'],
            'UserID' => $request['user_id'],
            'AttendanceImage' => $imagePath,
            'Area' => $area
        ]);

        if ($result) {
            return response()->json(['data' => $result, 'status' => 200], 200);
        } else {
            return response()->json(['data' => $result, 'status' => 500], 500);
        }
    }
}
