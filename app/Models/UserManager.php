<?php

namespace App\Models;

use App\Models\LevelOne;
use App\Models\LevelTwo;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserManager extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $table = "UserManager";

    protected $primaryKey = "UserID";

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    public function subOrdinateUsers()
    {
        $userLevel = $this->UserType;
        if ($userLevel === 'Level4') {
            return $this->hasMany(LevelThree::class, 'Level4', 'UserID');
        } else if ($userLevel === 'Level3') {
            return $this->hasMany(LevelTwo::class, 'Level3', 'UserID');
        } else if ($userLevel === 'Level2') {
            return $this->hasMany(LevelOne::class, 'Level2', 'UserID');
        }

        return $this->hasMany(LevelOne::class, 'Level2', 'UserID');
    }


    public function allSubordinateUsers($user)
    {
        $result = [];
        if ($user->UserType === 'Level1') return $result;
        $subordinates = $user->subOrdinateUsers;
        if ($subordinates) {
            foreach ($subordinates as $subordinate) {
                $result = array_merge($result, $subordinate->users->toArray());
            }
        }

        if ($subordinates) {
            foreach ($subordinates as $subordinate) {
                $users = $subordinate->users;
                foreach ($users as $user) {
                    $result = array_merge($result, $this->allSubordinateUsers($user));
                }
            }
        }

        return $result;
    }

    //    public function supervisor()
    //    {
    //
    //      return $this->hasOneThrough(UserManager::class, LevelOne::class, 'Level2', 'UserID','UserID', 'Level2');
    //
    //    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
