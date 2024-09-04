<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LevelFour extends Model
{
    use HasFactory;

    protected $table = "Level4";

    protected $primaryKey = "Level4";

    protected $keyType ='string';

    public $incrementing = false;

    protected $guarded = [];
}
