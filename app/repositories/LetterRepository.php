<?php

namespace App\Repositories;

use App\Models\Letter;

class LetterRepository
{
    public function getAll()
    {
        return Letter::all();
    }

    public function getLetterById(int $id)
    {
        return Letter::find($id);
    }

    public function createLetter(array $data) : Letter
    {
        
        $tabelTandaTanganData = end($data['data'])['tabel_tandaTangan'];

        $createdLetter = Letter::create([
            // 'approval_email' => $tabelTandaTanganData['valueEmail3'],
            'status' => "Waiting for " . $tabelTandaTanganData['valueName1'] . " approval",
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


    public function deleteLetter(int $id)
    {
        $letter = Letter::find($id);
        if ($letter) {
            $letter->delete();
            return true;
        }
        return false;
    }
}
