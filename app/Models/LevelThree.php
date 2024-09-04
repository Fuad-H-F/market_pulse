<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LevelThree extends Model
{
    use HasFactory;

    protected $table = "Level3";

    protected $primaryKey = "Level3";

    protected $keyType ='string';

    public $incrementing = false;

    protected $guarded = [];

    public function users()
    {
        return $this->hasMany(UserManager::class, 'UserID', 'Level3');
    }
}
