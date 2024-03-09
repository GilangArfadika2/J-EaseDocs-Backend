<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\LetterRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;
use Spatie\PdfToText\Pdf;
use App\Repositories\OtpRepository;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\File;
use Ibnuhalimm\LaravelPdfToHtml\Facades\PdfToHtml;
use Illuminate\Support\Facades\View;
use App\Validations\LetterValidation;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use App\Mail\NotifMail;
class LetterController extends Controller
{
    protected $letterRepository;
    protected $dompdf;
    protected $otpRepository;

    public function __construct(LetterRepository $letterRepository, Dompdf  $dompdf , OtpRepository $otpRepository)
    {
        $this->letterRepository = $letterRepository;
        $this->dompdf = $dompdf;
        $this->otpRepository = $otpRepository;
    }


    public function createLetter(Request $request){

        try {
            $data = $request->all();
            $validator = Validator::make($data, LetterValidation::createLetterRules());
            if ($validator->fails()) {
                return response()->json(['message' => 'input json is not validated', 'errors' => $validator->errors()], 400);
            }
           $letter = $this->letterRepository->createLetter($data);
           foreach ($data['member'] as $member) {

            if ($member['decision'] === 'on-progress' && $member['role'] === 'atasan_pemohon') {
                error_log($letter->id);
                $createdOTP = $this->otpRepository->generateOtp($member['email'],$letter->id);
                Mail::to($createdOTP->email)->send(new OtpMail($createdOTP->code));
                break;
            }
        }
            return response()->json(['message' => 'Letter registered successfully', 'data' => $createdOTP->id], 200);
        } catch (Exception $e) {
            return response()->json(['message' =>  $e->getMessage()], 500);
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
            return response()->json(['message' => 'OTP verification successful.' ,'data' => $letter ,'role' => 'atasan_pemohon' ], 200);
        } else {
            // OTP is invalid or expired
            return response()->json(['message' => 'OTP verification failed.'], 400);
        }
    }

    public function updateDecision(Request $request) {
        $validator = Validator::make($request->all(), LetterValidation::updateDecisionRules());
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $decision = $request->all()['decision'];

        $role = $request->all()['role'];

        $letter =  $this->letterRepository->getLetterByID( $request->all()['letter_id']);

        $pemohonEmail = "";
        $atasanPemohonEmail = "";
        $header = "";

      

        $memberArray = json_decode($letter->member, true);
        $dataArray = json_decode($letter->data,true);
        foreach ($dataArray as $data) {
            if (isset($data["header"])) {
                $header = $data["header"];
            }
        }
        foreach ($memberArray as $member){
            if ($member["role"] === "pemohon") {
                $pemohonEmail = $member["email"];
            }
            if ($member["role"] == "atasan_pemohon"){
                $atasanPemohonEmail = $member["email"];
            }

            
        }
        error_log("the email is " . $pemohonEmail);

        
            if ($role == "atasan_pemohon"){
                Mail::to($pemohonEmail)->send(new NotifMail($header, $decision,$role));
            }
            else {
                Mail::to($pemohonEmail)->send(new NotifMail($header, $decision,$role));
                Mail::to($atasanPemohonEmail)->send(new NotifMail($header, $decision,$role));
            }

        
            foreach ($memberArray as $member){
                if ($member["role"] ===  $role) {
                    $member["decision"] = $decision;
                    
                }

                if ($decision === "approved"){
                    if ($member["decision"] ===  "on-progress") {
                       $this->letterRepository->updateLetterStatus($letter->id, "waiting for " . $member["role"] . " approval" );
                        break;
                    }
                } else {
                    $this->letterRepository->updateLetterStatus($letter->id, "form is rejected by" . $member["role"] );
                    break;
                }
                
            }

            return response()->json(['message' => 'update letter success'], 200);

       

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
            return response()->json(['message' => 'Letter fetched successfully', 'data' => $letter ], 200);
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