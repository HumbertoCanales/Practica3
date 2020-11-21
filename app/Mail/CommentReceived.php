<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CommentReceived extends Mailable
{
    use Queueable, SerializesModels;

    public $author, $title, $content;

    public function __construct($author, $title, $content)
    {
        $this->author = $author;
        $this->title = $title;
        $this->content = $content;
    }

    public function build()
    {
        return $this->view('api.com_received');
    }
}
