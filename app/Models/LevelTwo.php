<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LevelTwo extends Model
{
    use HasFactory;

    protected $table = "Level2";

    protected $primaryKey = "Level2";

    protected $keyType ='string';

    public $incrementing = false;

    protected $guarded = [];

    public function users()
    {
        return $this->hasMany(UserManager::class, 'UserID', 'Level2');
    }
}
