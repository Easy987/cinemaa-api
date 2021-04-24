<?php

namespace App\Models\Movie;

use App\Models\User;
use App\Traits\UUIDTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BadLink extends Model
{
    use HasFactory, UUIDTrait, SoftDeletes;

    public $table = 'bad_links';

    public $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }

    public function reportable()
    {
        return $this->morphTo();
    }
}
