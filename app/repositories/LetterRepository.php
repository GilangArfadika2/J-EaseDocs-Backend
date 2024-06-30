<?php

namespace App\Repositories;

use App\Models\Letter;
use Illuminate\Support\Facades\DB;
class LetterRepository
{
    public function getAll()
    {
        return Letter::all();
    }

    public function checkEmailValidation($letter_id,$email)
    {
        // return DB::table('surat')->where('id', $letter_id)->where('email_atasan_pemohon',$email)->first();
        // return Letter::where('id', $letter_id)
        // ->where('email_atasan_pemohon', $email)
        // ->first();
    }

    public function getLetterMemberById(int $id)
{
    return DB::table('surat')->where('id', $id)->value('member');
}

    public function getLetterById(int $id)
    {
        return DB::table('surat')->where('id', $id)->first();
    }

    public function getLetterByReceiptNumber($id){
        return DB::table('surat')->where('nomor_surat', $id)->first();
    }

    public function getAllArsip() {

        $listLetter = DB::table('surat')
        ->join('template_surat', 'surat.id_template_surat', '=', 'template_surat.id')
        ->where("surat.status","=","approved")
        ->select('surat.*', 'template_surat.perihal', 'template_surat.priority', 'template_surat.attachment')
        ->orderByDesc('template_surat.priority')
        ->get();

        foreach ($listLetter as &$letter) {
            $letter->data = json_decode($letter->data, true);
          
            $attachment = $letter->attachment;
           
            $pdfPath = public_path($attachment . "_" . $letter->id . ".pdf");
            $pdfContent = file_get_contents($pdfPath);
           // Encode PDF content to base64
            $base64EncodedPdf = base64_encode($pdfContent);
            
            // Assign encoded PDF content to a property or key in the $listLetter array
            $letter->pdfContent = $base64EncodedPdf;
        }


        return $listLetter;
    }

    public function getArsipById($nomorSurat) {
        $letter = DB::table('surat')
            ->where('nomor_surat', $nomorSurat)
            ->first();
            $letter->data = json_decode($letter->data, true);
            // $letter->member = json_decode($letter->member, true);

        return $letter;
    }

    public function getLetterByBulkId(array $idArray,$user_id)
    {
        $listLetter = DB::table('surat')
        ->join('template_surat', 'surat.id_template_surat', '=', 'template_surat.id')
        ->join('inbox', 'surat.id', '=', 'inbox.letter_id')
        ->whereIn('surat.id', $idArray)
        ->where('inbox.user_id', $user_id)
        ->select('surat.*', 'template_surat.perihal', 'template_surat.priority','inbox.decision')
        ->orderByDesc('template_surat.priority')
        ->orderBy('surat.created_at', 'desc')
        ->get();

        foreach ($listLetter as &$letter) {
            $letter->data = json_decode($letter->data, true);
            // $letter->member = json_decode($letter->member, true);
        }


        return $listLetter;
    }


    public function getLetterByIdAndRole(int $id, $role)
    {
        return Letter::find($id);
    }

    public function getLetterByNomorSurat($nomorSurat)
    {
        return DB::table('surat')->where("nomor_surat",$nomorSurat)->first();
    }

    public function createLetter(array $data,$generatedNomorSurat) : Letter
    {
        error_log("MARK1: " . $data['jabatan_atasan_pemohon']);
        // $tabelTandaTanganData = end($data['data'])['tabel_tandaTangan'];

        $createdLetter = Letter::create([
            'id_template_surat' =>  $data['id_template_surat'],
            'data' => json_encode( $data['data']),
            'status' => "pending",
            // 'nomor_surat' =>  $data['nomor_surat'],
            'nama_pemohon' =>  $data['nama_pemohon'],
            'email_pemohon' =>  $data['email_pemohon'],
            'nip_pemohon' =>  $data['nip_pemohon'],
            'nama_atasan_pemohon' =>  $data['nama_atasan_pemohon'],
            'email_atasan_pemohon' =>  $data['email_atasan_pemohon'],
            'nip_atasan_pemohon' =>  $data['nip_atasan_pemohon'],
            'jabatan_atasan_pemohon' => $data['jabatan_atasan_pemohon'],
            'approved_at' => null, // Assuming 'approved_at' is nullable and defaults to null
        ]);

        return  $createdLetter;


    }

    public function updateLetter(int $id, array $data)
    {
        $letter = Letter::find($id);
        if ($letter) {
            $letter->update($data);
            return $letter;
        }
        return null;
    }

    public function updateLetterStatus(int $id, $status)
        {
    $letter = Letter::find($id);
    if ($letter) {
        $letter->update(["status" => $status]);
        return $letter;
    }
    return null; 
}
            public function updateLetterMember(int $id, $member)
            {
            $letter = Letter::find($id);
            if ($letter) {
            $letter->update(["member" => $member]);
            return $letter;
            }
            return null; 
            }



    public function deleteLetter(int $id)
    {
        $letter = Letter::find($id);
        if ($letter) {
            $letter->delete();
            return true;
        }
        return false;
    }

    
    public function updateLetterNomorSurat(int $id, $nomor_surat)
        {
    $letter = Letter::find($id);
    if ($letter) {
        if ($nomor_surat != null){
            $letter->update(["nomor_surat" => $nomor_surat,"approved_at" => now()]);
        } else {
            $letter->update(["nomor_surat" => $nomor_surat,"approved_at" => null]);
        }
        
        return $letter;
    }
    return null; 
}
}
