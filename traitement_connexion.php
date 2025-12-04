<?php
// traitement_connexion.php (SANS HACHAGE - POUR DÉBOGAGE)

session_start();

require_once 'db_config.php'; 

$login_error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $nom_compte = $conn->real_escape_string(trim($_POST['nom_compte']));
    $mot_de_passe = $_POST['mot_de_passe']; // MDP en clair
    $role_choisi = $conn->real_escape_string(trim($_POST['role']));
    
    $login_error = "Nom de compte, mot de passe ou rôle incorrect.";

    $sql = "SELECT id_utilisateur, nom_compte, mot_de_passe, role, est_actif FROM utilisateur WHERE nom_compte = ? AND role = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ss", $param_nom_compte, $param_role);
        $param_nom_compte = $nom_compte;
        $param_role = $role_choisi;
        
        if ($stmt->execute()) {
            $stmt->store_result();
            
            if ($stmt->num_rows == 1) {
                $stmt->bind_result($id, $nom_compte_db, $stored_password, $role, $est_actif);
                
                if ($stmt->fetch()) {
                    
                    // LECTURE SANS HACHAGE : Trim pour nettoyer les espaces parasites
                    $stored_password = trim($stored_password); 
                    
                    // 2. VÉRIFICATION TEMPORAIRE : Comparaison de chaînes en clair
                    if ($mot_de_passe === $stored_password) {
                        
                        // 3. Vérification du statut d'activation (Rôle Admin)
                        if ($est_actif) {
                            
                            // *** SUCCÈS - DÉMARRER SESSION ***
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["nom_compte"] = $nom_compte_db;
                            $_SESSION["role"] = $role;
                            
                            header("location: dashboard.php");
                            exit;
                        } else {
                            $login_error = "Votre compte a été désactivé par l'administrateur.";
                        }
                    } else {
                        // Mot de passe invalide
                        $login_error = "Nom de compte, mot de passe ou rôle incorrect."; 
                    }
                }
            } else {
                $login_error = "Nom de compte, mot de passe ou rôle incorrect.";
            }
        } else {
            $login_error = "Erreur d'exécution de la requête SQL: " . $conn->error;
        }

        $stmt->close();
    } else {
        $login_error = "Erreur de préparation de la requête SQL: " . $conn->error;
    }
    
    $conn->close();

    // --- AFFICHAGE DE L'ERREUR EN CAS D'ÉCHEC ---
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <title>Erreur de Connexion</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <div class="form-container" style="background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24;">
            <h1>Échec de la Connexion</h1>
            <p>**Raison de l'échec :** <?php echo htmlspecialchars($login_error); ?></p>
            <p>Tentez à nouveau la connexion depuis <a href="index.html">la page de connexion</a>.</p>
        </div>
    </body>
    </html>
    <?php
    exit;

} else {
    header("location: index.html");
    exit;
}
?>