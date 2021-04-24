<?php

namespace App\Models;

use App\Traits\UUIDTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfilePicture extends Model
{
    use HasFactory, UUIDTrait;

    protected $table = 'users_profile_pictures';
    protected $fillable = ['user_id', 'extension'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
