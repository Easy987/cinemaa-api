<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Events\ChatMessageSent;
use App\Events\ChatRoomCreated;
use App\Http\Resources\ChatLastMessageResource;
use App\Http\Resources\ChatMessageResource;
use App\Jobs\SendEmail;
use App\Models\ChatRoom\ChatRoom;
use App\Models\ChatRoom\ChatRoomMessage;
use App\Models\ChatRoom\ChatRoomUser;
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

    public function sendDeletedLinkChatMessage($link) {
        if(isset($link->movie)) {
            $systemUser = User::where('username', 'SYSTEM')->first();

            $rooms = ChatRoom::with('users')->has('users', '=', 2)->whereHas('users', function(Builder $query) use ($systemUser) {
                $query->where('id', $this->id);
                $query->orWhere('id', $systemUser->id);
            })->get();

            $filteredRooms = $rooms->filter(function($room) use ($systemUser) {
                return $room->users->count() === 2 && ($room->users[0]->id === $systemUser->id || $room->users[0]->id === $this->id ) && ($room->users[1]->id === $systemUser->id || $room->users[1]->id === $this->id );
            });

            $newRoom = false;

            if($filteredRooms->count() === 0) {
                $newRoom = true;

                $chatRoom = ChatRoom::create([
                    'name' => 'SYSTEM',
                    'user_id' => $systemUser->id,
                ]);

                ChatRoomUser::create([
                    'room_id' => $chatRoom->id,
                    'user_id' => $this->id,
                ]);
                ChatRoomUser::create([
                    'room_id' => $chatRoom->id,
                    'user_id' => $systemUser->id,
                ]);
            } else {
                $chatRoom = $filteredRooms->first();
            }

            $messageText = "
        A(z) ".($link->movie->getTitle()->title)." (".($link->movie->year).") adatlaphoz tartozó egyik linked törölve lett.
        Törölt link: ".($link->link)."
        ";

            $message = ChatRoomMessage::create([
                'user_id' => $systemUser->id,
                'room_id' => $chatRoom->id,
                'message' => $messageText,
                'message_id' => null,
                'is_system' => 1
            ]);

            $lastMessage = new ChatLastMessageResource($message, $this);

            if($newRoom) {
                broadcast(new ChatRoomCreated($this, $chatRoom))->toOthers();
            } else {
                broadcast(new ChatMessageSent($this, new ChatMessageResource($message, $this->id), $lastMessage));
            }
        }
    }
}
