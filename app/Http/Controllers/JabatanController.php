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
}