<?php
namespace App\Repositories;

use App\Models\Otp;
use OTPHP\TOTP;
use Carbon\Carbon;

class OtpRepository
{
    public function generateOtp($email,$letter_id)
    {
        // Generate OTP logic
        $totp = TOTP::create();
        $otp = $totp->now(); //
        $expired_at = Carbon::now()->addMinutes(300);
        $data = [
            'code' => $otp ,
            'email' => $email,
            'expired_at' => $expired_at,
            'id' => $this->generateInviteLinkID(16),
            'letter_id' => $letter_id
        ];

        $createdOTP = Otp::create($data);

        return $createdOTP;

    }


    public function getOtpById($id)
    {
        // Retrieve OTP by email logic
        return Otp::where('id', $id)->first();
    }

    public function verifyOtp($id, $otp)
    {
        $ExistingOtp = $this->getOtpById($id);
        if ($otp && $otp ==  $ExistingOtp->code) {
            if (Carbon::now()->lt($ExistingOtp->expired_at)){
                $ExistingOtp -> delete();
                return [$ExistingOtp->letter_id, true];
            }
        }

        return [$ExistingOtp->letter_id, false];
    }
    function generateInviteLinkID($length) {
        // Calculate the number of bytes required to encode the desired length of characters
        $numBytes = $length * 6 / 8;
        if ($length % 8 != 0) {
            $numBytes++;
        }
    
        // Generate random bytes
        $bytes = random_bytes($numBytes);
    
        // Encode the random bytes using base64 URL encoding
        $encoded = base64_encode($bytes);
    
        // Make the string URL-safe by replacing characters '+', '/' and '='
        $encoded = str_replace(['+', '/', '='], ['-', '_', ''], $encoded);
    
        // Truncate the encoded string to the desired length
        $encoded = substr($encoded, 0, $length);
    
        return $encoded;
    }
}
