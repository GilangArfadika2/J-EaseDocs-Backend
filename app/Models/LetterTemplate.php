<?php

namespace App\Models;


use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class LetterTemplate extends Model
{
    use  HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $table = 'template_surat';
    public $timestamps = false;
    protected $fillable = [
       'id',
       'id_admin',
       'id_checker',
       'id_approval',
        'perihal',
        'isian',
        'priority',
        'attachment',
        

    ];

   
}
