<?php
// Note: Dans un vrai projet, la session doit être démarrée ici
// session_start(); 

// --- SIMULATION DE SESSION (À REMPLACER PAR VOTRE LOGIQUE D'AUTHENTIFICATION) ---

// Supposons que l'utilisateur est déjà connecté et que son rôle est stocké:
// Cette variable doit être récupérée après une connexion réussie via traitement_connexion.php
$user_role = 'administrateur'; // Remplacez par une variable de session réelle (ex: $_SESSION['role']) 

// Si l'utilisateur n'est pas connecté, redirigez-le vers la page de connexion
// if (!isset($_SESSION['role'])) {
//     header('Location: index.html');
//     exit;
// }
// $user_role = $_SESSION['role'];

// --- LOGIQUE DE REDIRECTION ---

switch ($user_role) {
    case 'administrateur':
        header('Location: admin_home.html');
        exit;
    case 'ecole':
        header('Location: ecole_home.html');
        exit;
    case 'entreprise':
        header('Location: entreprise_home.html');
        exit;
    case 'simple_utilisateur':
        header('Location: utilisateur_home.html');
        exit;
    default:
        // Gérer les rôles non reconnus ou les erreurs
        header('Location: index.html?error=role_inconnu');
        exit;
}
?>