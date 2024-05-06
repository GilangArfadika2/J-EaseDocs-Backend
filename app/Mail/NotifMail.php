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
    protected $feedback;
    

    /**
     * Create a new message instance.
     *
     * @param string $otpCode
     */
    public function __construct($letterTitle,$decision,$role,$userString,$feedback)
    {
        $this->letterTitle= $letterTitle;
        $this->decision = $decision;
        $this->role = $role;
        $this->userString = $userString;
        $this->feedback = $feedback;
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
        $feedback = $this->feedback;

        if ($decision === "approved") {
            $this->subject("Letter Approved by $role  $userString");

            // Set the HTML content for the userString
            $this->html("<p>you letter with title : $letterTitle is Approved by $role $userString </p><p> Feedback : $feedback");
        } else {
            $this->subject("Letter  Rejected by $role $userString");

            // Set the HTML content for the userString
            $this->html("<p>you letter with title : $letterTitle is Rejected by $role $userString </p><p> Feedback : $feedback");
        }
       

        return $this;
    }
}
