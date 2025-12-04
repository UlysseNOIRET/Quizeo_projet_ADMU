<?php
// traitement_inscription.php (SÉCURISÉ AVEC HACHAGE)
session_start();

require_once 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $error = "";

    // 1. Validation du Captcha (5 + 3 = 8)
    $captcha_answer = trim($_POST['captcha']);
    if ($captcha_answer !== "8") {
        $error = "Réponse du CAPTCHA incorrecte.";
    } else {
        
        $nom_compte = trim($_POST['nom_compte']);
        $mot_de_passe = $_POST['mot_de_passe'];
        $confirm_mot_de_passe = $_POST['confirm_mot_de_passe'];
        $role = trim($_POST['role']);

        // 2. Validation des mots de passe
        if ($mot_de_passe !== $confirm_mot_de_passe) {
            $error = "Les mots de passe ne correspondent pas.";
        } 
        
        // 3. Vérification de l'existence du nom de compte
        if (empty($error)) {
            $sql = "SELECT id_utilisateur FROM utilisateur WHERE nom_compte = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("s", $param_nom_compte);
                $param_nom_compte = $nom_compte;
                if ($stmt->execute()) {
                    $stmt->store_result();
                    if ($stmt->num_rows > 0) {
                        $error = "Ce nom de compte est déjà utilisé.";
                    }
                }
                $stmt->close();
            }
        }
        
        // 4. Insertion du nouvel utilisateur
        if (empty($error)) {
            // SÉCURITÉ RÉTABLIE : Hachage du mot de passe
            $hashed_password = password_hash($mot_de_passe, PASSWORD_BCRYPT);
            
            $sql = "INSERT INTO utilisateur (nom_compte, mot_de_passe, role, date_inscription) VALUES (?, ?, ?, NOW())";
            
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("sss", $param_nom_compte, $param_mot_de_passe, $param_role);
                
                $param_nom_compte = $nom_compte;
                $param_mot_de_passe = $hashed_password; // On insère le hash sécurisé
                $param_role = $role;
                
                if ($stmt->execute()) {
                    header("location: index.html?success=" . urlencode("Inscription réussie. Vous pouvez maintenant vous connecter."));
                    exit;
                } else {
                    $error = "Erreur lors de l'enregistrement de l'utilisateur.";
                }
                $stmt->close();
            }
        }
    }
    
    $conn->close();

    header("location: inscription.html?error=" . urlencode($error));
    exit;
}
?>