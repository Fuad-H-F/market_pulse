<?php

namespace App\Http\Controllers;

use App\Models\HitCount;
use App\Models\UserManager;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HitCountController extends Controller
{
    public function updateHitCount(Request $request)
    {
        $user_id = $request['hittingUserId'];
        $hitCount = HitCount::where('UserID', $user_id)->where('HittingDate', Carbon::today()->format('Y-m-d'))->first();
        if($hitCount === null) {
            $hitCount = HitCount::create([
                'UserID' => $user_id,
                'HittingDate' => Carbon::today()->format('Y-m-d'),
                'Count' => 1
            ]);
        } else {
            $count = $hitCount->Count+1;
            $hitCount->update([
                'Count' => $count
            ]);
        }

        return response()->json(['data' => $hitCount, 'status' => 200], 200);
    }

    public function getHitCounts($from, $to, $userId)
    {
        $to = $to." 23:59:59";
        $user = UserManager::where('UserID', $userId)->first();
        if($user->UserType === "Level10") {
            $hitCounts = HitCount::whereBetween('HittingDate',[$from, $to])->with('user')->get()->toArray();
        } else {
            $subordinates = $user->allSubordinateUsers($user);
            $subordinateUserIds = array_map(function ($user) {
               return  $user['UserID'];
            }, $subordinates);
            $hitCounts = HitCount::whereIn('UserID', $subordinateUserIds)->whereBetween('HittingDate',[$from, $to])->with('user')->get()->toArray();
        }

        $hitCounts = array_map(function($hit) {
            $hit['UserName'] = $hit['user']['UserName'];
            unset($hit['user']);
            return $hit;
        }, $hitCounts);
        return response()->json(['data' => $hitCounts, 'status' => 200], 200);
    }
}
