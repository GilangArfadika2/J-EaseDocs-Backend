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

    public function getLetterMemberById(int $id)
{
    return DB::table('letter')->where('id', $id)->value('member');
}

    public function getLetterById(int $id)
    {
        return DB::table('letter')->where('id', $id)->first();
    }

    public function getAllArsip() {
        $listLetter = DB::table('letter')
            ->whereNotNull('nomor_surat')
            ->get();
        foreach ($listLetter as &$letter) {
            $letter->data = json_decode($letter->data, true);
            $letter->member = json_decode($letter->member, true);
        }

        return $listLetter;
    }

    public function getArsipById($nomorSurat) {
        $letter = DB::table('letter')
            ->where('nomor_surat', $nomorSurat)
            ->first();
            $letter->data = json_decode($letter->data, true);
            $letter->member = json_decode($letter->member, true);

        return $letter;
    }

    public function getLetterByBulkId(array $idArray)
    {
        $listLetter =  DB::table('letter')
        ->whereIn('id',  $idArray)
        ->get();
        foreach ($listLetter as &$letter) {
            $letter->data = json_decode($letter->data, true);
            $letter->member = json_decode($letter->member, true);
        }

        return $listLetter;
    }


    public function getLetterByIdAndRole(int $id, $role)
    {
        return Letter::find($id);
    }

    public function getLetterByNomorSurat($nomorSurat)
    {
        return DB::table('letter')->where("nomor_surat",$nomorSurat)->first();
    }

    public function createLetter(array $data) : Letter
    {
        
        $tabelTandaTanganData = end($data['data'])['tabel_tandaTangan'];

        $createdLetter = Letter::create([
            // 'approval_email' => $tabelTandaTanganData['valueEmail3'],
            'status' => "Waiting for " . $tabelTandaTanganData['valueName2'] . " approval",
            "data" => json_encode($data['data']),
            "member" => json_encode($data['member']),
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
        $letter->update(["nomor_surat" => $nomor_surat]);
        return $letter;
    }
    return null; 
}
}
