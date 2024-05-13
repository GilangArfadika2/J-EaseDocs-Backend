<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;


class LetterTemplateRepository
{
    protected $table = 'template_surat';

    public function getById($id)
    {
        return DB::table($this->table)->find($id);
    }

    public function getAll()
    {
            return DB::table($this->table)->select('id', 'perihal')->get();
    }

    

    public function create(array $attributes,$attachment)
    {
        

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






// $stringChecker = trim($string, $attributes["id_checker"]);

        // $numberChecker = explode(",", $string);

        // $intArrayChecker = array_map('intval', $numbers);

        // $stringApproval = trim($string, $attributes["id_approvall"]);

        // $numberApproval = explode(",", $string);

        // $intArrayApproval = array_map('intval', $numbers);