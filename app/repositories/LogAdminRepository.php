<?php

namespace App\Repositories;

use App\Models\LogAdmin;

class LogAdminRepository {
    protected $model;

    public function __construct(LogAdmin $model)
    {
        $this->model = $model;
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

    public function getById($id)
    {
        return $this->model->find($id);
    }

    public function getByUserId($userId)
    {
        return $this->model->where('user_id', $userId)->get();
    }
    
    // Add more methods as needed, such as getByAction, getByDate, etc.
}
