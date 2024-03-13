<?php
namespace App\Http\Controllers;
use App\Repositories\OtpRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
class OtpController extends Controller
{
    protected $otpRepository;

    public function __construct(OtpRepository $otpRepository)
    {
        $this->otpRepository = $otpRepository;
    }

    public function generateOTP(Request $request)
    {
        // Validation rules
        $rules = [
            'email' => 'required|email',
           
            // Add other rules as needed
        ];

        // Validate the request data
        $validator = Validator::make($request->all(), $rules);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $email = $request->input('email');
            $otpCode = $this->otpRepository->generateOtp($email);

            Mail::to($request->email)->send(new OtpMail($otpCode));
            // Return success response
            return response()->json(['message' => 'OTP stored & Send successfully: ' . $otpCode], 200);
        } catch (UniqueConstraintViolationException $e) {
            // Handle the integrity constraint violation (duplicate entry)
            return response()->json(['error' => 'Email already exists.'], 422);
        }
    }

    public function verifyOTP(Request $request)
    {
        // Validation rules
        $rules = [
            'email' => 'required|email',
            'otp' => 'required|numeric',
        ];

        // Validate the request data
        $validator = Validator::make($request->all(), $rules);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Retrieve email and OTP from the request
        $email = $request->input('email');
        $otp = $request->input('otp');

        // Verify OTP
        if ($this->otpRepository->verifyOtp($email, $otp)) {
            // OTP is valid
            return response()->json(['message' => 'OTP verification successful.'], 200);
        } else {
            // OTP is invalid or expired
            return response()->json(['error' => 'OTP verification failed.'], 400);
        }
    }
}
