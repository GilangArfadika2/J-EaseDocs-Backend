<?php

namespace App\Models;


use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class LogAdmin extends Model
{
    use  HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
// km
    protected $table = 'log_admin';
    public $timestamps = false;
    protected $fillable = [
       'id',
        'user_id',
        'action',
        

    ];

   
}
