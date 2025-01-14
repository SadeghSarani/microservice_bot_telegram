<?php

namespace App\Http\Controllers;

use App\Http\Requests\Prompt\PromptCreateRequest;
use App\Models\Prompt;
use App\Repositories\PromptRepository;
use Illuminate\Http\Request;

class PromptController extends Controller
{

    private PromptRepository $promptRepo;

    public function __construct()
    {
        $this->promptRepo = new PromptRepository();
    }

    public function promptCreate(PromptCreateRequest $request)
    {
        $response = $this->promptRepo->create($request->all());

        if (!$response) {
            return response()->json([
                'success' => false,
                'message' => 'Prompt not created'
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Prompt created'
        ]);

    }

    public function promptList()
    {
        return response()->json([
           'data' => $this->promptRepo->list()
        ]);
    }

    public function promptShow(Prompt $id)
    {
        return response()->json([
           'data' => $id
        ]);
    }
}
