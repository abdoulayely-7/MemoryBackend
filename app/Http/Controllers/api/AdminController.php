<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Medecin;
use App\Models\Specialite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminController extends Controller
{
    public function registerPersonnel(Request $request)
    {
        // Valider les données du formulaire
        $data = $request->validate([
            "prenom" => "required",
            "nom" => "required",
            "adresse" => "required",
            "telephone" => "required|unique:users|min:9",
            "sexe" => "required",
            "email" => "required|email|unique:users",
            "motDePasse" => "required|min:6", // Assurez-vous que ceci correspond au nom de la colonne dans la DB
            "role" => "required|in:medecin,secretaire",
            "specialite_id" => "nullable|exists:specialites,id", // Spécifique aux médecins
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
            $data['motDePasse'] = Hash::make($data['motDePasse']); // Assurez-vous que ceci correspond au nom de la colonne dans la DB
            $data['status'] = 0;

            // Création de l'utilisateur
            $user = User::create($data);

            // Création du médecin ou secrétaire selon le rôle
            if ($data['role'] === 'medecin') {
                if (!$data['specialite_id']) {
                    return response()->json([
                        'message' => 'La spécialité est requise pour les médecins.',
                        'status' => false
                    ], 400);
                }


                 $medecinData = [
                    'specialite_id' => $data['specialite_id'],
                    'user_id' => $user->id // Assurez-vous que vous avez une colonne user_id dans la table patients
                ];
                $medecin = Medecin::create($medecinData);
            }

            // Réponse avec les données de l'utilisateur
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


    public function loginPersonnel(Request $request)
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

    public function updateStatusUser($id)
    {
        // Trouver l'utilisateur par ID
        $user = User::find($id);

        if ($user) {
            // Vérifier si le statut est déjà à 1
            if ($user->status == 1) {
                $user->status = 0;
                $user->save();
                return response()->json([
                    'message' => 'Utilisateur débloqué avec succès.',
                    'status' => $user->status
                ], 200);
            }

            // Mettre à jour le statut à 1
            $user->status = 1;
            $user->save();

            return response()->json([
                'message' => 'Utilisateur bloqué avec succès.',
                'status' => $user->status
            ], 200);
        }

        // Si l'utilisateur n'existe pas
        return response()->json([
            'message' => 'Utilisateur non trouvé.'
        ], 404);
    }

    // Méthodes CRUD pour le personnel
    public function getPersonnel()
    {
        $users = User::with('medecin')->get(); // Inclure les relations si nécessaire
        return response()->json($users);
    }
    public function getAllPersonnel()
    {
        $users = User::all(); // Récupère tous les utilisateurs sans inclure de relations
        return response()->json($users);
    }


    public function getPersonnelById($id)
    {
        $user = User::with('medecin')->find($id);
        if ($user) {
            return response()->json($user);
        }
        return response()->json([
            'message' => 'Utilisateur non trouvé.'
        ], 404);
    }

    public function updatePersonnel(Request $request, $id)
    {
        $data = $request->validate([
            "prenom" => "required",
            "nom" => "required",
            "adresse" => "required",
            "telephone" => "required|min:9",
            "sexe" => "required",
            "email" => "required|email",
            "role" => "required|in:medecin,secretaire",
            "specialite_id" => "nullable|exists:specialites,id", // Spécifique aux médecins
            "photo" => "nullable|image|mimes:jpeg,png,jpg,gif|max:6048", // Validation pour l'image
        ]);

        $user = User::find($id);

        if ($user) {
            // Traitement de l'upload de l'image
            if ($request->hasFile('photo')) {
                $filename = time() . '_' . $request->file('photo')->getClientOriginalName();
                $path = $request->file('photo')->storeAs('images', $filename, 'public');
                $data['photo'] = '/storage/' . $path; // Chemin stocké dans la base de données
            }

            // Mise à jour des données de l'utilisateur
            $user->update($data);

            // Mise à jour du médecin si nécessaire
            if ($data['role'] === 'medecin') {
                $medecin = Medecin::where('user_id', $id)->first();
                if ($medecin) {
                    $medecin->update([
                        'specialite_id' => $data['specialite_id']
                    ]);
                } else {
                    Medecin::create([
                        'user_id' => $id,
                        'specialite_id' => $data['specialite_id']
                    ]);
                }
            } else {
                // Supprimer le médecin si le rôle est modifié
                Medecin::where('user_id', $id)->delete();
            }

            return response()->json([
                'message' => 'Utilisateur mis à jour avec succès.',
                'user' => $user
            ], 200);
        }

        return response()->json([
            'message' => 'Utilisateur non trouvé.'
        ], 404);
    }

    public function deletePersonnel($id)
    {
        $user = User::find($id);

        if ($user) {
            // Supprimer le médecin associé s'il existe
            Medecin::where('user_id', $id)->delete();

            $user->delete();

            return response()->json([
                'message' => 'Utilisateur supprimé avec succès.'
            ], 200);
        }

        return response()->json([
            'message' => 'Utilisateur non trouvé.'
        ], 404);
    }

    public function getSpecialites()
    {
        return Specialite::all();
    }
}
