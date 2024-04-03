<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class LetterTemplateRepository
{
    protected $table = 'template_surat';

    public function getById($id)
    {
        return DB::table($this->table)->find($id);
    }

    public function getAll()
    {
        return DB::table($this->table)->get();
    }
    

    public function create(array $attributes,$attachment)
    {
        // $stringChecker = trim($string, $attributes["id_checker"]);

        // $numberChecker = explode(",", $string);

        // $intArrayChecker = array_map('intval', $numbers);

        // $stringApproval = trim($string, $attributes["id_approvall"]);

        // $numberApproval = explode(",", $string);

        // $intArrayApproval = array_map('intval', $numbers);

        $insertedData =  [
            "id_admin" => $attributes["id_admin"],
             "id_checker" => $attributes["id_checker"], 
             "id_approval" => $attributes["id_approval"], 
             "perihal" => $attributes["perihal"],
            "priority" => $attributes["priority"],
            "isian" => $attributes["isian"] ,
            "attachment" => $attachment];
        return DB::table($this->table)->insertGetId($insertedData);
    }

    public function update( array $attributes)
    {
        $insertedData =  [
            "id_admin" => $attributes["id_admin"],
             "id_checker" => $attributes["id_checker"], 
             "id_approval" => $attributes["id_approval"], 
             "perihal" => $attributes["perihal"],
            "priority" => $attributes["priority"],
            "isian" => json_encode($attributes["isian"]) ];
        return DB::table($this->table)->where('id', $attributes["id"])->update($attributes);
    }

    public function delete($id)
    {
        return DB::table($this->table)->where('id', $id)->delete();
    }
}
