<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\HtmlString;

class UserRegisteredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->greeting('Helló,')
                    ->subject( config('app.name'). ' - E-mail megerősítése')
                    ->line('Köszönjük a regisztrációt, kérlek a lent található gombra kattintva, erősítsd meg a regisztrációd.')
                    ->action('E-mail megerősítése', url(config('app.frontend_url') . '/verify/' . $this->token))
                    ->salutation(new HtmlString("Üdvözlettel,<br>" . config('app.name')));
    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
