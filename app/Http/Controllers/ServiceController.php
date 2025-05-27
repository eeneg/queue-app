<?php

namespace App\Http\Controllers;

use App\Models\Service;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(
            Service::query()
                ->select('id', 'name', 'prefix', 'description', 'active')
                ->get()
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Service $service)
    {
        return response()->json($service->only(['id', 'name', 'prefix', 'description', 'active']));
    }
}
