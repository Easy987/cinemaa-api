<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Jobs\SendEmail;
use App\Models\Movie\MovieComment;
use App\Notifications\UserPasswordResetNotification;
use App\Notifications\UserRegisteredNotification;
use App\Traits\UUIDTrait;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    use UUIDTrait, HasFactory, Notifiable, SoftDeletes, HasRoles, CanResetPassword;

    protected $fillable = [
        'username',
        'email',
        'password',
        'email_verified_at',
        'about',
        'last_login_at',
        'created_at',
        'updated_at',
        'status',
        'gender',
        'birth_date',
        'last_activity_at',
        'public_name',
        'secret_uuid'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public static $filters = ['role', 'name'];

    protected $guard_name = 'api';

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function username()
    {
        return 'username';
    }

    public function role()
    {
        return $this->morphMany(UserRole::class, 'model');
    }

    public function profile_picture()
    {
        return $this->hasOne(UserProfilePicture::class);
    }

    public function comments()
    {
        return $this->hasMany(MovieComment::class, 'user_id');
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public function sendEmailVerificationNotification()
    {
        $emailVerification = EmailVerification::create(['user_id' => $this->id, 'token' => Str::random(32)]);

        dispatch(new SendEmail($this, new UserRegisteredNotification($emailVerification->token)));
    }

    public function sendPasswordResetNotification($token)
    {
        dispatch(new SendEmail($this, new UserPasswordResetNotification($token)));
    }

    public function scopeFilter($query, $type, $filter)
    {
        switch($type) {
            case 'role':
                return $query->whereHas('role.role', function(Builder $subQuery) use ($filter) {
                     $subQuery->where(function($query) use ($filter){
                        foreach($filter as $roleFilter) {
                            $query->orWhere('name', $roleFilter['key']);
                        }
                    });
                });
                break;
            case 'name':
                return $query->where('username', 'LIKE', '%'.$filter.'%');
                break;
            default:
                return $query;
                break;
        }
    }
}
