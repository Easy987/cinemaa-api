<?php

namespace App\Http\Controllers;

use App\Jobs\SendEmail;
use App\Models\User;
use App\Notifications\ContactEmailNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function contact(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email',
            'subject' => 'required|string',
            'message' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        dispatch(new SendEmail(User::where('username', 'cinemaa')->first(), new ContactEmailNotification($request->only(['name', 'email', 'subject', 'message']))));

        return response('', 200);
    }
}
