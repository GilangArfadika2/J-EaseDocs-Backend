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
    protected $userString;
    

    /**
     * Create a new message instance.
     *
     * @param string $otpCode
     */
    public function __construct($letterTitle,$decision,$role,$userString)
    {
        $this->letterTitle= $letterTitle;
        $this->decision = $decision;
        $this->role = $role;
        $this->userString = $userString;
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
        $userString = $this->userString;

        if ($decision === "approved") {
            $this->subject("Letter Approved by $role");

            // Set the HTML content for the userString
            $this->html("<p>you letter with title : $letterTitle is Approved by $role $userString </p>");
        } else {
            $this->subject("Letter  Rejected by $role $userString");

            // Set the HTML content for the userString
            $this->html("<p>you letter with title : $letterTitle is Rejected by $role $userString </p>");
        }
       

        return $this;
    }
}
