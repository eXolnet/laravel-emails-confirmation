<?php

namespace Exolnet\Auth\Notifications;

use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConfirmEmail extends Notification
{
    /**
     * Where to send the email confirmation.
     *
     * @var string
     */
    public $email;

    /**
     * The email confirmation token.
     *
     * @var string
     */
    public $token;

    /**
     * Create a notification instance.
     *
     * @param  string  $email
     * @param  string  $token
     * @return void
     */
    public function __construct($email, $token)
    {
        $this->email = $email;
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
     * @return \Illuminate\Mail\Mailable
     */
    public function toMail($notifiable)
    {
        $url = url(config('app.url') .
            route('email.confirm', [urlencode($notifiable->getEmailForEmailConfirmation()), $this->token], false));

        $message = (new MailMessage())
            ->line('You are receiving this email because we received an email confirmation request for your account.')
            ->action('Confirm Email', $url)
            ->line('If you did not request an email confirmation, no further action is required.');

        // Since we want to override the recipient, we have to create our own Mailable instance
        return (new class extends Mailable {
            public function build()
            {
            }
        })
            ->to($this->email)
            ->subject('Confirm Email')
            ->markdown($message->markdown, $message->data());
    }
}
