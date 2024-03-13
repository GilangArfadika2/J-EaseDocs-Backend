<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $otpCode;
    protected $link;

    /**
     * Create a new message instance.
     *
     * @param string $otpCode
     * @param string $link
     */
    public function __construct($otpCode, $link)
    {
        $this->otpCode = $otpCode;
        $this->link = $link; // corrected assignment
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $otpCode = $this->otpCode;
        $link = $this->link;

        // Set the email subject
        $this->subject('verification for request Letter');

        // Set the HTML content for the email
        $this->html("<p>Your OTP code is: $otpCode</p> <p>input your OTP code in this link $link</p>");

        return $this;
    }
}
