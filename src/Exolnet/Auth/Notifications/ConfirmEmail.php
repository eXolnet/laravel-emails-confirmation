<?php

namespace Exolnet\Auth\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConfirmEmail extends Notification
{
    /**
     * The email confirmation token.
     *
     * @var string
     */
    public $token;

    /**
     * Create a notification instance.
     *
     * @param  string  $token
     * @return void
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's channels.
     *
     * @param  mixed  $notifiable
     * @return array|string
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = url(config('app.url').
            route('email.confirm', [urlencode($notifiable->getEmailForEmailConfirmation()), $this->token], false));

        return (new MailMessage)
            ->line('You are receiving this email because we received an email confirmation request for your account.')
            ->action('Confirm Email', $url)
            ->line('If you did not request an email confirmation, no further action is required.');
    }
}
