<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('Cinema.Global', function($user) {
    return ['id' => $user->id, 'username' => $user->username];
});

Broadcast::channel('Cinema.MessageBoard', function($user) {
    return ['id' => $user->id, 'username' => $user->username];
});

Broadcast::channel('Cinema.Notification.{userID}', function($user, $userID) {
    if($user->id === $userID) {
        return true;
    }
});

Broadcast::channel('Cinema.Chat.{userID}', function($user, $userID) {
    if($user->id === $userID) {
        return true;
    }
});
