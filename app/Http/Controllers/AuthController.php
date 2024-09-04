<?php

namespace App\Http\Controllers;

use App\Models\LevelFour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

use App\Models\UserManager;
use App\Models\LevelOne;
use App\Models\LevelTwo;
use App\Models\LevelThree;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller

{
    //		public function __construct()
    //	    {
    //	        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    //	    }
    // public function login(Request $request)
    // {
    //     $user = UserManager::where('UserID', $request['userId'])->where('Password', $request['password'])->first();

    //     if ($user) {

    //         $token = JWTAuth::fromUser($user);
    //     } else {

    //         return response()->json(['error' => 'could_not_create_token'], 401);
    //     }

    //     return response()->json(['user' => $user, 'access_token' => $token, 'status' => 200], 200);

    //     //	        return $this->respondWithToken($token);
    // }

    public function login(Request $request)
    {
        $databaseName = DB::connection()->getDatabaseName();
        return "Connected to database: " . $databaseName;
        dd(DB::table('UserManager'));
        $user = UserManager::where('UserID', $request->userId)->first();
        dd($request->all(), $request->userId, DB::table('UserManager')->first());
        if ($user && $user->Password === $request['password']) { // Replace this with the correct password check
            try {
                $token = JWTAuth::fromUser($user);
                return response()->json(['user' => $user, 'access_token' => $token, 'status' => 200], 200);
            } catch (JWTException $e) {
                return response()->json(['error' => 'could_not_create_token'], 500);
            }
        }

        return response()->json(['error' => 'invalid_credentials'], 401);
    }

    public function register(Request $request)
    {
        $existingUser = UserManager::where('UserID', $request->get('UserID'))->first();

        if ($existingUser) {
            return response()->json(['status' => 500, 'message' => "Duplicate user id"], 500);
        }

        $user = UserManager::create([
            'UserID' => $request->get('UserID'),
            'UserName' => $request->get('UserName'),
            'Designation' => $request->get('Designation'),
            'UserType' => $request->get('UserType'),
            'Email' => $request->get('Email'),
            'Password' => $request->get('Password'),
            'Active' => $request->get('Active')
        ]);

        if ($request['Supervisor']) {
            $supervisor = $request['Supervisor'];
            if ($request["UserType"] === "Level1") {
                LevelOne::create([
                    'Level1' => $user->UserID,
                    'Level2' => $supervisor
                ]);
            } else if ($request["UserType"] === "Level2") {
                LevelTwo::create([
                    'Level2' => $user->UserID,
                    'Level3' => $supervisor
                ]);
            } else if ($request["UserType"] === "Level3") {
                LevelThree::create([
                    'Level3' => $user->UserID,
                    'Level4' => $supervisor
                ]);
            }
        }

        return response()->json(['data' => $user, 'status' => 200], 200);
    }

    public function update_user(Request $request)
    {
        $userId = $request->get('UserID');
        $user = UserManager::where('UserID', $userId)->firstOrFail();
        $userType = $user["UserType"];
        try {
            if ($userType === "Level1") {
                LevelOne::where("Level1", $userId)->delete();
            } else if ($userType === "Level2") {
                LevelTwo::where("Level2", $userId)->delete();
            } else if ($userType === "Level3") {
                LevelThree::where("Level3", $userId)->delete();
            } else if ($userType === "Level4") {
                LevelThree::where("Level4", $userId)->delete();
            }
        } catch (\Exception $e) {
            return response()->json(['data' => 'deleted']);
        }
        $update = $user->update([
            'UserName' => $request->get('UserName'),
            'Designation' => $request->get('Designation'),
            'UserType' => $request->get('UserType'),
            'Email' => $request->get('Email'),
            'Password' => $request->get('Password'),
            'Active' => $request->get('Active')
        ]);
        $updatedLevel = $request->get('UserType');
        $supervisorId = $request->get('Supervisor');

        if ($updatedLevel === "Level1") {
            LevelOne::create([
                'Level1' => $userId,
                'Level2' => $supervisorId
            ]);
        } else if ($updatedLevel === "Level2") {
            LevelTwo::create([
                'Level2' => $userId,
                'Level3' => $supervisorId
            ]);
        } else if ($updatedLevel === "Level3") {
            LevelThree::create([
                'Level3' => $userId,
                'Level4' => $supervisorId
            ]);
        } else if ($updatedLevel === "Level4") {
            LevelFour::create([
                'Level4' => $userId,
                'Level5' => $supervisorId
            ]);
        }

        if ($update) {
            return response()->json(['data' => $update, 'status' => 200], 200);
        } else {
            return response()->json(['data' => $update, 'status' => 500], 500);
        }
    }

    public function deleteUser(Request $request)
    {
        $userId = $request["UserID"];
        try {
            $user = UserManager::where('UserID', $userId)->first();
            if ($user) {
                $userType = $user['UserType'];
                if ($userType === "Level4") {
                    LevelFour::where("Level4", $userId)->delete();
                } else if ($userType === "Level3") {
                    LevelThree::where("Level3", $userId)->delete();
                } else if ($userType === "Level2") {
                    LevelTwo::where("Level2", $userId)->delete();
                } else if ($userType === "Level1") {
                    LevelOne::where("Level1", $userId)->delete();
                }
                DB::table('Attendance')->where('UserID', $userId)->delete();
                DB::table('UserManager')->where('UserID', $userId)->delete();
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => $e->getMessage()], 200);
        }


        return response()->json(['status' => 200], 200);
    }

    public function me()
    {
        return response()->json($this->guard()->user());
    }

    /**
     * Log the user out (Invalidate the token)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $this->guard()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken($this->guard()->refresh());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }

    public function guard()
    {
        return Auth::guard();
    }
}
