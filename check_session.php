<?php
// check_session.php

session_start();

// Si l'utilisateur n'est PAS connecté, il est renvoyé à la page de connexion
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.html?error=" . urlencode("Vous devez être connecté pour accéder à cette page."));
    exit;
}

// L'utilisateur est connecté. Les variables de session sont disponibles :
// $_SESSION['id']
// $_SESSION['nom_compte']
// $_SESSION['role']
?>