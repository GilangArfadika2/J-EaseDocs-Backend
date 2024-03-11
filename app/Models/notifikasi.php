<?php

namespace App\Models;


use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Notifikasi extends Model
{
    use  HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $table = 'notifikasi';
    public $timestamps = false;
    protected $fillable = [
       'id',
        'message',
        'user_id',
        'letter_id'
        

    ];

   
}
