<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeoLocation extends Model
{
    use HasFactory;

    protected $table = "Attendance";

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(UserManager::class, 'UserID', 'UserID');
    }
}
