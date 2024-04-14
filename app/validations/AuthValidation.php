<?php
namespace App\Validations;



class AuthValidation{
    public static function getRegisterRules(): array
    {
        //print 'role';
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:user|max:255',
            'password' => 'required|string|min:8|max:255',
            'role' => 'required|in:checker,approval,admin,superadmin',
            'nip' => 'required|int|max:16',
            'jabatan' => 'required|in:kadivOTI,kabagLay,kabagbis'
        ];
    }

    public static function getLoginRules(): array
    {
        return [
            
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8|max:255'
        ];
    }

    public static function getUserIDRules(): array {
        return [
            'id' => 'required|numeric',
        
        ];
    }

    public static function getUpdateRules(): array
    {
        //print 'role';
        return [
            'id' => 'required|numeric|min:1',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'jabatan' => 'required|in:kadivOTI,kabagLay,kabagbis',
        // 'password' => 'required|string|min:8|max:255',
            'role' => 'required|in:checker,approval,admin,superadmin',
            'nip' => 'required|int|max:16',
        ];
    }

}

// {
//     "$schema": "http://json-schema.org/draft-07/schema#",
//     "type": "object",
//     "properties": {
//       "name": {
//         "type": "string",
//         "maxLength": 255
//       },
//       "email": {
//         "type": "string",
//         "format": "email",
//         "maxLength": 255
//       },
//       "password": {
//         "type": "string",
//         "minLength": 8,
//         "maxLength": 255
//       },
//       "role": {
//         "type": "string",
//         "enum": ["user", "admin", "superadmin"]
//       }
//     },
//     "required": ["name", "email", "password", "role"]
//   }
  