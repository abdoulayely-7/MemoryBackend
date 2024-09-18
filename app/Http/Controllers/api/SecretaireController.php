<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class SecretaireController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Obtenir l'utilisateur connecté (secrétaire)
            $secretaire = auth()->user();

            // Vérifier si l'utilisateur connecté est bien un secrétaire
            if ($secretaire->role !== 'secretaire') {
                return response()->json([
                    'statut' => false,
                    'message' => 'Utilisateur non autorisé'
                ], 403);
            }

            // Récupérer les médecins dans le même service que le secrétaire
            $medecins = User::where('service_id', $secretaire->service_id)
                ->where('role', 'medecin')
                ->get();
            $token = JWTAuth::fromUser($secretaire);
            return response()->json([
                'statut' => 201,
                'data' => $medecins,
                'token' => $token,
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'statut' => false,
                'message' => 'Erreur lors de la récupération des médecins',
                'error' => $e->getMessage(),
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
        //
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
