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
        'data' => 'required|array',
        'member' => 'required|array',
        'member.*.role' => 'required|string',
        'member.*.email' => 'required|email',
        'member.*.decision' => 'required|string|in:on-progress,approved,rejected',
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
            'letter_id' => 'required|numeric',
            'role' => 'required|string|in:atasan_pemohon,checker,approver'
        ];
    }


    



}
