<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Valider les données du formulaire
        $data = $request->validate([
            "prenom" => "required",
            "nom" => "required",
            "adresse" => "required",
            "telephone" => "required",
            "sexe" => "required",
            "email" => "required|email|unique:users",
            "motDePasse" => "required",
            "photo" => "nullable|image|mimes:jpeg,png,jpg,gif|max:2048", // Validation pour l'image
        ]);

        try {
            // Traitement de l'upload de l'image
            if ($request->hasFile('photo')) {
                $filename = time() . '_' . $request->file('photo')->getClientOriginalName();
                $path = $request->file('photo')->storeAs('images', $filename, 'public');
                $data['photo'] = '/storage/' . $path; // Chemin stocké dans la base de données
            }

            // Hash du mot de passe avant de le stocker
            $data['motDePasse'] = Hash::make($data['motDePasse']);

            // Définir le statut par défaut à "debloquer"
            $data['status'] = 0;
            $data['role'] = 'patient';

            // Création de l'utilisateur
            $user = User::create($data);

            // Génération du token JWT (si nécessaire, sinon laisser null)
            // $token = JWTAuth::fromUser($user);

            // Réponse avec les données de l'utilisateur et le token
            return response()->json([
                'statut' => 201,
                'data' => $user,
                "token" => null, // Remplacez par $token si vous générez un token
            ], 201);

        } catch (\Exception $e) {
            // En cas d'erreur, retourne un message d'erreur
            return response()->json([
                "statut" => false,
                "message" => "Erreur lors de l'inscription",
                "error" => $e->getMessage()
            ], 500);
        }
    }



    public function login ( Request $request)
    {
        $data = $request-> validate(
            [
                'email' => 'required|email',
                'password' => 'required',
            ]);
        $token = JWTAuth::attempt($data);
        if (!$token) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
        $user = auth()->user();
        return response()->json([
            'status' => '200',
            'data' => auth()->user(),
            'role' => $user->role,
            'token' => $token
        ]);
    }

    public function logout()
    {
        auth()->logout();
        return  response() -> json([
            'status' => 'true',
            'message' => 'Logged out successfully',
            'token' => null
        ]);
    }

    public function refreshToken()
    {
        $newToken = auth() -> refresh();
        return response()->json([
            'status' => 'true',
            'token' => $newToken
        ]);
    }
}
