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
        return DB::table('log')->get();
    }

    public function getById($id)
    {
        return DB::table('log')->find($id);
    }

    public function deleteNotifikasiByUserIdAndLetterId($userId, $letterId)
    {
        DB::table('log')
            ->where('user_id', $userId)
            ->where('letter_id', $letterId)
            ->delete();
    }

    public function deleteNotifikasiByLetterId( $letterId)
    {
        DB::table('log')
            ->where('letter_id', $letterId)
            ->delete();
    }

    public function deleteNotifikasiByListUserAndLetterId( $listUserId, $letterId)
    {
        DB::table('log')
        ->whereIn('user_id', $listUserId)
        ->where('letter_id', $letterId)
        ->delete();
    }

    public function getNotifikasiByUserId($userId)
    {
        return DB::table('log')->where('user_id', $userId)->get();
    }

    public function getNotifikasiByListUserAndLetterId($listUserId, $letterId)
{
    return DB::table('log')
        ->whereIn('user_id', $listUserId)
        ->where('letter_id', $letterId)
        ->get();
}

    public function getNotifikasiByUserAndLetterId($userId, $letterId)
    {
        return DB::table('log')
            ->where('user_id', $userId)
            ->where('letter_id', $letterId)
            ->first();
    }

    public function create($data)
    {
        return DB::table('log')->insertGetId($data);
    }


    public function update($id, $data)
    {
        DB::table('log')->where('id', $id)->update($data);
    }

    public function delete($id)
    {
        DB::table('log')->where('id', $id)->delete();
    }
}
