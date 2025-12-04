<?php
// traitement_reponse_quiz.php

require_once 'check_session.php';
require_once 'db_config.php';

$id_utilisateur = $_SESSION['id'];
$quiz_id = isset($_POST['quiz_id']) ? (int)$_POST['quiz_id'] : 0;
$error = '';
$redirect_url = 'utilisateur_home.php';

if ($quiz_id > 0) {
    // 1. Détermination du rôle du créateur (pour savoir si c'est une note ou un pourcentage)
    $sql_quiz_info = "SELECT q.id_quiz, u.role as createur_role FROM quiz q JOIN utilisateur u ON q.id_createur = u.id_utilisateur WHERE q.id_quiz = ?";
    // ... (Exécuter la requête pour obtenir $createur_role)
    $createur_role = 'ecole'; // Simulation, à remplacer par la vraie valeur BDD
    
    // 2. Calcul du Score/Pourcentage
    $score = 0;
    $total_points = 0;
    $total_qcm_questions = 0;
    $correct_qcm_count = 0;

    // Récupérer toutes les questions et leurs corrections pour ce quiz
    // ... (Requête BDD complexe pour les corrections)
    $corrections = []; // Array pour stocker les bonnes réponses et points

    if ($createur_role === 'ecole') {
        // Logique de correction pour les Écoles (points)
        // Calculer $score et $total_points
        // Le score/pointage pour les réponses libres (Entreprise) est mis à 0
    }
    
    // 3. Insertion dans resultat_utilisateur
    $note_totale = ($createur_role === 'ecole') ? $score : null;
    $pourcentage_reussi = ($createur_role === 'entreprise') ? 50.0 : null; // Calculer le vrai pourcentage

    $sql_resultat = "INSERT INTO resultat_utilisateur (id_utilisateur, id_quiz, date_soumission, note_totale, pourcentage_reussi) VALUES (?, ?, NOW(), ?, ?)";
    if ($stmt_res = $conn->prepare($sql_resultat)) {
        $stmt_res->bind_param("iidd", $id_utilisateur, $quiz_id, $note_totale, $pourcentage_reussi);
        if ($stmt_res->execute()) {
            $resultat_id = $stmt_res->insert_id;
            $stmt_res->close();
            
            // 4. Insertion dans reponse_utilisateur (boucle sur $_POST['reponse_qcm'] et $_POST['reponse_texte'])
            // Cette partie est très longue et dépend de la structure exacte de $corrections
            
            // Simulation :
            $message = "Vos réponses ont été enregistrées. Merci de votre participation.";
            
            // 5. Mettre à jour le statut du quiz à 'terminé' si nécessaire (si c'est le dernier utilisateur qui répond, non applicable ici)
            
        } else {
            $error = "Erreur lors de l'enregistrement du résultat global.";
        }
    } else {
        $error = "Erreur de préparation de la requête résultat.";
    }

} else {
    $error = "ID du quiz invalide.";
}

$conn->close();

if (!empty($error)) {
    // Redirection vers une page d'erreur ou le dashboard
    header("location: utilisateur_home.php?error=" . urlencode($error));
} else {
    header("location: " . $redirect_url . "?success=" . urlencode($message));
}
exit;
?>