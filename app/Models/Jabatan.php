<?php

namespace App\Models;


use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Jabatan extends Model
{
    use  HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
// wdaddaaa
    protected $table = 'jabatan';
    public $timestamps = false;
    protected $fillable = [
       'id',
        'jabatan',
            

    ];

   
}
