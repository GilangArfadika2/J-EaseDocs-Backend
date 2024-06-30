<?php

namespace App\Models;


use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Letter extends Model
{
    use  HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $table = 'surat';
    public $timestamps = false;
    protected $fillable = [
       'id',
       'id_template_surat',
        'data',
        'status',
        'nomor_surat',
        'nama_pemohon',
        'email_pemohon',
        'nip_pemohon',
        'nama_atasan_pemohon',
        'email_atasan_pemohon',
        'nip_atasan_pemohon',
        'jabatan_atasan_pemohon',
        'approved_at'
        

    ];

   
}
