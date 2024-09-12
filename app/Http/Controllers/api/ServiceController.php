<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use PHPUnit\Exception;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $service = Service::all();
        try {
            return response()->json([
                'statut' => 201,
                'data' => $service,
                "token" => null,
            ], 201);
        }catch (Exception $e)
        {
            return response()->json([
                "statut" => false,
                "message" => "Erreur lors de la recuperation des services",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data= $request->validate([
            "nomService"=> "required"
        ]);

        try {
            $service= Service::create($data);

            return response()->json([
                'statut' => 201,
                'data' => $service,
                "token" => null,
            ], 201);
        }catch (Exception $e)
        {
            return response()->json([
                "statut" => false,
                "message" => "Erreur lors de l'ajout du service",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
