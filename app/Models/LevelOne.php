<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LevelOne extends Model
{
    use HasFactory;

    protected $table = "Level1";

    protected $guarded = [];

    public function users()
    {
        return $this->hasMany(UserManager::class, 'UserID', 'Level1');
    }
}
