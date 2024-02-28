<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $otpCode;

    /**
     * Create a new message instance.
     *
     * @param string $otpCode
     */
    public function __construct($otpCode)
    {
        $this->otpCode = $otpCode;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $otpCode = $this->otpCode;

        // Set the email subject
        $this->subject('Otp Mail');

        // Set the HTML content for the email
        $this->html("<p>Your OTP code is: $otpCode</p>");

        return $this;
    }
}
