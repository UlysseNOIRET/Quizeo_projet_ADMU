<?php
// dashboard.php
session_start();

// Vérification de la connexion (exigence du cahier des charges)
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.html");
    exit;
}

// Récupération du rôle depuis la session
$user_role = $_SESSION['role'];

// LOGIQUE DE REDIRECTION BASÉE SUR LE RÔLE
switch ($user_role) {
    case 'administrateur':
        header('Location: admin_home.php');
        exit;
    case 'ecole':
        header('Location: ecole_home.php');
        exit;
    case 'entreprise':
        header('Location: entreprise_home.php');
        exit;
    case 'simple_utilisateur':
        header('Location: utilisateur_home.php');
        exit;
    default:
        // Cas d'un rôle inconnu (sécurité)
        session_destroy();
        header('Location: index.html?error=' . urlencode('Rôle utilisateur non valide.'));
        exit;
}
?>