<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class ContactEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'mail'];
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
                    ->greeting('Helló,')
                    ->subject('Cinemaa.cc - Új kapcsolat üzenet')
                    ->line('Új kapcsolat üzenet érkezett')
                    ->line('Beküldő adatai:')
                    ->line(new HtmlString('Név: ' . $this->data['name'] . '<br>'.
                                                'Email: ' . $this->data['email'] . '<br>'.
                                                'Tárgy: ' . $this->data['subject'] . '<br>'.
                                                'Üzenet: ' . $this->data['message']. '<br>'))
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
