<?php
// utilisateur_home.php

require_once 'check_session.php';
require_once 'db_config.php';

if ($_SESSION['role'] !== 'simple_utilisateur') {
    header("location: dashboard.php"); 
    exit;
}

$id_utilisateur = $_SESSION['id'];
$nom_utilisateur = $_SESSION['nom_compte'];
$historique_quiz = [];

// Récupérer les quiz auxquels l'utilisateur a participé
$sql = "
    SELECT 
        q.titre, ru.date_soumission, ru.note_totale, ru.pourcentage_reussi, u_creator.nom_compte as createur
    FROM resultat_utilisateur ru
    JOIN quiz q ON ru.id_quiz = q.id_quiz
    JOIN utilisateur u_creator ON q.id_createur = u_creator.id_utilisateur
    WHERE ru.id_utilisateur = ?
    ORDER BY ru.date_soumission DESC;
";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id_utilisateur);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $historique_quiz[] = $row;
    }
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Utilisateur - Quizeo</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard-content { max-width: 900px; margin: 50px auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .answered-quiz { border: 1px solid #ddd; padding: 15px; margin-bottom: 10px; border-left: 5px solid #007bff; text-align: left; }
    </style>
</head>
<body>
    <div class="dashboard-content">
        <h1>Dashboard Utilisateur 👋</h1>
        <p>Bienvenue **<?php echo htmlspecialchars($nom_utilisateur); ?>** ! Retrouvez ci-dessous l'historique de vos participations.</p>
        
        <p><a href="profil.php" class="btn-login" style="display: block; width: 300px; margin: 20px auto;">Gérer mon Profil</a></p>
        
        <h2 style="margin-top: 30px;">Quiz auxquels j'ai répondu (<?php echo count($historique_quiz); ?>)</h2>
        
        <?php if (empty($historique_quiz)): ?>
            <p>Vous n'avez pas encore répondu à un questionnaire.</p>
        <?php else: ?>
            <?php foreach ($historique_quiz as $participation): ?>
                <div class="answered-quiz">
                    <strong>Quiz: <?php echo htmlspecialchars($participation['titre']); ?></strong><br>
                    Créé par : **<?php echo htmlspecialchars($participation['createur']); ?>**<br>
                    Date : <?php echo date('d/m/Y H:i', strtotime($participation['date_soumission'])); ?>
                    <?php 
                        if ($participation['note_totale'] !== null) {
                            echo '<br>Note obtenue : **' . htmlspecialchars($participation['note_totale']) . '/10**'; 
                        } elseif ($participation['pourcentage_reussi'] !== null) {
                            echo '<br>Pourcentage réussi : **' . htmlspecialchars($participation['pourcentage_reussi']) . '%**';
                        }
                    ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <p><a href="logout.php" class="link-switch">Déconnexion</a></p>
    </div>
</body>
</html>