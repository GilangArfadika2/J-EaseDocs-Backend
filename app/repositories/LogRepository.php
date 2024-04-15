<?php

namespace App\Repositories;
use App\Repositories\AuthRepository;
use App\Models\Log;
use App\Repositories\LetterRepository;
use App\Repositories\LetterTemplateRepository;
class LogRepository
{
    protected $model;
    protected $authRepository;
    protected $letterRepository;
    protected $letterTemplateRepository;

    public function __construct(Log $model, LetterRepository $letterRepository,LetterTemplateRepository $letterTemplateRepository,  AuthRepository $authRepository)
    {
        $this->model = $model;
        $this->authRepository = $authRepository;
        $this->letterRepository = $letterRepository;
        $this->letterTemplateRepository = $letterTemplateRepository;

    }

    public function create(array $attributes)
    {
        return $this->model->create($attributes);
    }

    public function update($id, array $attributes)
    {
        return $this->model->where('id', $id)->update($attributes);
    }

    public function delete($id)
    {
        return $this->model->destroy($id);
    }

    public function getAll()
    {
        return $this->model->all();
    }

    public function getById($listUsedId)
    {
        return $this->model->find($id);
    }

    public function getlistLogByUserId($userId)
    {
        $user = $this->authRepository->getUserById( $userId);
        $ListLogSurat = $this->model->orderBy('createdAt', 'desc')->get();
        $logString = [];
        foreach ($ListLogSurat as $logSurat){
            $trimmedStringUser = trim($logSurat->user_id, '{}');
            $integerUser = explode(',', $trimmedStringUser);
            $listUserId = array_map('intval', $integerUser);
            if (in_array($userId ,$listUserId)){
            
                $letter = $this->letterRepository->getLetterByID($logSurat->letter_id);
                $letterTemplate = $this->letterTemplateRepository->getById($letter->id_template_surat);
    
                $listUserName = [];
                foreach ($listUserId as $user_id) {
                    $user = $this->authRepository->getUserById( $user_id);
                    $listUserName[] = $user->name . " (" . $user->jabatan . ")";
                }
                $userString = implode(', ', $listUserName);
                $logString[] = $letterTemplate->perihal . " by pemohon " . $letter->nama_pemohon . " (NIP : " . $letter->nip_pemohon . ")" . 
                " has been " . $logSurat->status . " By ". $user->role  . " " . $userString . " at " . $logSurat->createdAt;
            }
        }

        return  $logString ;
    }
}
