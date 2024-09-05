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
            "telephone" => "required|unique:users|min:9",
            "sexe" => "required",
            "email" => "required|email|unique:users",
            "motDePasse" => "required|min:6",
            "photo" => "nullable|image|mimes:jpeg,png,jpg,gif|max:6048", // Validation pour l'image
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
                "token" => null,
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

    public function login(Request $request)
    {
        // Valider les données
        $request->validate([
            'email' => 'required|email',
            'motDePasse' => 'required',
        ]);

        // Rechercher l'utilisateur par email
        $user = User::where('email', $request->email)->first();

        // Si l'utilisateur n'est pas trouvé ou que le mot de passe ne correspond pas
        if (!$user || !Hash::check($request->motDePasse, $user->motDePasse)) {
            return response()->json([
                'message' => 'Email ou mot de passe incorrect.',
                'status' => false
            ], 401);
        }

        // Générer un token JWT pour l'utilisateur
        if (!$token = JWTAuth::fromUser($user)) {
            return response()->json([
                'message' => 'Erreur lors de la génération du token.',
                'status' => false
            ], 500);
        }

        // Retourner la réponse avec le token et les informations de l'utilisateur
        return response()->json([
            'message' => 'Connexion réussie.',
            'status' => true,
            'token' => $token,
            'user' => $user,
            'roles' => $user->role,
        ], 200);
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
