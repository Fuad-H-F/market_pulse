<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\LevelOne;
use App\Models\LevelTwo;
use App\Models\LevelThree;
use App\Models\GeoLocation;
use App\Models\UserManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function get_all_users(Request $request)
    {
        $level = $request->route('level');
        $userId = $value = $request->header('userid');
        // $levels = ['Level10', 'Level4', 'Level3', 'Level2', 'Level1'];
        // $levelIndex = array_search($level, $levels);

        if ($level === 'Level10') {
            //$users = UserManager::all();
            $users = UserManager::where('UserType', '<>', 'Level10')->orderBy('UserID')->get();
        } else {
            $user = UserManager::where('UserID', $userId)->first();
            $subordinateUsers = $user->allSubordinateUsers($user);
            $users = array_merge($subordinateUsers, [$user]);
        }

        foreach ($users as $user) {
            $user['Supervisor'] = $this->getSupervisor($user);
        }

        return response()->json(['data' => $users, 'userid' => $userId, 'status' => 200],  200);
    }

    public function getSupervisor($user)
    {
        $userType = $user["UserType"];
        $userId = $user["UserID"];
        $supervisorId = '';
        if ($userType === 'Level1') {
            $level1 = LevelOne::where('Level1', $userId)->first();
            if ($level1) {
                $supervisorId = $level1->Level2;
            }
        } else if ($userType === 'Level2') {
            $level2 = LevelTwo::where('Level2', $userId)->first();
            if ($level2) {
                $supervisorId = $level2->Level3;
            }
        } else if ($userType === 'Level3') {
            $level3 = LevelThree::where('Level3', $userId)->first();
            if ($level3) {
                $supervisorId = $level3->Level4;
            }
        }

        if ($supervisorId !== '') {
            return UserManager::where('UserID', $supervisorId)->first();
        } else {
            return null;
        }
    }

    public function get_users_by_type($level)
    {
        $users = UserManager::where('UserType', $level)->get();
        return response()->json(['data' => $users, 'status' => 200],  200);
    }

    public function get_user_details($id = null)
    {
        if ($id) {
            $user = UserManager::where('UserID', $id)->firstOrFail();
            return response()->json(['data' => $user, 'status' => 200], 200);
        } else {
            return response()->json(['data' => null, 'status' => 404], 404);
        }
    }

    public function levelOne(Request $request)
    {
        $level1 = $request['Level1'];
        $level2 = $request['Level2'];
        $insertedLevel1 = LevelOne::where('Level1', $level1)->first();
        if ($insertedLevel1) {
            $result = $insertedLevel1->update([
                'Level2' => $level2
            ]);
        } else {
            $result = LevelOne::create([
                'Level1' => $level1,
                'Level2' => $level2
            ]);
        }

        return response()->json(['data' => $result, 'status' => 200],  200);
    }

    public function levelTwo(Request $request)
    {
        $level2 = $request['Level2'];
        $level3 = $request['Level3'];

        $insertedLevel2 = LevelTwo::where('Level2', $level2)->first();
        if ($insertedLevel2) {
            $result = $insertedLevel2->update([
                'Level3' => $level3
            ]);
        } else {
            $result = LevelTwo::create([
                'Level2' => $level2,
                'Level3' => $level3
            ]);
        }
        return response()->json(['data' => $result, 'status' => 200],  200);
    }

    public function levelThree(Request $request)
    {
        $level3 = $request['Level3'];
        $level4 = $request['Level4'];

        $insertedLevel3 = LevelThree::where('Level3', $level3)->first();

        if ($insertedLevel3) {
            $result = $insertedLevel3->update([
                'Level4' => $level4
            ]);
        } else {
            $result = LevelThree::create([
                'Level3' => $level3,
                'Level4' => $level4
            ]);
        }
        return response()->json(['data' => $result, 'status' => 200],  200);
    }


    public function  inOutReport($start_date, $end_date, $userId)
    {
        $end_date = $end_date . " 23:59:59";

        $user = UserManager::where('UserID', $userId)->first();

        $m_attendances = GeoLocation::select('UserID', 'AttendanceTime', 'Area');

        if (isset($user) && $user->UserType !== "Level10") {

            $subordinates = $user->allSubordinateUsers($user);
            $subordinateUserIds = array_map(function ($user) {
                return  $user['UserID'];
            }, $subordinates);

            array_push($subordinateUserIds, $userId);

            $m_attendances = $m_attendances->whereIn('UserID', $subordinateUserIds);
        }

        $inOutReport = [];

        $m_attendances = $m_attendances->whereBetween('AttendanceTime', [$start_date, $end_date])
            ->with('user')
            ->orderBy('AttendanceTime')
            ->get()->groupBy([
                function ($item) {
                    return Carbon::createFromFormat('Y-m-d H:i:s.u', $item->AttendanceTime)->format('Y-m-d');
                },
                'UserID'
            ])->toArray();


        foreach ($m_attendances as $date => $d_attendances) {

            foreach ($d_attendances as $userId => $attendances) {

                $report_count = count($attendances);

                if ($report_count > 2) {
                    $attendances = [$attendances[0], $attendances[count($attendances) - 1]];
                } else if (count($attendances) === 1) {
                    $attendances = [$attendances[0], $attendances[0]];
                }

                $in_attendance = $attendances[0];
                $out_attendance = $attendances[1];

                $atten['date'] = $date;
                $atten['user_name'] = isset($in_attendance['user']) ? $in_attendance['user']['UserName'] : '';
                $atten['user_id'] = $userId;
                $atten['in_time'] = Carbon::createFromFormat('Y-m-d H:i:s.u', $in_attendance['AttendanceTime'])
                    ->format('h:i A');
                $atten['out_time'] = Carbon::createFromFormat('Y-m-d H:i:s.u', $out_attendance['AttendanceTime'])
                    ->format('h:i A');
                $atten['in_area'] = $in_attendance['Area'];
                $atten['out_area'] = $out_attendance['Area'];
                $atten['report_count'] = $report_count;
                array_push($inOutReport, $atten);
            }
        }

        return response()->json(['data' => $inOutReport, 'status' => 200], 200);
    }
}
