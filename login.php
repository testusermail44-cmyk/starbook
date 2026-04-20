<?php

session_start();

    include('header.php');
    include('models/userModel.php');
    $error = '';
    if (isset($_POST['email'])) {
        if (checkUser($_POST['email'], $_POST['password'])) {
            header('Location: index.php');
        }
        else {
            $error = 'Email або пароль введено не правильно!';
        }
    }
?>
<!DOCTYPE html>
<html lang="uk">

<head>
    <meta charset="UTF-8" />
    <link rel="stylesheet" href="styles/header.css" />
    <link rel="stylesheet" href="styles/viewer.css" />
    <link rel="stylesheet" href="styles/style.css" />
    <link rel="stylesheet" href="styles/font-awesome.css" />
    <title>Авторизація</title>
</head>

<body>
    <div class="back"></div>
    <form class="auth bbrg" method="post" action="login.php">
        <div class="at">Авторизація</div>
        <input class="input bbrg" type="email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$" name="email"
            placeholder="Email" required />
        <input class="input bbrg" type="password" name="password" placeholder="Пароль" required />
        <?= $error != '' ? "<div class='error'>$error</div>" : '' ?>
        <a href="registration.php" class="link">Зареєструватись</a>
        <button class="btn bbrg ob" type="submit">Увійти</button>
    </form>
</body>

</html>
