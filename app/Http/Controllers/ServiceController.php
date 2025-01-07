<?php

namespace App\Http\Controllers;

use App\Http\Requests\Service\ServiceCreateRequest;
use App\Models\Service;
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
