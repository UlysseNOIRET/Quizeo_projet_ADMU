<?php
// traitement_connexion.php
session_start();

require_once 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $nom_compte = $conn->real_escape_string($_POST['nom_compte']);
    $mot_de_passe = $_POST['mot_de_passe'];
    $role_choisi = $conn->real_escape_string($_POST['role']);
    
    // Requête préparée pour plus de sécurité
    $sql = "SELECT id_utilisateur, mot_de_passe, role, est_actif FROM utilisateur WHERE nom_compte = ? AND role = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ss", $param_nom_compte, $param_role);
        $param_nom_compte = $nom_compte;
        $param_role = $role_choisi;
        
        if ($stmt->execute()) {
            $stmt->store_result();
            
            if ($stmt->num_rows == 1) {
                $stmt->bind_result($id, $hashed_password, $role, $est_actif);
                if ($stmt->fetch()) {
                    
                    // 1. Vérification du mot de passe hashé
                    if (password_verify($mot_de_passe, $hashed_password)) {
                        
                        // 2. Vérification du statut d'activation (Admin Rôle)
                        if ($est_actif) {
                            
                            // Succès : Création de la session
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["nom_compte"] = $nom_compte;
                            $_SESSION["role"] = $role;
                            
                            // Redirection vers le contrôleur général
                            header("location: dashboard.php");
                            exit;
                        } else {
                            // Compte désactivé
                            $error = "Votre compte a été désactivé par l'administrateur.";
                        }
                    } else {
                        $error = "Identifiants ou rôle incorrects.";
                    }
                }
            } else {
                $error = "Identifiants ou rôle incorrects.";
            }
        } else {
            $error = "Erreur de connexion.";
        }
        $stmt->close();
    }
    $conn->close();

    // En cas d'échec, rediriger vers la page de connexion avec l'erreur
    header("location: index.html?error=" . urlencode($error));
    exit;
}
?>