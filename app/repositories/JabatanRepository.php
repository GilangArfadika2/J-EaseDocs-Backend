<?php

namespace App\Repositories;

use App\Models\Jabatan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;


class JabatanRepository
{
    public function getAll()
    {
        return Jabatan::all();
    }
    public function createJabatan($jabatanStr){

        $createdJabatan = Jabatan::create([
            'jabatan' =>  $jabatanStr]);
        return $createdJabatan;
    }
}