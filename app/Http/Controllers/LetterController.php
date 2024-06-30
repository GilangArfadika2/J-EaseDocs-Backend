<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\LetterRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;
use Spatie\PdfToText\Pdf;
use App\Repositories\OtpRepository;
use App\Repositories\LogRepository;
use App\Repositories\AuthRepository;
use App\DTO\Letter\TemplateFieldDTO;
use App\Repositories\NotifikasiRepository;
// use Dompdf\Dompdf;

use Illuminate\Support\Facades\File;
use Ibnuhalimm\LaravelPdfToHtml\Facades\PdfToHtml;
use Illuminate\Support\Facades\View;
use App\Validations\LetterValidation;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use App\Models\Notifikasi;
use App\Mail\NotifMail;
use App\Repositories\LetterTemplateRepository;
use Docxtemplater\Docxtemplater;
use Symfony\Component\HttpFoundation\StreamedResponse;
use PhpOffice\PhpWord\TemplateProcessor;
use TCPDF;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Carbon\Carbon;
use App\Jobs\GenerateDocumentJob;
use App\Models\Log;
class LetterController extends Controller
{
    protected $letterRepository;
    protected $letterTemplateRepository;
    // protected $dompdf;
    protected $otpRepository;
    protected $authRepository;
    protected $notifikasiRepository;
    protected $logRepository;
    public function __construct(LetterRepository $letterRepository,LetterTemplateRepository $letterTemplateRepository , OtpRepository $otpRepository, AuthRepository $authRepository, NotifikasiRepository $notifikasiRepository
    ,LogRepository $logRepository)
    {
        $this->letterRepository = $letterRepository;
        // $this->dompdf = $dompdf;
        $this->otpRepository = $otpRepository;
        $this->authRepository = $authRepository;
        $this->notifikasiRepository = $notifikasiRepository;
        $this->letterTemplateRepository = $letterTemplateRepository;
        $this->logRepository = $logRepository;
    }

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

    public function getLetterBarcodeDetail($nomorSurat){

        try {

            $letter = $this->letterRepository->getArsipById($nomorSurat);
            $letterTemplate = $this->letterTemplateRepository->getById($letter->id_template_surat);
           
            $trimmedStringApproval = trim($letterTemplate->id_approval, '{}');
            $integerApproval = explode(',', $trimmedStringApproval);
            $listApprovalId = array_map('intval', $integerApproval);
            $listUserName = [];
            foreach ($listApprovalId as $id_approval) {
                $user = $this->authRepository->getUserById( $id_approval);
                $listUserName[] = $user->name . " (" . $user->jabatan . ")";
            }
            $approvalString = implode(', ', $listUserName);
            if  ($letter === null) {
                return response()->json(['message' => 'letter not found' ],400);
            }
            $data = ["nomor_surat" => $letter->nomor_surat , "perihal" => $letterTemplate->perihal,"approval" =>  $approvalString , "tanggal_penyetujuan" => $letter->approved_at ];
            return response()->json(['message' => 'letter fetched succesfully' , 'data' => $data],200);
        }  catch (Exception $e){

            return response()->json(['message' => $e ],500);
        }
    
    }


    public function CreateLetter(Request $request){

        try { 
           error_log("masuk!!");
            $data = $request->all();
            $validator = Validator::make($data, LetterValidation::createLetterRules());
            if ($validator->fails()) {
                return response()->json(['message' => 'input json is not validated', 'errors' => $validator->errors()], 400);
            }
           // Access nested data safely
            $formData = $request->input('data');

            // Retrieve dynamic validation rules
            $formField = $this->letterTemplateRepository->getById($request->input("id_template_surat"))->isian;
            $rules = json_decode($formField, true);

            // Validate nested data against dynamic rules
            $formDataValidator = Validator::make($formData, $rules);

            if ($formDataValidator->fails()) {
                return response()->json(['message' => 'input json is not validated', 'errors' => $formDataValidator->errors()], 400);
            }
            $nomorSurat = "J-ESD".$this->generateNomorSurat(10);

           $letter = $this->letterRepository->createLetter($data,$nomorSurat);
           $createdOTP = $this->otpRepository->generateOtp($data['email_atasan_pemohon'],$letter->id);
           $link = "http://localhost:3000/J-EaseDoc/letter/verify-otp/" . $createdOTP['id'] ."/" . $data['email_atasan_pemohon'];
          
           $startingLog = new Log();
           $startingLog->letter_id = $letterId;
           $startingLog->status ="pending";
           $startingLog->user_id = $letterTemplate->id_admin;
           Mail::to($data['email_atasan_pemohon'])->send(new OtpMail($createdOTP['code'] , $link));
           GenerateDocumentJob::dispatch($letter, $this->authRepository, $this->letterRepository, $this->letterTemplateRepository)->delay(now()->addSeconds(10)); // Example delay
           
          

           $this->logRepository->create($startingLog->getAttributes());
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
        $link = "http://localhost:3000/J-EaseDoc/letter/verify-otp/" . $createdOTP['id'] ."/" . $member['email'];
                // error_log($link);
        Mail::to($createdOTP['email'])->queue(new OtpMail($createdOTP['code'] , $link));

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
        // error_log($code);
        // error_log($id);

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

        try {

        
            $validator = Validator::make($request->all(), LetterValidation::updateDecisionRules());
            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()], 400);
            }
    
        $decision = $request->input('decision');
        $role = $request->input('role');
        $letterId = $request->input('letter_id');
        $email = $request->input('email');
        $feedback = $request->input('message');
        $isGenerateDocument = false;
        
        
        // error_log($userId);
        $letter = $this->letterRepository->getLetterByID($letterId);
        $letterTemplate = $this->letterTemplateRepository->getById($letter->id_template_surat);
        $header =   $letterTemplate->perihal;


        $notifikasiNext = false ;
        $notifikasi = false;

        $isDecisionAssigned = false;
        
      
        $trimmedStringChecker = trim($letterTemplate->id_checker, '{}');
        $integerChecker = explode(',', $trimmedStringChecker);
        $listCheckerId = array_map('intval', $integerChecker);

        $trimmedStringApproval = trim($letterTemplate->id_approval, '{}');
        $integerApproval = explode(',', $trimmedStringApproval);
        $listApprovalId = array_map('intval', $integerApproval);

        $userId = 0;
        if ($role === "checker" || $role === "approval"){
            $existingUser = $this->authRepository->getUserByEmail($email);
            if ($existingUser->role !== $role) {
                return response()->json(['message' => "invalid email"], 400);
            }
            $listLetterUser = [];
            if ($role =="checker"  ){
                $listLetterUser =  $listCheckerId;
                
            } else {
                $listLetterUser = $listApprovalId;
            }
            if (!in_array($existingUser->id, $listLetterUser)) {
                return response()->json(['message' => "user is unauthorized to give approval to letter"], 400);
            }
            $userId = $existingUser->id;
            
        }

        if ($role == "atasan_pemohon"){
            if( $decision == "approved"){
                foreach ( $listCheckerId as $id_checker){
                    $notifikasi = new Notifikasi();
                    $notifikasi->user_id = $id_checker;
                    $notifikasi->letter_id = $letterId;
                    $notifikasi->decision = "on-progress";

                    $notifikasiArray = $notifikasi->getAttributes();
                    $this->notifikasiRepository->create( $notifikasiArray);

                    $progressLog = new Log();
                    $progressLog->letter_id = $letterId;
                    $progressLog->status ="on-progress";
                    $progressLog->user_id = $letterTemplate->id_admin;
                    $this->logRepository->create($progressLog->getAttributes());
                    
                    $this->letterRepository->updateLetterStatus($letterId, "on-progress");
                }   

            } else {
                $progressLog = new Log();
                $progressLog->letter_id = $letterId;
                $progressLog->status ="rejected";
                $progressLog->user_id = $letterTemplate->id_admin;
                $this->logRepository->create($progressLog->getAttributes());
                 $this->notifikasiRepository->deleteNotifikasiByListUserAndLetterId($listCheckerId, $letterId);
                 $this->letterRepository->updateLetterStatus($letterId, $decision);

                 $isGenerateDocument = true;
            }
            
            // $listUser = $this->authRepository->getUserByListId( $listCheckerId);  
            $userString =  $letter->nama_atasan_pemohon . " NIP : " . $letter->nip_atasan_pemohon;
            Mail::to($letter->email_pemohon)->queue(new NotifMail($header, $decision, $role,  $userString ,$feedback));
            
        } else {
            $notifikasi =  $this->notifikasiRepository->getNotifikasiByUserAndLetterId($userId, $letterId);

            $notifikasi->decision = $decision;
            
            $notifikasiArray = (array) $notifikasi;
            $this->notifikasiRepository->update($notifikasi->id,$notifikasiArray);
            
            $listUserId = [];
            if ($role == "checker"){
                $listUserId =   $listCheckerId;
            }
            else {
                $listUserId =  $listApprovalId;
            }

            $listNotifikasi =  $this->notifikasiRepository->getNotifikasiByListUserAndLetterId($listUserId, $letterId);
            $isFinalized = true;
            $isRejected = false;
            $listUserRejected = [];
            $listUserAccepted = [];
            $listUser = [];

            foreach ( $listNotifikasi as $notifikasi){
                if ($notifikasi->decision == "rejected"){
                    $isRejected = true;
                    $listUserRejected[] =$this->authRepository->getUserById($notifikasi->user_id)->name;
                } else {
                    $listUserAccepted[] =$this->authRepository->getUserById($notifikasi->user_id)->name;
                }
            }
            if (!$isRejected){
                $listUser =  $listUserAccepted;
            } else {
                $listUser =  $listUserRejected;
            }
            if ($isFinalized){
                
                if (!$isRejected){
                     $listUserString = implode(', ', $listUser);
                        Mail::to($letter->email_pemohon)->queue(new NotifMail($header, $decision, $role,  $listUserString ,$feedback)); 
                          Mail::to($letter->email_atasan_pemohon)->queue(new NotifMail($header, $decision, $role,  $listUserString ,$feedback)); 
                    if ($role === "checker"){
                        
                        foreach ( $listApprovalId as $id_approval){
                            $notifikasi = new Notifikasi();
                            $notifikasi->user_id = $id_approval;
                            $notifikasi->letter_id = $letterId;
                            $notifikasi->decision = "on-progress";
            
                            $notifikasiArray = $notifikasi->getAttributes();
                            $this->notifikasiRepository->create( $notifikasiArray);
                        }   

                        
                        $logChecker = new Log();
                        $logChecker->letter_id = $letterId;
                        $logChecker->status ="approved";
                        $logChecker->user_id = $letterTemplate->id_checker;

                        $this->logRepository->create($logChecker->getAttributes());

                    } else {
                        $nomorSurat = "J-ESD".$this->generateNomorSurat(10);
                        // $this->letterRepository->updateLetterNomorSurat($letterId,  $nomorSurat);
                        $createdOTP = $this->otpRepository->generateOtp($letter->email_pemohon,$letterId);
                        $link = "http://localhost:3000/J-EaseDoc/letter/arsip/" . $createdOTP['id'] ."/" . $letter->email_pemohon;
                        //                 Mail::to($createdOTP['email'])->queue(new OtpMail($createdOTP['code'] , $link));
                        $log = new Log();
                        $log->letter_id = $letterId;
                        $log->status ="approved";
                        $log->user_id = $letterTemplate->id_approval;
                        $this->logRepository->create($log->getAttributes());
                        $this->letterRepository->updateLetterStatus($letterId, $decision);
                        $isGenerateDocument = true;                    }
                    
                } else {

                    $listUserString = implode(', ', $listUser);
                    Mail::to($letter->email_pemohon)->queue(new NotifMail($header, $decision, $role,  $listUserString ,$feedback)); 
                    Mail::to($letter->email_atasan_pemohon)->queue(new NotifMail($header, $decision, $role,  $listUserString ,$feedback)); 
                    
                   
                    //  $this->letterRepository->updateLetterNomorSurat($letterId,null);
                     $this->letterRepository->updateLetterStatus($letterId, $decision);
                     $isGenerateDocument = true;                    
                     $log = new Log();
                     $log->letter_id = $letterId;
                     $log->status = "rejected";
                     if ($role == "checker"){
                        $log->user_id = $letterTemplate->id_checker;
                     } else {
                        $log->user_id = $letterTemplate->id_approval;
                     }
                     $this->logRepository->create($log->getAttributes());
                }
                $this->notifikasiRepository->deleteNotifikasiByListUserAndLetterId($listUserId, $letterId);
            } else {
               
                $log = new Log();
                $log->letter_id = $letterId;
                $log->status = "rejected";
                if ($role == "checker"){

                    $log->user_id = $letterTemplate->id_checker;
                } else {
                    $log->user_id = $letterTemplate->id_approval;
                }
    
                // $this->letterRepository->updateLetterNomorSurat($letterId,null);
                $isGenerateDocument = true;           
             }

         

            

        }
        if ($isGenerateDocument ){
            $letterNew = $this->letterRepository->getLetterByID($letterId);
            $error = $this->generateDocumentLetter($letterNew,$decision);
            if ($error != null){
                return response()->json(['message' =>  $error], 500);
            }
   
        }
       

        return response()->json(['message' => 'update letter success'], 200);
    }
        catch (Exception $e) {
            return response()->json(['message' =>  $e->getMessage()], 500);
        }
       

    
        
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
    public function getLetterByReceiptNumber(Request $request){
        try {
            $id = $request->input('id');
            $letter = $this->letterRepository->getLetterByReceiptNumber($id);
    
            // Decode the JSON data
            $letter->data = json_decode($letter->data);
    
            return response()->json(['message' => 'Letter fetched successfully', 'data' => $letter ], 200);
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
    
            return response()->json(['message' => 'Letter fetched successfully', 'data' => $letter ], 200);
        } catch (Exception $e) {
            return response()->json(['message' =>  $e->getMessage()], 500);
        }
    }

    // public function getLetterFileByID(Request $request){

    //     try {
    //         $id = $request->input('id');
    //         $letter = $this->letterRepository->getLetterByID($id);
    
    //         // Decode the JSON data
    //         $letter->data = json_decode($letter->data);
    //         $letter->member = json_decode($letter->member);
    
    //         return response()->json(['message' => 'Letter fetched successfully', 'data' => $letter ], 200);
    //     } catch (Exception $e) {
    //         return response()->json(['message' =>  $e->getMessage()], 500);
    //     }
    // }

    public function getLetterTemplateField($id){
        try {
            // $id = $request->input('id');
            $letter = $this->letterTemplateRepository->getById($id);
    
            // Decode the JSON data
            // $letter->isian= json_decode($letter->isian);
            // $letter->member = json_decode($letter->member);

            $isian = json_decode($letter->isian);

            $listTemplateField = [];
            foreach ($isian as $isianName => $validation) {
                $control_id =  str_replace('_', '-', $isianName);
                $label =  mb_convert_case(str_replace('_', ' ', $isianName), MB_CASE_TITLE);
                $type = "text";
                $optionArray = [];
                if (!is_array($validation)){
                    if (strpos($validation, 'in:') !== false) {
                        $type = "option";
                        $optionString = explode(":", explode("|", $validation)[2])[1];
                        $optionArray = explode(",",  $optionString);
                    }
                    if (strpos($validation, 'date') !== false) {
                        $type = "date";
                    }
                    $listTemplateField[] = new TemplateFieldDTO( $control_id , $type,$label,  $isianName, $optionArray, [],[] );
                } else {
                    $type = "array";
                    $templateFieldArrayHead = ["No"];
                    $templateFieldArrayData = [];
                    $isianArray = $validation[0];
                    // error_log( $isianArray );
                    foreach ($isianArray as $isianArrayItemName => $isianArrayItemValidation) {
                        $controlIdItem =  str_replace('_', '-', $isianArrayItemName);
                        $labelItem =  mb_convert_case(str_replace('_', ' ', $isianArrayItemName), MB_CASE_TITLE);
                        $typeItem = "text";
                        $optionArrayItem = [];
                        if (strpos($isianArrayItemValidation, 'in:') !== false) {
                            $typeItem = "option";
                            $optionString = explode(":", explode("|", $isianArrayItemValidation)[2])[1];
                            $optionArrayItem = explode(",",  $optionString);
                        }
                        if (strpos($isianArrayItemValidation, 'date') !== false) {
                            $typeItem = "date";
                        }
                        $templateFieldArrayHead[] = $isianArrayItemName;
                        $templateFieldArrayData[] = new TemplateFieldDTO( $controlIdItem , $typeItem,$labelItem,  $isianArrayItemName, $optionArrayItem, [],[] );
                        
                    }
                    $listTemplateField[] = new TemplateFieldDTO( $control_id , $type,$label,  $isianName, $optionArray,$templateFieldArrayHead , $templateFieldArrayData  );
                }
               
            }


            return [null,$listTemplateField];
    
            // return response()->json(['message' => 'Letter fetched successfully', 'data' =>  $listTemplateField], 200);
        } catch (Exception $e) {
            // return response()->json(['message' =>  $e->getMessage()], 500);
            return [$e,null];
    
        }
    }

    public function getLetterTemplateFieldByID($id){

        try {
            $data = $this->getLetterTemplateField($id);
            if ($data[0] === null) {
                return response()->json(['message' => 'Letter fetched successfully', 'data' =>  $data[1]], 200);
            } else {
                return response()->json(['message' => 'Letter fetched successfully', 'data' =>  $data[0]->getMessage()], 500);
            }

            
        } catch (Exception $e) {
            return response()->json(['message' =>  $e->getMessage()], 500);
        }
    }

    public function getUnreadNotificationCount(Request $request){

        try {
            if (!$request->hasCookie('jwt_token')) {
                return response()->json(['message' => 'Missing token cookie'], 400);
            }

            $token = $request->cookie('jwt_token');

            $user = Auth::guard('web')->setToken($token)->user();

            $countUnread = $this->notifikasiRepository->countUnread($user->id);
            return response()->json(['message' =>  "unread notification count fetched succesfully", "data" => $countUnread], 200);
         }
        catch (Exception $e) {
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
                $this->notifikasiRepository->markAsRead($notifikasi->id);

            }

            $listLetter = $this->letterRepository->getLetterByBulkId($listLetterId, $user->id);
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
    


    

    public function generateDocumentLetter($letter, $decision)
    {

        try {
        $letterTemplate = $this->letterTemplateRepository->getById($letter->id_template_surat);
        $attachment = $letterTemplate->attachment;
        // Load the DOCX template
        // $templatePath = storage_path("app\\public\\template\\" . $attachment);
        $templatePath = public_path("template/" . $attachment);

        
        // Create a TemplateProcessor instance
        $templateProcessor = new TemplateProcessor($templatePath);

        
        // Define data to render
        $data =  json_decode( $letter->data ,true);

        // Fill the template with data
        foreach ($data as $key => $value) {
            // error_log($value);
            
            // $templateProcessor->setValue($key, $value);
            if (is_array($value)) {
                // If $value is an array, assume it represents data for cloning rows in a table
                $rowCount = count($value);
                // Clone the row in the table identified by $key for each item in the array
                $templateProcessor->cloneRow($key, $rowCount);
                // Loop through each item in the array and set the values in the cloned rows
                foreach ($value as $index => $item) {
                    foreach ($item as $itemKey => $itemValue) {
                        error_log($itemKey . ": ". $itemValue);
                        if ($itemKey !== $key){
                             // Set the value in the |template for each item attribute
                            $templateProcessor->setValue($itemKey . '#' . ($index + 1), $itemValue);
                        }
                        // Assuming attributes are named as attribute_1, attribute_2, ...
                    }
                    $templateProcessor->setValue($key . '#' . ($index + 1),$index + 1 );
                }
            } else {
                error_log($value);
                $templateProcessor->setValue($key, $value);
            }
        }
        $tanggal_permohonan = Carbon::parse($letter->created_at)->translatedFormat('d F Y');
        $templateProcessor->setValue("tanggal_permohonan", $tanggal_permohonan);
        $templateProcessor->setValue("tanggal_permohonan", $letter->created_at);
        $templateProcessor->setValue("keputusan", $letter->status);
        if ($decision === "approved" &&  $decision === "rejected"){
            $tanggal_penyetujuan = Carbon::parse($letter->approved_at)->translatedFormat('d F Y');
             $templateProcessor->setValue("tanggal_penyetujuan", $tanggal_penyetujuan);
             $templateProcessor->setValue("keputusan",$decision);
        } else {
            $templateProcessor->setValue("tanggal_penyetujuan", "");
            $templateProcessor->setValue("keputusan", $decision);
        }
        $templateProcessor->setValue("nama_pemohon",$letter->nama_pemohon);
        $templateProcessor->setValue("email_pemohon",$letter->email_pemohon);
        $templateProcessor->setValue("nip_pemohon",$letter->nip_pemohon);
        $templateProcessor->setValue("nama_atasan_pemohon", $letter->nama_atasan_pemohon);
        $templateProcessor->setValue("nip_atasan_pemohon", $letter->nip_atasan_pemohon);
        $templateProcessor->setValue("jabatan_atasan_pemohon", $letter->jabatan_atasan_pemohon);
        $id_approval = $letterTemplate->id_approval;
        $id_approval_array = explode(',', str_replace(array('{', '}'), '', $id_approval));
        if (count($id_approval_array) > 1) {
            
            $approvals = [];
            
            foreach ($id_approval_array as $id) {
                $approval = $this->authRepository->getUserById((int)$id);
                
                // $approvals[] = $approval;
                $templateProcessor->setValue("nama_kepala_divisi",$approval->name);
                $templateProcessor->setValue("jabatan_kepala_divisi",$approval->jabatan);
                break;
            }
         
        } else {
          
            $id_approvalInt = (int)str_replace(array('{', '}'), '', $id_approval);
            $approval = $this->authRepository->getUserById($id_approvalInt);

            $templateProcessor->setValue("nama_kepala_divisi",$approval->name);
            $templateProcessor->setValue("jabatan_kepala_divisi",$approval->jabatan);

        }
        // $id_approval = (int) str_replace(array('{', '}'), '', $id_approval);
        // $approval = $this->authRepository->getUserById($id_approval);
        // $templateProcessor->setValue("nama_kepala_divisi",$approval->name);
        // $templateProcessor->setValue("jabatan_kepala_divisi",$approval->jabatan);

        if ($decision === "approved"){
            $link = 'http://localhost:3000/api/J-EaseDoc/letter/barcode/' . $letter->nomor_surat;

                            // Generate a QR code
            $qrCode = new QrCode($link);
            $qrCodeFilePath = public_path("qrcode" . "_". $letter->id . ".png");

            $writer = new PngWriter();

            // Write the QR code to a file
            $result = $writer->write($qrCode);
            $result->saveToFile($qrCodeFilePath );
            $templateProcessor->setImageValue('barcode', array('path' => $qrCodeFilePath, 'width' => 200, 'height' => 200));
        } else {
            $templateProcessor->setValue('barcode',"not yet assigned");
        }

        
        $outputPath = public_path($attachment . "_" . $letter->id . ".docx");
 +       $templateProcessor->saveAs($outputPath);



       
        $pdfPath = public_path($attachment . "_" . $letter->id . ".pdf");

        if (file_exists($pdfPath)) {
            unlink($pdfPath);
        }

        // Command to convert DOCX to PDF using LibreOffice
        $command = "soffice --headless --convert-to pdf \"$outputPath\" --outdir \"" . dirname($pdfPath) . "\" 2>&1";

        error_log("command libre office : " .  $command);

        // Execute the command
        exec($command, $output, $returnCode);
        if ($returnCode !== 0) {
            return "Command failed with return code: " . $returnCode . " : " . implode("\n", $output);
       } else {
           return null;
       }
    } catch (Exception $e){
        return $e->getMessage();
    }
    }
    public function generateDocument(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), LetterValidation::GetLetterAttachment());
            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()], 400);
            }

            $letter = $this->letterRepository->getLetterById($request->input("letter_id"));
            $letterTemplate = $this->letterTemplateRepository->getById($letter->id_template_surat);
            $attachment = $letterTemplate->attachment;

            
            $outputPath = public_path($attachment . "_" . $letter->id . ".docx");
            // $templateProcessor->saveAs($outputPath);


    
           
            $pdfPath = public_path($attachment . "_" . $letter->id . ".pdf");

            // Command to convert DOCX to PDF using LibreOffice
            // $command = "soffice --headless --convert-to pdf \"$outputPath\" --outdir \"" . dirname($pdfPath) . "\" 2>&1";

            // Execute the command
            // exec($command, $output, $returnCode);

            
            
            // Get Docx and PDF Contents
            $pdfContent = file_get_contents($pdfPath);
            $docxContent = file_get_contents($outputPath);
            if ($pdfContent != null &&  $docxContent != null ){
                // Return the PDF content or handle it accordingl
                return response()->json(['message'=> "attachment fetched succesfully",'pdf_content' => base64_encode($pdfContent), 'docx_content' => base64_encode($docxContent)  ], 200);
            } else {
                // Command execution failed, handle the error
                return response()->json(['Error' => "Error executing LibreOffice command: " . implode("\n", $output)], 500);
                
            }
            
    

            // return response()->json(['message' => 'Document generated successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['message' =>  $e->getMessage()], 500);
        }
    }

}