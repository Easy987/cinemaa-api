<?php

namespace App\Http\Controllers;

use App\Enums\UserStatusEnum;
use App\Http\Resources\OtherUserResource;
use App\Http\Resources\UserMinimalResource;
use App\Http\Resources\UserResource;
use App\Models\ChatRoom\ChatRoomUser;
use App\Models\EmailVerification;
use App\Models\PasswordReset;
use App\Models\UserProfilePicture;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;


class AuthController extends Controller
{
    public function __construct(Request $request)
    {
        if ($request->has('lang')) {
            App::setLocale($request->get('lang'));
        }
        $this->middleware('auth:api', ['except' => ['login', 'register', 'verify', 'refresh', 'forgot', 'reset']]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (!$token = JWTAuth::attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 402);
        }

        $user = User::where('username', $request->get('username'))->first();
        if ($user->email_verified_at === null) {
            return response()->json(['error' => 'Email verification required'], 412);
        }
        if($user->status === '2') {
            return response()->json(['error' => 'Blocked'], 405);
        }

        $user->update(['last_login_at' => now()]);

        ChatRoomUser::firstOrCreate([
            'room_id' => '93343d2f-6d8b-463e-b4d3-347ef04a461C',
            'user_id' => auth()->user()->id,
        ]);

        return $this->createNewToken($token, auth()->user());
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|unique:users|max:12',
            'email' => 'required|email',
            'password' => 'required|string|confirmed|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create(array_merge(
            $validator->validated(),
            ['password' => $request->password,
            'secret_uuid' => Str::uuid()]
        ));

        event(new Registered($user));

        return response()->json(['message' => 'User successfully registered.'], 202);
    }

    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'User successfully signed out']);
    }

    public function refresh()
    {
        $token = auth()->refresh();
        $user = JWTAuth::setToken($token)->toUser();

        return $this->createNewToken($token, $user);
    }

    public function verify(Request $request, $token)
    {
        $emailVerification = EmailVerification::where('token', $token)->first();

        if ($emailVerification) {
            $emailVerification->user->update(['email_verified_at' => now(), 'status' => (string) UserStatusEnum::Verified]);
            $emailVerification->user->assignRole('user');
            $emailVerification->delete();

            return response()->json(['message' => 'User successfully verified.'], 200);
        }

        return response()->json(['message' => 'User verification error.'], 400);
    }

    public function forgot(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $status = Password::sendResetLink(
            $request->only('email')
        );

        switch($status) {
            case Password::PASSWORD_RESET:
            case Password::INVALID_USER:
                return response($status, 200);
                break;
            case Password::RESET_THROTTLED:
                return response($status, 400);
                break;
        }
    }

    public function reset(Request $request, $token)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|confirmed|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $passwordReset = PasswordReset::where('email', $request->get('email'))->first();
        if($passwordReset) {
            if(Hash::check($token, $passwordReset->token)) {
                User::where('email', $request->get('email'))->update(['password' => Hash::make($request->get('password'))]);

                PasswordReset::where('email', $request->get('email'))->delete();
                return response('', 200);
            }
        }

        return response('', 404);
    }

    public function me()
    {
        return new UserResource(auth()->user());
    }

    public function update(Request $request)
    {
        $updatedData = $request->only(['gender', 'birth_date', 'public_name', 'about']);

        $request->user()->update($updatedData);

        return new UserResource(auth()->user());
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'password' => 'required|string|confirmed|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = $request->user();

        if(Hash::check($request->get('old_password'), $user->password)){
            $user->update(['password' => $request->get('password')]);

            return response('', 200);
        }

        return response('', 402);
    }

    public function profilePicture(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $userID = $request->user()->id;
        $basePath = storage_path('images/profiles/');

        $userProfilePicture = UserProfilePicture::where('user_id', $userID)->first();
        if($userProfilePicture) {
            File::delete($basePath . '/' . $userID . '/' . $userProfilePicture->id . '.' . $userProfilePicture->extension);
            $userProfilePicture->delete();
        }

        $userProfilePicture = UserProfilePicture::create([
            'user_id' => $userID,
            'extension' => $request->image->extension()
        ]);

        $imageName = $userProfilePicture->id.'.'.$request->image->extension();

        File::makeDirectory($basePath . '/' . $userID, 0777, true, true);

        $photo = Image::make($request->image)
            ->encode('png',100);

        Storage::disk('profile')->put( $userID . '/' . $imageName, $photo);
        //$request->image->move(storage_path('images/profiles/' . $userID . '/'), $imageName);

        return response('', 200);
    }

    protected function createNewToken($token, $user)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => JWTFactory::getTTL() * 60,
            'user' => new UserResource($user)
        ]);
    }

    public function users(Request $request)
    {
        $users = User::query();

        if($request->hasAny(User::$filters)){
            $filters = $request->only(User::$filters);
            foreach($filters as $type => $filter) {
                $users = $users->filter($type, json_decode($filter, true));
            }
        }

        return OtherUserResource::collection($users->paginate(24));
    }

    public function user(Request $request, $username)
    {
        $user = User::where('username', $username)->firstOrFail();

        return new OtherUserResource($user);
    }
}
