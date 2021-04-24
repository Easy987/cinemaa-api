<?php

namespace App\Models\Movie;

use App\Traits\UUIDTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Genre extends Model
{
    use UUIDTrait, SoftDeletes;
    public $fillable = ['name'];
}
