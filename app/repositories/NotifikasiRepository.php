<?php

namespace App\Repositories;

use App\Models\Notifikasi;
use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Notification;
// use App\Notifications\LetterNotification;

class NotifikasiRepository
{
    public function getAll()
    {
        return DB::table('notifikasi')->get();
    }

    public function getById($id)
    {
        return DB::table('notifikasi')->find($id);
    }

    public function deleteNotifikasiByUserIdAndLetterId($userId, $letterId)
    {
        DB::table('notifikasi')
            ->where('user_id', $userId)
            ->where('letter_id', $letterId)
            ->delete();
    }

    public function deleteNotifikasiByLetterId( $letterId)
    {
        DB::table('notifikasi')
            ->where('letter_id', $letterId)
            ->delete();
    }

    public function getNotifikasiByUserId($userId)
    {
        return DB::table('notifikasi')->where('user_id', $userId)->get();
    }

    public function getNotifikasiByUserAndLetterId($userId, $letterId)
    {
        return DB::table('notifikasi')
            ->where('user_id', $userId)
            ->where('letter_id', $letterId)
            ->first();
    }

    public function create($data)
    {
        return DB::table('notifikasi')->insertGetId($data);
    }


    public function update($id, $data)
    {
        DB::table('notifikasi')->where('id', $id)->update($data);
    }

    public function delete($id)
    {
        DB::table('notifikasi')->where('id', $id)->delete();
    }
}
