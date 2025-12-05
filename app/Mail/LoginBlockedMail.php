<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LoginBlockedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $seconds;

    public function __construct($seconds)
    {
        $this->seconds = $seconds;
    }

    public function build()
    {
        return $this->subject('Login Temporarily Blocked')
            ->view('emails.login_blocked')
            ->with(['seconds' => $this->seconds]);
    }
}
