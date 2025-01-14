<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateButtonRequest;
use App\Http\Requests\Service\ServiceCreateRequest;
use App\Models\Service;
use App\Models\TelegramReplyKeyboard;
use App\Repositories\ServiceRepository;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ServiceController extends Controller
{

    private ServiceRepository $serviceRepository;

    public function __construct()
    {
        $this->serviceRepository = new ServiceRepository();
    }


    public function createButtonTelegram(CreateButtonRequest $request)
    {
        $created = TelegramReplyKeyboard::create([
            'title' => $request->input('name'),
            'service_id' => $request->input('service_id'),
        ]);

        if (empty($created)) {

            return response()->json([
               'success' => false,
                'message' => 'button_not_created',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'button_created',
        ]);
    }


    public function serviceCreate(ServiceCreateRequest $request)
    {
        $response = $this->serviceRepository->create($request->all());

        if (!$response) {
            return response()->json([
                'status' => false,
                'message' => 'Service Create Failed'
            ])->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'status' => true,
            'message' => 'Service Create Success'
        ]);
    }

    public function serviceList()
    {
        return response()->json([
            'data' => $this->serviceRepository->list(),
        ]);
    }

    public function serviceShow(Service $id)
    {
        return response()->json([
            'data' => $id,
        ]);
    }
}
