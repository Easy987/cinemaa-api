<?php

namespace App\Models\Movie;

use App\Traits\UUIDTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LanguageType extends Model
{
    use HasFactory, UUIDTrait, SoftDeletes;
    public $fillable = ['name'];
}
