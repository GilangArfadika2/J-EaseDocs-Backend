<?php

namespace App\Repositories;

use App\Models\Notifikasi;
// use Illuminate\Support\Facades\Notification;
// use App\Notifications\LetterNotification;

class NotifikasiRepository
{
    public function getAll()
    {
        return Notifikasi::all();
    }

    public function getById($id)
    {
        return Notifikasi::findOrFail($id);
    }

    public function getNotifikasiByUserId($userId)
    {
        return Notifikasi::where('user_id', $userId)->get();
    }

    public function create($data)
    {
        $notifikasi = Notifikasi::create($data);
        $this->sendNotification('created', $notifikasi);
        return $notifikasi;
    }

    public function update($id, $data)
    {
        $notifikasi = Notifikasi::findOrFail($id);
        $notifikasi->update($data);
        $this->sendNotification('updated', $notifikasi);
        return $notifikasi;
    }

    public function delete($id)
    {
        $notifikasi = Notifikasi::findOrFail($id);
        $notifikasi->delete();
        $this->sendNotification('deleted', $notifikasi);
    }

    // private function sendNotification($action, $letter)
    // {
    //     $usersToNotify = $this->getUsersToNotify($letter);

    //     foreach ($usersToNotify as $user) {
    //         Notification::send($user, new LetterNotification($action, $letter));
    //     }
    // }

    // private function getUsersToNotify($letter)
    // {
    //     // Define your logic to determine users to notify, e.g., $letter->user_id
    //     // Return collection of users to notify
    // }
}
