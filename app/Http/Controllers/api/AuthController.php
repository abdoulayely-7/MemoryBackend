<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            $data['role'] = 'admin';

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
                'status' => 1
            ], 401);
        }
        if ($user->status == 1) {
            return response()->json([
                'message' => 'Votre compte est bloqué. Veuillez contacter l\'administrateur.',
                'status' => 1
            ], 403);
        }
        // Générer un token JWT pour l'utilisateur
        if (!$token = JWTAuth::fromUser($user)) {
            return response()->json([
                'message' => 'Erreur lors de la génération du token.',
//                'status' => false
            ], 500);
        }
        // Retourner la réponse avec le token et les informations de l'utilisateur
        return response()->json([
            'message' => 'Connexion réussie.',
            'status' => 0,
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

    public function profile()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return response()->json([
            'prenom' => $user->prenom,
            'nom' => $user->nom,
            'role' => $user->role,
//            'photo' => $user->photo ? asset($user->photo) : null,
        ]);
    }
    public function getUserRole()
    {
        // Récupérer l'utilisateur connecté
        $user = Auth::user();

        // Vérifier si l'utilisateur est authentifié
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Retourner le rôle de l'utilisateur
        return response()->json([
            'role' => $user->role,
        ], 200);  // Statut HTTP 200 OK pour une requête réussie
    }
    public function getPatient()
    {
        // Récupérer l'utilisateur connecté
        $user = Auth::user();

        // Vérifier si l'utilisateur est authentifié
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Retourner le rôle de l'utilisateur
        return response()->json([
            'role' => $user->role,
        ], 200);  // Statut HTTP 200 OK pour une requête réussie
    }
    public function getMedecinMemeService()
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
                ->with('service') // Inclure les informations du service des médecins
                ->get();

            // Inclure également les informations du service du secrétaire
            $serviceSecretaire = Service::find($secretaire->service_id);

            $token = JWTAuth::fromUser($secretaire);
            return response()->json([
                'statut' => 201,
                'data' => $medecins,
                'secretaireService' => $serviceSecretaire,
                'token' => $token,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'statut' => false,
                'message' => 'Erreur lors de la récupération des médecins',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function getAllDoctor()
    {
        try {
            $medecin = User::where('role','medecin')->with('service')->get();

            return response()->json([
                'statut' => 201,
                'data' => $medecin
            ], 201);

        }catch (\Exception $e)
        {
            return response()->json([
                'statut' => false,
                'message' => 'Erreur lors de la récupération des médecins',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function search (Request $request)
    {
        $nom = $request->query('nom');
        $service_id = $request->query('service_id');

        $medecins = User::where('role', 'medecin') // Filtrer uniquement les médecins
        ->when($nom, function($query, $nom) {
            return $query->where('nom', 'like', '%' . $nom . '%');
        })
            ->when($service_id, function($query, $service_id) {
                return $query->where('service_id', $service_id);
            })
            ->with('service') // Charge le service avec les médecins
            ->get();
        if ($nom) {
            $medecins->whereHas('user', function ($q) use ($nom) {
                $q->whereRaw('LOWER(nom) LIKE ?', ['%' . strtolower($nom) . '%']);
            });
        }

        return response()->json($medecins);
    }

}
