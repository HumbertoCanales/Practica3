<?php
namespace App\Notifications;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailBase;
class VerifyApiEmail extends VerifyEmailBase
{

public function toMail($notifiable)
{
    $verificationUrl = $this->verificationUrl($notifiable);

    return (new MailMessage())->view('api.verify_email', ['url' => $verificationUrl]);
}

protected function verificationUrl($notifiable)
    {
        return URL::temporarySignedRoute(
        'verificationapi.verify', Carbon::now()->addMinutes(60), 
        ['id' => $notifiable->getKey(),
         'hash' => sha1($notifiable->email)]);
    }
}
