<?php

namespace App\Models;


use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Log extends Model
{
    use  HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $table = 'log_surat';
    public $timestamps = false;
    protected $fillable = [
       'id',
        'user_id',
        'letter_id',
        'status',
        'createdAt',
        

    ];

   
}
