<?php

namespace App\Repositories;

use App\Models\Prompt;

class PromptRepository
{
    private Prompt $model;

    public function __construct()
    {
        $this->model = new Prompt();
    }

    public function create(array $data): Prompt
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