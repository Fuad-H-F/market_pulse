<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HitCount extends Model
{
    use HasFactory;

    protected $table = "HitCount";

    protected $fillable = ['UserID', 'HittingDate', 'Count'];

    public function user()
    {
        return $this->belongsTo(UserManager::class, 'UserID', 'UserID');
    }
}
