<?php

namespace App\Models;


use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Otp extends Model
{
    use  HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $table = 'otp';
    public $timestamps = false;
    protected $fillable = [
       'id',
        'code',
        'email',
        'expired_at',
        'letter_id'

    ];

   
}
