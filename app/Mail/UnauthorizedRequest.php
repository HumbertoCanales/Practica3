<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UnauthorizedRequest extends Mailable
{
    use Queueable, SerializesModels;

    public $user, $id, $ability;

    public function __construct($user, $id, $ability)
    {
        $this->user = $user;
        $this->id = $id;
        $this->ability = $ability;
    }

    public function build()
    {
        return $this->view('api.unauth_request');
    }
}
