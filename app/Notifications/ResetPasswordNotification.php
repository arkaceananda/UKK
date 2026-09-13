<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseReset;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends BaseReset
{
    public function toMail($notifiable)
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Atur ulang password — BurjoOrder')
            ->greeting('Halo, '.$notifiable->name)
            ->line('Kamu menerima email ini karena kami menerima permintaan atur ulang password untuk akun BurjoOrder kamu.')
            ->line('Link di bawah hanya berlaku 60 menit. Jangan bagikan link ini ke siapa pun.')
            ->action('Atur ulang password', $url)
            ->line('Jika kamu tidak meminta atur ulang, abaikan email ini — tidak ada perubahan yang terjadi.')
            ->salutation('— BurjoOrder');
    }
}
