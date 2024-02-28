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

    public function createLetter(array $data)
    {
        return Letter::create($data);
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

    public function deleteLeetter(int $id)
    {
        $letter = Letter::find($id);
        if ($letter) {
            $letter->delete();
            return true;
        }
        return false;
    }
}
