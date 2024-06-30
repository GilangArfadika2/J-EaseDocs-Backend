<?php

namespace App\Jobs;

use App\Repositories\AuthRepository;
use App\Repositories\LetterRepository;
use App\Repositories\LetterTemplateRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Facades\File;
use Ibnuhalimm\LaravelPdfToHtml\Facades\PdfToHtml;
use Illuminate\Support\Facades\View;
use App\Validations\LetterValidation;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use App\Models\Notifikasi;
use App\Mail\NotifMail;
use Docxtemplater\Docxtemplater;
use Symfony\Component\HttpFoundation\StreamedResponse;
use PhpOffice\PhpWord\TemplateProcessor;
use TCPDF;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Carbon\Carbon;
class GenerateDocumentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $letter;
    protected $authRepository;
    protected $letterRepository;
    protected $letterTemplateRepository;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($letter, AuthRepository $authRepository, LetterRepository $letterRepository, LetterTemplateRepository $letterTemplateRepository)
    {
        $this->letter = $letter;
        $this->authRepository = $authRepository;
        $this->letterRepository = $letterRepository;
        $this->letterTemplateRepository = $letterTemplateRepository;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Call the generateDocument method
        $this->generateDocument($this->letter);
    }

    /**
     * Generate the document.
     *
     * @param mixed $letter
     * @return void
     */
    private function generateDocument($letter)
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
            // if ($decision === "approved" &&  $decision === "rejected"){
            //     $tanggal_penyetujuan = Carbon::parse($letter->approved_at)->translatedFormat('d F Y');
            //      $templateProcessor->setValue("tanggal_penyetujuan", $tanggal_penyetujuan);
            //      $templateProcessor->setValue("keputusan",$decision);
            // } else {
            //     $templateProcessor->setValue("tanggal_penyetujuan", "");
            //     $templateProcessor->setValue("keputusan", $decision);
            // }
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
    
            // if ($decision === "approved"){
            //     $link = 'http://localhost:3000/api/J-EaseDoc/letter/barcode/' . $letter->nomor_surat;
    
            //                     // Generate a QR code
            //     $qrCode = new QrCode($link);
            //     $qrCodeFilePath = public_path("qrcode" . "_". $letter->id . ".png");
    
            //     $writer = new PngWriter();
    
            //     // Write the QR code to a file
            //     $result = $writer->write($qrCode);
            //     $result->saveToFile($qrCodeFilePath );
            //     $templateProcessor->setImageValue('barcode', array('path' => $qrCodeFilePath, 'width' => 200, 'height' => 200));
            // } else {
                $templateProcessor->setValue('barcode',"not yet assigned");
            // }
    
            
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
}}
