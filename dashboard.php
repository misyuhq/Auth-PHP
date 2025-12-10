<?php
ob_start();
session_start();

if (empty($_SESSION['logged'])) {
    header("Location: index.php");
    exit;
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Dashboard</title>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400&display=swap');
    
    body {
        height: 100vh;
        margin: 0;
        font-family: 'Roboto', sans-serif;
        display: flex;
        justify-content: center;
        align-items: center;
        background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
        color: #fff;
        text-align: center;
    }

    .container {
        background: rgba(255,255,255,0.05);
        padding: 50px 40px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        backdrop-filter: blur(10px);
    }

    a {
        display: inline-block;
        margin-top: 20px;
        padding: 10px 25px;
        background: #ff6b6b;
        color: #fff;
        border-radius: 8px;
        text-decoration: none;
        transition: 0.3s;
    }

    a:hover { background: #ff4b4b; }
</style>
</head>
<body>
    <div class="container">
        <h1>Bienvenue !</h1>
        <a href="?logout=1">Se déconnecter</a>
    </div>
</body>
</html>
