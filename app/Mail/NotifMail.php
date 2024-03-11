<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotifMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $letterTitle;
    protected $decision ;
    protected $role;
    

    /**
     * Create a new message instance.
     *
     * @param string $otpCode
     */
    public function __construct($letterTitle,$decision,$role)
    {
        $this->letterTitle= $letterTitle;
        $this->decision = $decision;
        $this->role = $role;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $letterTitle = $this->letterTitle;
        $decision = $this->decision;
        $role = $this->role;

        if ($decision === "approved") {
            $this->subject("Letter Approved by $role");

            // Set the HTML content for the email
            $this->html("<p>you letter with title : $letterTitle is Approved by $role </p>");
        } else {
            $this->subject("Letter  Rejected by $role");

            // Set the HTML content for the email
            $this->html("<p>you letter with title : $letterTitle is Rejected by $role </p>");
        }
       

        return $this;
    }
}
