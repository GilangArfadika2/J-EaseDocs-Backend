<?php
namespace App\Validations;



class LetterValidation{
    // public static function getRegisterRules(): array
    // {
    //     return [
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|string|email|unique:user|max:255',
    //         'password' => 'required|string|min:8|max:255',
    //         'role' => 'required|in:user,admin,superadmin',
    //         'jabatan' => 'required|string|max:255'
    //     ];
    // }



    public static function createLetterRules(): array
    {
        return [
            'id_template_surat' => 'required|exists:template_surat,id',
            'data' => 'required|array',
            'status' => 'required|string', // Assuming 'status' is a string field
            // 'nomor_surat' => 'required|string',
            'nama_pemohon' => 'required|string',
            'email_pemohon' => 'required|email',
            'nip_pemohon' => 'required|numeric',
            'nama_atasan_pemohon' => 'required|string',
            'email_atasan_pemohon' => 'required|email',
            'nip_atasan_pemohon' => 'required|numeric',
            'approved_at' => 'nullable|date', // Assuming 'approved_at' is a date field
        ];
    }
    public static function verifyOTPRules(): array
    {
        return [
            'id' => 'required|string',
            'code' => 'required|numeric',
        ];
    }

    public static function getOTPIDRules(): array
    {
        return [
            'id' => 'required|string|min:1',
        ];
    }

    public  static function updateDecisionRules() : array {
        return [
            'decision' => 'required|string',
            'letter_id' => 'required|numeric|exists:surat,id',
            'role' => 'required|string|in:atasan_pemohon,checker,approval',
            'email' => 'required|email',
        ];
    }

    public  static function GetLetterAttachment() : array {
        return [
            'letter_id' => 'required|numeric|exists:surat,id',
            // 'id_template_surat' => 'required|numeric|exists:template_surat,id',
            'user_id' => 'required|numeric|exists:user,id'
        ];
    }


    



}
