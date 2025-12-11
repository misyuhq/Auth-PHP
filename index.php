<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user['username'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Identifiants incorrects.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Connexion</title>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400&display=swap');
    * { box-sizing: border-box; margin:0; padding:0; font-family: 'Roboto', sans-serif; }

    body {
        height: 100vh;
        background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .card {
        background: rgba(255,255,255,0.05);
        padding: 40px 30px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        width: 350px;
        text-align: center;
        backdrop-filter: blur(10px);
    }

    .card h2 {
        color: #fff;
        margin-bottom: 30px;
        font-weight: 300;
        letter-spacing: 1px;
    }

    input[type="text"], input[type="password"] {
        width: 100%;
        padding: 12px;
        margin-bottom: 20px;
        border-radius: 8px;
        border: none;
        outline: none;
        font-size: 16px;
    }

    button {
        width: 100%;
        padding: 12px;
        border: none;
        border-radius: 8px;
        background: #ff6b6b;
        color: #fff;
        font-size: 16px;
        cursor: pointer;
        transition: 0.3s;
    }

    button:hover { background: #ff4b4b; }

    .error {
        color: #ff4b4b;
        margin-bottom: 20px;
        font-weight: bold;
    }
</style>
</head>
<body>
    <div class="card">
        <h2>Connexion</h2>
        <?php if(!empty($error)) echo "<div class='error'>$error</div>"; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Nom d'utilisateur" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <button type="submit">Se connecter</button>
        </form>
    </div>
</body>
</html>
