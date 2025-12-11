<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

// Ajouter un élève
if (isset($_POST['add'])) {
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $sexe = $_POST['sexe'] ?? 'H';
    $date_naissance = $_POST['date_naissance'] ?? null;
    $photo = 'default.png';

    $stmt = $pdo->prepare("INSERT INTO eleves (nom, prenom, sexe, date_naissance, photo) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$nom, $prenom, $sexe, $date_naissance, $photo]);
    header("Location: dashboard.php");
    exit;
}

// Supprimer un élève
if (isset($_POST['delete'])) {
    $id = $_POST['id'] ?? 0;
    $stmt = $pdo->prepare("DELETE FROM eleves WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: dashboard.php");
    exit;
}

// Modifier un élève
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $sexe = $_POST['sexe'];
    $date_naissance = $_POST['date_naissance'];

    // Photo
    $photo = $_POST['current_photo'] ?? 'default.png';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $filename = time() . '_' . $_FILES['photo']['name'];
        move_uploaded_file($_FILES['photo']['tmp_name'], "photos/".$filename);
        $photo = $filename;
    }

    $stmt = $pdo->prepare("UPDATE eleves SET nom=?, prenom=?, sexe=?, date_naissance=?, photo=? WHERE id=?");
    $stmt->execute([$nom, $prenom, $sexe, $date_naissance, $photo, $id]);
    header("Location: dashboard.php");
    exit;
}

// Récupérer tous les élèves
$eleves = $pdo->query("SELECT * FROM eleves ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Dashboard</title>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500&display=swap');
    * { box-sizing: border-box; margin:0; padding:0; font-family:'Roboto', sans-serif; }

    body {
        display: flex;
        min-height: 100vh;
        background: #f0f2f5;
    }

    /* Menu latéral */
    .sidebar {
        width: 250px;
        background: #2c3e50;
        color: #fff;
        padding: 20px;
        flex-shrink: 0;
    }
    .sidebar h2 { margin-bottom:30px; font-weight:500; }
    .sidebar a {
        display: block;
        color: #fff;
        text-decoration: none;
        padding: 10px 15px;
        margin-bottom: 10px;
        border-radius:5px;
        transition:0.3s;
    }
    .sidebar a:hover, .sidebar a.active { background:#34495e; }
    .sidebar a.logout { color:#ff6b6b; margin-top:20px; display:inline-block; }

    /* Contenu principal */
    .main { flex:1; padding:30px; overflow-y:auto; }

    h2 { margin-bottom:20px; }

    /* Formulaire ajouter élève */
    .add-form {
        display:flex;
        gap:10px;
        margin-bottom:20px;
        flex-wrap:wrap;
        align-items:center;
    }
    .add-form input, .add-form select { padding:8px; border-radius:5px; border:1px solid #ccc; }
    .add-form button { padding:8px 12px; border:none; border-radius:5px; background:#2ecc71; color:#fff; cursor:pointer; transition:0.3s; }
    .add-form button:hover { background:#27ae60; }

    /* Cartes élèves */
    .eleve-card {
        display:flex;
        align-items:center;
        background:#fff;
        border-radius:10px;
        padding:15px;
        margin-bottom:15px;
        box-shadow:0 4px 8px rgba(0,0,0,0.1);
        position:relative;
    }
    .eleve-card img {
        width:70px; height:70px; object-fit:cover; border-radius:50%; margin-right:15px;
        border:2px solid #ddd;
    }
    .eleve-info { flex:1; }
    .eleve-info p { margin:3px 0; }
    .eleve-actions {
        display:flex;
        gap:5px;
        flex-shrink:0;
    }
    .eleve-actions button {
        padding:6px 10px;
        border:none;
        border-radius:5px;
        cursor:pointer;
        transition:0.3s;
        font-size:16px;
    }
    .edit-btn { background:#3498db; color:#fff; }
    .edit-btn:hover { background:#2980b9; }
    .delete-btn { background:#e74c3c; color:#fff; }
    .delete-btn:hover { background:#c0392b; }

    /* Formulaire édition */
    .edit-form {
        display:none;
        flex-direction:column;
        gap:8px;
        margin-top:10px;
        background:#ecf0f1;
        padding:10px;
        border-radius:8px;
    }
    .edit-form input, .edit-form select { padding:6px; border-radius:5px; border:1px solid #ccc; }
    .edit-form button { padding:6px 10px; border:none; border-radius:5px; cursor:pointer; transition:0.3s; }
    .save-btn { background:#2ecc71; color:#fff; }
    .save-btn:hover { background:#27ae60; }
    .cancel-btn { background:#95a5a6; color:#fff; }
    .cancel-btn:hover { background:#7f8c8d; }

</style>
<script>
function toggleEdit(id){
    const form = document.getElementById('edit-'+id);
    form.style.display = form.style.display === 'flex' ? 'none' : 'flex';
}
</script>
</head>
<body>

<div class="sidebar">
    <h2>Bienvenue, <?php echo htmlspecialchars($_SESSION['user']); ?> !</h2>
    <a href="javascript:void(0)" class="active">Liste d'élèves</a>
    <a href="logout.php" class="logout">Déconnexion</a>
</div>

<div class="main">
    <h2>Liste des élèves</h2>

    <!-- Formulaire Ajouter -->
    <form method="POST" class="add-form">
        <input type="text" name="nom" placeholder="Nom" required>
        <input type="text" name="prenom" placeholder="Prénom" required>
        <input type="date" name="date_naissance">
        <select name="sexe">
            <option value="H">Homme</option>
            <option value="F">Femme</option>
        </select>
        <button type="submit" name="add">+</button>
    </form>

    <?php foreach($eleves as $eleve): ?>
        <?php
        // Chemin de la photo
        $photoPath = 'img/default.jpg'; // photo par défaut
        if (!empty($eleve['photo']) && file_exists('img/' . $eleve['photo'])) {
            $photoPath = 'img/' . $eleve['photo'];
        }
        ?>
        <div class="eleve-card">
            <img src="<?php echo $photoPath; ?>" 
                 alt="Photo" style="width:70px; height:70px; object-fit:cover; border-radius:50%;">
            <div class="eleve-info">
                <p><strong><?php echo htmlspecialchars($eleve['nom'].' '.$eleve['prenom']); ?></strong></p>
                <p>
                    Sexe: <?php echo htmlspecialchars($eleve['sexe']); ?> | 
                    <?php echo ($eleve['sexe'] === 'F') ? 'Née' : 'Né'; ?>: <?php echo $eleve['date_naissance']; ?>
                </p>

                <!-- Formulaire édition -->
                <form method="POST" enctype="multipart/form-data" id="edit-<?php echo $eleve['id']; ?>" class="edit-form">
                    <input type="hidden" name="id" value="<?php echo $eleve['id']; ?>">
                    <input type="hidden" name="current_photo" value="<?php echo htmlspecialchars($eleve['photo']); ?>">
                    <input type="text" name="nom" value="<?php echo htmlspecialchars($eleve['nom']); ?>" required>
                    <input type="text" name="prenom" value="<?php echo htmlspecialchars($eleve['prenom']); ?>" required>
                    <input type="date" name="date_naissance" value="<?php echo $eleve['date_naissance']; ?>">
                    <select name="sexe">
                        <option value="H" <?php if($eleve['sexe']=='H') echo 'selected'; ?>>Homme</option>
                        <option value="F" <?php if($eleve['sexe']=='F') echo 'selected'; ?>>Femme</option>
                    </select>
                    <input type="file" name="photo">
                    <div style="display:flex; gap:5px; margin-top:5px;">
                        <button type="submit" name="update" class="save-btn">💾 Enregistrer</button>
                        <button type="button" onclick="toggleEdit(<?php echo $eleve['id']; ?>)" class="cancel-btn">✖ Annuler</button>
                    </div>
                </form>

            </div>
            <div class="eleve-actions">
                <button class="edit-btn" onclick="toggleEdit(<?php echo $eleve['id']; ?>)">⚙️</button>
                <form method="POST" onsubmit="return confirm('Supprimer cet élève ?');">
                    <input type="hidden" name="id" value="<?php echo $eleve['id']; ?>">
                    <button type="submit" name="delete" class="delete-btn">-</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
</div>


<script>
function toggleEdit(id){
    const form = document.getElementById('edit-'+id);
    form.style.display = form.style.display === 'flex' ? 'none' : 'flex';
}
</script>


</body>
</html>
