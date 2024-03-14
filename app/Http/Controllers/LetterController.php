<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\LetterRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;
use Spatie\PdfToText\Pdf;
use App\Repositories\OtpRepository;
use App\Repositories\AuthRepository;
use App\Repositories\NotifikasiRepository;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\File;
use Ibnuhalimm\LaravelPdfToHtml\Facades\PdfToHtml;
use Illuminate\Support\Facades\View;
use App\Validations\LetterValidation;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use App\Models\Notifikasi;
use App\Mail\NotifMail;

class LetterController extends Controller
{
    protected $letterRepository;
    protected $dompdf;
    protected $otpRepository;
    protected $authRepository;
    protected $notifikasiRepository;
    public function __construct(LetterRepository $letterRepository, Dompdf  $dompdf , OtpRepository $otpRepository, AuthRepository $authRepository, NotifikasiRepository $notifikasiRepository)
    {
        $this->letterRepository = $letterRepository;
        $this->dompdf = $dompdf;
        $this->otpRepository = $otpRepository;
        $this->authRepository = $authRepository;
        $this->notifikasiRepository = $notifikasiRepository;
    }
// adawdwadwadwadwdaawdwaadawadw
    public function getAllArsip(){

        try {

            // if (!$request->hasCookie('jwt_token')) {
            //     return response()->json(['message' => 'Missing token cookie'], 400);
            // }

            // $token = $request->cookie('jwt_token');

            // $user = Auth::guard('web')->setToken($token)->user();

            $listLetter = $this->letterRepository->getAllArsip();
            return response()->json(['message' => 'letter fetched succesfully' , 'data' => $listLetter],200);
        } catch (Exception $e){

            return response()->json(['message' => $e ],500);
        }

    
    }

    public function getArsipByID($nomorSurat){

        try {

            $letter = $this->letterRepository->getArsipById($nomorSurat);
            if  ($letter === null) {
                return response()->json(['message' => 'letter not found' ],400);
            }
            return response()->json(['message' => 'letter fetched succesfully' , 'data' => $letter],200);
        } catch (Exception $e){

            return response()->json(['message' => $e ],500);
        }

    
    }


    public function CreateLetter(Request $request){

        try {
            $data = $request->all();
            $validator = Validator::make($data, LetterValidation::createLetterRules());
            if ($validator->fails()) {
                return response()->json(['message' => 'input json is not validated', 'errors' => $validator->errors()], 400);
            }
           $letter = $this->letterRepository->createLetter($data);
           foreach ($data['member'] as $member) {

            if ($member['decision'] === 'on-progress' && $member['role'] === 'atasan_pemohon') {
                // error_log($letter->id);
                $createdOTP = $this->otpRepository->generateOtp($member['email'],$letter->id);
                $link = "https://j-easedocs-frontend.vercel.app/J-EaseDoc/letter/verify-otp/" . $createdOTP['id'] ."/" . $member['email'];
                // error_log($link);
                Mail::to($createdOTP['email'])->send(new OtpMail($createdOTP['code'] , $link));
                break;
            }
        }
            return response()->json(['message' => 'Letter registered successfully', 'data' => $createdOTP['id']], 200);
        } catch (Exception $e) {
            return response()->json(['message' =>  $e->getMessage()], 500);
        }
        
        
    }

    public function resendOtp($email,$id){
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Return an error message or throw an exception for invalid email format
            return response()->json(['message'  => 'Invalid email format'],400);
        }

        $createdOTP = $this->otpRepository->resendOtp($email,$id);
        $link = "https://j-easedocs-frontend.vercel.app/J-EaseDoc/letter/verify-otp/" . $createdOTP['id'] ."/" . $member['email'];
                // error_log($link);
        Mail::to($createdOTP['email'])->send(new OtpMail($createdOTP['code'] , $link));

        return response()->json(['message' => 'Otp regenerated succesfully', 'data' => $createdOTP['id']], 200);
    }



    public function getOtpById( $id)
    {
        
        // $validator = Validator::make($request->all(), LetterValidation::getOTPIDRules());
        // $otp = $this->otpRepository->getOtpById($request->input(['id']));
        $otp = $this->otpRepository->getOtpById($id);

        if ($otp) {
            return response()->json(["message" => "OTP fetched Succesfully" ]);
        } else {
            return response()->json(['error' => 'OTP not found'], 400);
        }
    }



    public function verifyOTP(Request $request)
    {
        

        // Validate the request data
        $validator = Validator::make($request->all(), LetterValidation::verifyOTPRules());

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $id = $request->input('id');
        $code = $request->input('code');

        $verificationResult = $this->otpRepository->verifyOtp($id, $code);
        $letterID = $verificationResult[0];
        $isVerified = $verificationResult[1];
        // Verify OTP
        if ($isVerified) {
            // OTP is valid
            $letter = $this->letterRepository->getLetterByID($letterID);
            return response()->json(['message' => 'OTP verification successful.' ,'id' => $letter->id ,'role' => 'atasan_pemohon' ], 200);
        } else {
            // OTP is invalid or expired
            return response()->json(['message' => 'OTP verification failed'], 400);
        }
    }

    public function updateDecision(Request $request) {
        $validator = Validator::make($request->all(), LetterValidation::updateDecisionRules());
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
    
        $decision = $request->input('decision');
        $role = $request->input('role');
        $letterId = $request->input('letter_id');
        $email = $request->input('email');
        $userId = 0;
        if ($role === "checker" || $role === "approval"){
            $userId = $this->authRepository->getUserByEmail($email)->id;
        }
        error_log($userId);
    
        // Fetch the letter object
        $letter = $this->letterRepository->getLetterByID($letterId);
    
        // Extract necessary data from the letter object
        $memberArray = json_decode($letter->member, true);
        $dataArray = json_decode($letter->data, true);
        $header = 'form';
        // foreach ($dataArray as $data) {
        //     if (isset($data['header'])) {
        //         $header = $data['header'];
        //         break;
        //     }
        // }
    
        // Prepare email variables
        $pemohonEmail = '';
        $atasanPemohonEmail = '';
        $checkerEmail = "";
        $approvalEmail = '';

        $notifikasiNext = false ;
        $notifikasi = false;

        $isDecisionAssigned = false;
        $memberCount = count($memberArray);
        // error_log($memberCount);
        for ($i = 0; $i < $memberCount; $i++) {
            $member = &$memberArray[$i];
            switch ($member['role']) {
                case 'pemohon':
                    $pemohonEmail = $member['email'];
                    // break;
                case 'atasan_pemohon':
                    $atasanPemohonEmail = $member['email'];
                    // break;
                case 'checker':
                    $checkerEmail = $member['email'];
                    // break;
                case 'approval':
                    $approvalEmail = $member['email'];
                    // break;
            }

            if ($role === $member["role"] && $email === $member["email"] ){
                $member['decision'] = $decision;
                $isDecisionAssigned = true;
            }

            if ($isDecisionAssigned){
                if ($decision === 'rejected') {
                    $message = "letter is rejected by " . $role . " " . $member["email"];


                    if ($role == "atasan_pemohon"){
                        $this->notifikasiRepository->deleteNotifikasiByLetterId($letterId);
                    }

                    if ($role === "checker") {
                        $approvalRecord = $memberArray[$memberCount-1];
                        $approvalId = $this->authRepository->getUserByEmail($approvalRecord["email"])->id;
                        $this->notifikasiRepository->deleteNotifikasiByUserIdAndLetterId($approvalId,$letterId);
                    }

                    $this->letterRepository->updateLetterStatus($letterId, "letter is rejected by " . $role . " " . $member["email"]);

                    // $notifikasi->message = $message;
                    // $notifikasi->save();

                } else  if  ($decision === "on-progress"){
                    $message ="waiting for " . $member["role"] . " " . $member["email"] . " approval";

                    if ($role == "atasan_pemohon"){
                        $this->notifikasiRepository->deleteNotifikasiByLetterId($letterId);
                       
                        $createdOTP = $this->otpRepository->generateOtp($member['email'],$letter->id);
                        $link = "https://j-easedocs-frontend.vercel.app/J-EaseDoc/letter/verify-otp/" . $createdOTP['id'] ."/" . $member['email'];
                        // error_log($link);
                        Mail::to($createdOTP['email'])->send(new OtpMail($createdOTP['code'] , $link));
                    }

                    if ($role === "checker") {
                        $approvalRecord = $memberArray[$memberCount-1];
                        $approvalId = $this->authRepository->getUserByEmail($approvalRecord["email"])->id;
                        $this->notifikasiRepository->deleteNotifikasiByUserIdAndLetterId($approvalId,$letterId);
                    }

                    $this->letterRepository->updateLetterStatus($letterId, "waiting for " . $member["role"] . " " . $member["email"] . " approval");
                    
                    // $notifikasi->message = $message;
                    // $notifikasi->save();
                }
                else {

                    if ($i + 1 >= $memberCount) {
                        $message ="letter is approved by  " . $member["role"] . " " . $member["email"];
                        // $notifikasi->message = $message;
                        // $notifikasi->save();

                        $nomorSurat = $this->generateNomorSurat(10);
                        
                        $this->letterRepository->updateLetterNomorSurat($letterId,  $nomorSurat);

                        $createdOTP = $this->otpRepository->generateOtp($pemohonEmail,$letter->id);
                        $link = "https://j-easedocs-frontend.vercel.app/J-EaseDoc/letter/arsip/" . $createdOTP['id'] ."/" . $pemohonEmail;
                        Mail::to($createdOTP['email'])->send(new OtpMail($createdOTP['code'] , $link));
                        
                        $this->letterRepository->updateLetterStatus($letterId, "letter is approved by  " . $member["role"] . " " . $member["email"] );
                    
                    } else {

                        $nextChecker = $memberArray[$i+1];
                        error_log(json_encode($nextChecker));
                        error_log(json_encode($member));

                        $message = "waiting for " . $nextChecker["role"] . " " . $nextChecker["email"] . " approval";
                        
                        $nextId = $this->authRepository->getUserByEmail( $nextChecker["email"])->id;
                        $messageNext = "waiting for " . $nextChecker["role"] . " " . $nextChecker["email"] . " approval";

                        $notifikasiNext = $this->notifikasiRepository->getNotifikasiByUserAndLetterId($nextId, $letterId);
                        if ($notifikasiNext === null) {
                            $notifikasiNext = new Notifikasi();
                            $notifikasiNext->user_id = $nextId;
                            $notifikasiNext->letter_id = $letterId;
                            $notifikasiNext->message = $messageNext;

                            // $notifikasiNextArray = (array) $notifikasiNext;
                            // $this->notifikasiRepository->create( $notifikasiNext);
                            $notifikasiNext->save();

                        } else {
                            $notifikasiNext->message = $messageNext;
                            $notifikasiNextArray = (array) $notifikasiNext;
                            $this->notifikasiRepository->update($notifikasiNext->id,$notifikasiNextArray);
                            // $notifikasiNext->save();
                        }
                        
                        $this->letterRepository->updateLetterStatus($letterId, "waiting for " . $nextChecker["role"] . " " . $nextChecker["email"] . " approval");
                    }

                  
                  
                }
                if ($role === "checker" || $role ==="approval"){
                    if ($decision === "approved"){
                        $message ="letter is approved by  " . $member["role"] . " " . $member["email"];
                    } else if ($decision === "rejected"){
                        $message = "letter is rejected by " . $role . " " . $member["email"];
                    } else {
                        $message ="waiting for " . $member["role"] . " " . $member["email"] . " approval";
                    }
                    $notifikasi = $this->notifikasiRepository->getNotifikasiByUserAndLetterId($userId, $letterId);
                   
                    if ($notifikasi === null) {
                        $notifikasi = new Notifikasi();
                        $notifikasi->user_id = $nextId;
                        $notifikasi->letter_id = $letterId;
                        $notifikasi->message = $messageNext;

                        $notifikasiArray = (array) $notifikasi;
                        // $this->notifikasiRepository->create( $notifikasiArray);
                        $notifikasi->save();

                    } else {
                        $notifikasi->message = $message;
                        $notifikasiArray = (array) $notifikasi;
                        $this->notifikasiRepository->update($notifikasi->id,$notifikasiArray);
                        // $notifikasi->save();
                    }
                    // if ($notifikasiNext){
                    //     $notifikasiNext->save();
                    // }

                }
                break;
            }
            

        }
        if (!$isDecisionAssigned){
            return response()->json(['message' => 'Member Record Not Found'], 400);
        }
        // if ($notifikasi !== false || $notifikasi !== null ){
        //     $notifikasi->save();
        // }
        // if ($notifikasiNext !== false || $notifikasiNext !== null ){
        //     $notifikasiNext->save();
        // }
        error_log(json_encode($memberArray));
        $this->letterRepository->updateLetterMember($letterId,$memberArray);
    
        try {
            if ($role === 'atasan_pemohon') {
                Mail::to($pemohonEmail)->send(new NotifMail($header, $decision, $role, $email));
            } else {
                Mail::to($pemohonEmail)->send(new NotifMail($header, $decision, $role, $email));
                Mail::to($atasanPemohonEmail)->send(new NotifMail($header, $decision, $role, $email));
            }
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to send notification emails'], 500);
        }

    
        return response()->json(['message' => 'update letter success'], 200);
    }

    public function getLetterByIdAndRole(Request $request){
        if (!$request->hasCookie('jwt_token')) {
            return response()->json(['message' => 'Missing token cookie','log_in' => 'false'], 400);
        }

        $token = $request->cookie('jwt_token');

        $user = Auth::guard('web')->setToken($token)->user();

        // $listLetter = $this->letterRepository-
    }

    public function changeAtasanPemohonDecision(array $memberArray, $decision) {

        foreach ($memberArray as &$member) {
            if ($member['role'] === "atasan_pemohon") {
                $member['decision'] = $decision;
                break;
            }     
        }

        return $member;
    }
    

    public function updateLetter(Request $request){

        try {
            $id = $request->input('id');
            $this->letterRepository->updateLetter($id,$request->all());
            return response()->json(['message' => 'Letter updated successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['message' =>  $e->getMessage()], 500);
        }
        
        
    }

    public function getLetterByID(Request $request){

        try {
            $id = $request->input('id');
            $letter = $this->letterRepository->getLetterByID($id);
    
            // Decode the JSON data
            $letter->data = json_decode($letter->data);
            $letter->member = json_decode($letter->member);
    
            return response()->json(['message' => 'Letter fetched successfully', 'data' => $letter ], 200);
        } catch (Exception $e) {
            return response()->json(['message' =>  $e->getMessage()], 500);
        }
    }

    public function getLetterByBulkUserId(Request $request){

        try {
            if (!$request->hasCookie('jwt_token')) {
                return response()->json(['message' => 'Missing token cookie','log_in' => 'false'], 400);
            }

            $token = $request->cookie('jwt_token');

            $user = Auth::guard('web')->setToken($token)->user();
            $listNotifikasi = $this->notifikasiRepository->getNotifikasiByUserId($user->id);
            error_log(json_encode($listNotifikasi));
            $listLetterId = [];

            foreach ($listNotifikasi as $notifikasi) {
                // error_log($notifikasi);
                $listLetterId[] = $notifikasi->letter_id;
            }

            $listLetter = $this->letterRepository->getLetterByBulkId($listLetterId);
            // // Decode the JSON data
            // $letter->data = json_decode($letter->data);
            // $letter->member = json_decode($letter->member);
    
            return response()->json(['message' => 'Letter fetched successfully', 'data' => $listLetter ], 200);
        } catch (Exception $e) {
            return response()->json(['message' =>  $e->getMessage()], 500);
        }
    }
    

    public function getAllLetter(Request $request){

        try {
            $listLetter = $this->letterRepository->getAllLetter();
            return response()->json(['message' => 'Letter fetched successfully', 'data' => $letter ], 200);
        } catch (Exception $e) {
            return response()->json(['message' =>  $e->getMessage()], 500);
        }
          
    }

    public function deleteLetter(Request $request){
        try {
            $id = $request->input('id');
            $this->letterRepository->deleteLetter($id);
            return response()->json(['message' => 'Letter deleted successfully'], 500);
        } catch (Exception $e){
            return response()->json(['message' =>  $e->getMessage()], 500);
        }
    }
    // public function getLetterByNomorSurat($nomorSurat)
    // {
    //    $return response()->json()
    // }
    public function generateNomorSurat($length) {
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

    

//     public function generatePDF() {
//     // $filename = 'example2.pdf';
//     // $directory = public_path('arsip');

//     // // Create the directory if it doesn't exist
//     // if (!File::exists($directory)) {
//     //     File::makeDirectory($directory, 0755, true, true);
//     // }

//     // // Create an instance of Dompdf
//     // $dompdf = new \Dompdf\Dompdf();
    
//     // // Load HTML content from the file
//     // $html = file_get_contents(public_path('html/generated_document.html'));
    
//     // // Load HTML into Dompdf
//     // $dompdf->loadHtml($html);
    
//     // // Set paper size and orientation
//     // $dompdf->setPaper('A4', 'portrait');
    
//     // // Render the PDF
//     // $dompdf->render();
    
//     // // Output PDF
//     // $output = $dompdf->output();
    
//     // // Save PDF to public directory
//     // $filePath = $directory . '/' . $filename;
//     // $result = file_put_contents($filePath, $output);

//     // if ($result === false) {
//     //     return response()->json(['error' => 'Failed to save PDF file']);
//     // }
    
//     // // Return the filename or any other response you need
//     // return response()->json(['message' => 'PDF generated successfully', 'filename' => $filename]);
//     $content = View::make('dokumen')->render();

//     // Instantiate Dompdf
//     $dompdf = new Dompdf();

//     // Load HTML content
//     $dompdf->loadHtml($content);

//     // (Optional) Set paper size and orientation
//     $dompdf->setPaper('A4', 'portrait');

//     // Render the HTML as PDF
//     $dompdf->render();

//     // Output the generated PDF to Browser
//     return $dompdf->stream('document.pdf');
// }
//     public function getDocument(){
        
//         return view("dokumen");
//     }
}