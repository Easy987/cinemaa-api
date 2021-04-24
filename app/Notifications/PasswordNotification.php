<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class PasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    protected $username;
    protected $password;
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject( config('app.name'). ' - Belépési adatok')
            ->greeting('Helló,')
            ->line('Köszöntünk a megújult Cinemaa.cc regisztrált felhasználói között!')
            ->line('Biztonsági okokból a meglévő jelszavadat megváltoztattuk, a lent található adatokkal tudsz belépni az oldalra.')
            ->line('Felhasználóneved: ' . $this->username)
            ->line('Jelszavad: ' . $this->password)
            ->line('')
            ->action('Tovább az oldalra', url(config('app.frontend_url')))
            ->salutation(new HtmlString("Üdvözlettel,<br>" . config('app.name')));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
