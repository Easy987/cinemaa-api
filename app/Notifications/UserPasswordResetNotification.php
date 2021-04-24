<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\HtmlString;

class UserPasswordResetNotification extends Notification implements ShouldQueue
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
                    ->subject( config('app.name'). ' - Elfelejtett jelszó')
                    ->line('Ha elfelejtetted a jelszavadat, kattints a lent található gombra.')
                    ->action('Elfelejtett jelszó', url(config('app.frontend_url') . '/forgot/' . $this->token . '/' . $notifiable->email))
                    ->salutation(new HtmlString("Üdvözlettel,<br>" . config('app.name')));
    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
