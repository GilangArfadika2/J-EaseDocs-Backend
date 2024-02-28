<?php
namespace App\Repositories;

use App\Models\Otp;
use OTPHP\TOTP;
use Carbon\Carbon;

class OtpRepository
{
    public function generateOtp($email)
    {
        // Generate OTP logic
        $totp = TOTP::create();
        $otp = $totp->now(); //
        $expired_at = Carbon::now()->addMinutes(120);
        $data = [
            'code' => $otp ,
            'email' => $email,
            'expired_at' => $expired_at,
        ];

        Otp::create($data);

        return $otp;

    }

    // public function storeOtp($email, $otp, $expiration)
    // {
    //     // Store OTP in the database logic
    // }

    public function getOtpByEmail($email)
    {
        // Retrieve OTP by email logic
        return Otp::where('email', $email)->first();
    }

    public function verifyOtp($email, $otp)
    {
        $ExistingOtp = $this->getOtpByEmail($email);
        if ($otp && $otp ==  $ExistingOtp->code) {
            if (Carbon::now()->lt($ExistingOtp->expired_at)){
                $ExistingOtp -> delete();
                return true;
            }
        }

        return false;
    }
}
