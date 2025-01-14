<?php

namespace App\Repositories;

use App\Models\ChatBot;

class ChatBotRepository
{


    private ChatBot $model;

    public function __construct()
    {
        $this->model = new ChatBot();
    }

    public function create(array $data): ChatBot
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