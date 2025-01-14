<?php

namespace App\Repositories;

use App\Models\Service;

class ServiceRepository
{
    private Service $model;

    public function __construct()
    {
        $this->model = new Service();
    }

    public function create(array $data): Service
    {
       return $this->model->create($data);
    }

    public function list()
    {
        return $this->model->all();
    }

    public function get($id)
    {
        return $this->model->where('id', $id)->first();
    }
}