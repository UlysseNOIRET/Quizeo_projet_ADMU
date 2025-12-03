<?php
// creation_quiz.php

require_once 'check_session.php';
require_once 'db_config.php';

// --- VÉRIFICATION DU RÔLE ---
$user_role = $_SESSION['role'];
if ($user_role !== 'ecole' && $user_role !== 'entreprise') {
    // Si l'utilisateur n'est ni École ni Entreprise, il est redirigé
    header("location: dashboard.php");
    exit;
}

$id_createur = $_SESSION['id'];
$mode_edition = false;
$quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$quiz = ['titre' => 'Nouveau Quiz', 'description' => '', 'statut' => 'en_cours_ecriture'];
$questions = [];
$message = '';

// Si un ID de quiz est passé, on est en mode édition
if ($quiz_id) {
    // Récupérer les données du quiz et vérifier si l'utilisateur en est bien le créateur
    $sql_quiz = "SELECT titre, description, statut FROM quiz WHERE id_quiz = ? AND id_createur = ?";
    if ($stmt_quiz = $conn->prepare($sql_quiz)) {
        $stmt_quiz->bind_param("ii", $quiz_id, $id_createur);
        $stmt_quiz->execute();
        $result = $stmt_quiz->get_result();
        if ($result->num_rows == 1) {
            $quiz = $result->fetch_assoc();
            $mode_edition = true;
            
            // Récupérer les questions existantes (Exigence du PDF [cite: 49, 58])
            $sql_questions = "SELECT * FROM question WHERE id_quiz = ?";
            // ... (Code pour exécuter la requête et stocker les questions)
        }
        $stmt_quiz->close();
    }
}

// --- LOGIQUE DE TRAITEMENT (SIMPLIFIÉE) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    if ($_POST['action'] == 'save_base') {
        // Logique de création ou mise à jour du titre/description dans la table `quiz`
        $titre = $conn->real_escape_string($_POST['titre']);
        $description = $conn->real_escape_string($_POST['description']);
        
        if ($mode_edition) {
            // Mise à jour
            $sql = "UPDATE quiz SET titre=?, description=? WHERE id_quiz=?";
            $message = "Quiz mis à jour.";
        } else {
            // Création initiale (statut par défaut: en_cours_ecriture)
            $sql = "INSERT INTO quiz (titre, description, id_createur, statut, date_creation) VALUES (?, ?, ?, 'en_cours_ecriture', NOW())";
            $message = "Quiz créé. Vous pouvez maintenant ajouter des questions.";
        }
        // ... (Exécuter la requête SQL)
    }
    // Note: Le traitement pour ajouter une question sera dans un script séparé ou ici.
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Création de Quiz - Quizeo</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .quiz-form-container { max-width: 900px; margin: 50px auto; padding: 30px; background: white; border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
        .question-list { margin-top: 20px; border-top: 2px solid #ddd; padding-top: 15px; }
        .question-card { border: 1px solid #ccc; padding: 15px; margin-bottom: 10px; border-radius: 5px; }
        .add-question-section { border: 2px dashed #007bff; padding: 20px; margin-top: 30px; border-radius: 5px; }
        .btn-lancer { background-color: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="quiz-form-container">
        <h1><?php echo $mode_edition ? 'Éditer le Quiz : ' . htmlspecialchars($quiz['titre']) : 'Nouveau Quiz'; ?></h1>
        <?php if (!empty($message)) echo "<p style='color: green;'>$message</p>"; ?>

        <h2>Informations de Base</h2>
        <form method="POST">
            <input type="hidden" name="action" value="save_base">
            <div class="form-group">
                <label for="titre">Titre du Quiz</label>
                <input type="text" id="titre" name="titre" value="<?php echo htmlspecialchars($quiz['titre']); ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"><?php echo htmlspecialchars($quiz['description']); ?></textarea>
            </div>
            <button type="submit" class="btn-login">Sauvegarder les bases</button>
        </form>

        <?php if ($mode_edition): ?>
            <h2 style="margin-top: 40px;">Questions Actuelles</h2>
            <div class="question-list">
                <?php if (empty($questions)): ?>
                    <p>Aucune question ajoutée pour l'instant.</p>
                <?php else: ?>
                    <?php endif; ?>
            </div>

            <div class="add-question-section">
                <h2>Ajouter une Nouvelle Question</h2>
                <form action="traitement_question.php" method="POST">
                    <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
                    
                    <div class="form-group">
                        <label for="enonce">Énoncé de la question</label>
                        <textarea id="enonce" name="enonce" required></textarea>
                    </div>

                    <?php if ($user_role === 'entreprise'): ?>
                        <div class="form-group">
                            <label for="type_question">Type de question :</label>
                            <select id="type_question" name="type_question" onchange="toggleQuestionType(this.value)">
                                <option value="qcm">Choix Multiples (QCM)</option>
                                <option value="reponse_libre">Réponse Libre (Texte)</option>
                            </select>
                        </div>
                    <?php else: ?>
                        <input type="hidden" name="type_question" value="qcm">
                    <?php endif; ?>

                    <div id="qcm-options">
                        <h3>Options de Réponse (QCM)</h3>
                        <p>Ajoutez jusqu'à 4 options et cochez celle(s) qui est/sont correcte(s).</p>
                        <div class="form-group">
                            <input type="text" name="reponses[]" placeholder="Texte de l'option 1" required>
                            <input type="checkbox" name="correcte[]" value="0"> Correct
                        </div>
                        </div>

                    <?php if ($user_role === 'ecole'): ?>
                        <div class="form-group">
                            <label for="points">Points attribués à cette question (max 10)</label>
                            <input type="number" id="points" name="points" min="1" max="10" required>
                        </div>
                    <?php endif; ?>

                    <button type="submit" name="add_question" class="btn-register">Ajouter la Question</button>
                </form>
            </div>
            
            <hr style="margin: 40px 0;">
            <p>
                <button class="btn-lancer" onclick="location.href='lancer_quiz.php?id=<?php echo $quiz_id; ?>'">
                    Passer le Quiz au statut "Lancé"
                </button>
            </p>
        <?php endif; ?>

        <p><a href="dashboard.php" class="link-switch">Retour au Dashboard</a></p>
    </div>

<script>
    // Script simple pour masquer/afficher les options QCM si le type de question change (pour Entreprise)
    function toggleQuestionType(type) {
        const qcmOptions = document.getElementById('qcm-options');
        if (qcmOptions) {
            qcmOptions.style.display = (type === 'qcm') ? 'block' : 'none';
        }
    }
    // Appeler une première fois si le rôle est Entreprise
    document.addEventListener('DOMContentLoaded', () => {
        const selectElement = document.getElementById('type_question');
        if (selectElement) {
            toggleQuestionType(selectElement.value);
        }
    });
</script>
</body>
</html>