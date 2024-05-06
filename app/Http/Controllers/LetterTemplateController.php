<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\LetterTemplateRepository;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\LetterController;
use Illuminate\Support\Facades\Cache;
class LetterTemplateController extends Controller
{
    protected $letterTemplateRepository;
    protected $letterController;

    public function __construct(LetterTemplateRepository $letterTemplateRepository, LetterController $letterController)
    {
        $this->letterTemplateRepository = $letterTemplateRepository;
        $this->letterController  = $letterController;
    }

    public function index()
    {
       
        // $cacheKey = 'all_letterTemplate';

        
        // if (Cache::has($cacheKey)) {
            
            // $templates = Cache::get($cacheKey);
        // } else {
            $templates = $this->letterTemplateRepository->getAll();

            // Cache::put($cacheKey, $templates, now()->addMinute(1));
        // }

        
        return response()->json(['message' => 'Letter templates fetched successfully', 'data' => $templates], 200);
    }

    public function getLetterTemplateById($id)
    {
        

        $template = $this->letterTemplateRepository->getById($id);
        return response()->json(['message' => 'letter fetched succesfully' , 'data' => $template],200);
    }

    public function fetchFile(Request $request)
    {
    // Check if the file exists in the storage directory
    if (Storage::exists('uploads/' .$request['attachment'])) {
        // Get the file path
        
        $validationRules = [
            'id' => 'required|numeric|exists:template_surat,id',
            'attachment' => 'required|string|exists:template_surat,attachment'
        ];
        $validator = Validator::make($request->all(),$validationRules);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        }
        $filePath = storage_path('app/uploads/' . $request['attachment']);

        $template = $this->letterTemplateRepository->getById($request['id']);

        if ($template->attachment !== $request['attachment']){
            return response()->json(['message' => 'attachment file does not exist'], 400);
        }

        // Return the file as a response
        return response()->file($filePath);
    } else {
        // Return a response indicating that the file does not exist
        return response()->json(['message' => 'File not found'], 404);
    }
    }

    public function CreateLetterTemplate(Request $request)
    {
        if ($request->hasFile('attachment')) {
           
            
            
                $file = $request->file('attachment');

                $fileName =  $file->getClientOriginalName();
                $directory = 'public/template';

              
                Storage::makeDirectory($directory);

                
                Storage::putFileAs($directory, $file, $fileName);
            $validationRules = [
                'id_admin' => 'required|numeric|exists:user,id',
                'id_checker' => 'required|string',
                'id_approval' => 'required|string',
                'perihal' => 'required|string',
                'priority' => 'required|integer|between:1,5',
                'isian' => 'required|string',
            ];
            $validator = Validator::make($request->all(),$validationRules);
            if ($validator->fails()) {
                return response()->json(['message' => 'input json is not validated', 'errors' => $validator->errors()], 400);
            }
            $template = $this->letterTemplateRepository->create($request->all(),$fileName);
            return response()->json(['message' => 'letter template created succesfully' , 'data' => $template],200);
        } else {
            return response()->json(['message' => 'there is no file in the input'], 400);
        }
    }

    public function UpdateLetterTemplate(Request $request)
    {
        if ($request->hasFile('attachment')) {
            $validationRules = [
                'id' => 'required|numeric|exists:template_surat,id',
                'id_admin' => 'required|numeric|exists:user,id',
                'id_checker' => 'required|string',
                'id_approval' => 'required|string',
                'perihal' => 'required|string',
                'priority' => 'required|integer|between:1,5',
                'isian' => 'required',
            ];
            $validator = Validator::make($request->all(),$validationRules);
            
            if ($validator->fails()) {
                return response()->json(['message' => 'input json is not validated', 'errors' => $validator->errors()], 400);
            }
            
            $letterTemplate = $this->letterTemplateRepository->getById($request->input('id'));
            $existingFileName = $letterTemplate->attachment;

            $file = $request->file('attachment');

            $fileName =  $file->getClientOriginalName();
            $directory = 'public/template';

          
            Storage::makeDirectory($directory);
            Storage::putFileAs($directory, $file, $fileName);

            if ($existingFileName) {
                Storage::delete($directory . '/' . $existingFileName);
            }
            

          
            $template = $this->letterTemplateRepository->update($request->all());
            return response()->json(['message' => 'letter template updated succesfully' , 'data' => $template],200);
    } else {
        return response()->json(['message' => 'there is no file in the input'], 400);
    }
    }

    public function destroy(Request $request)
    {
        $validatedData = $request->validate([
            'id' => 'required|integer|exists:template_surat',
        ]);

        $deleted = $this->letterTemplateRepository->delete($validatedData['id']);
        if (!$deleted) {
            return response()->json(['message' => 'Template not found'], 404);
        }
        return response()->json(['message' => 'Template deleted'], 200);
    }

    public function convertWordToPdf() {
        $domPdfPath = base_path('vendor/dompdf/dompdf');
        \PhpOffice\PhpWord\Settings::setPdfRendererPath($domPdfPath);
        \PhpOffice\PhpWord\Settings::setPdfRendererName('DomPDF');
    
        // Load the Word file
        $content = \PhpOffice\PhpWord\IOFactory::load(public_path('output.docx'));
    
        // Save it as a PDF
        $pdfWriter = \PhpOffice\PhpWord\IOFactory::createWriter($content, 'PDF');
        $pdfWriter->save('pdf/output (6).pdf');
    
        // // Optionally, return a response or redirect
        // return response()->download(public_path('path/to/save/yourfile.pdf'));
    }
}
