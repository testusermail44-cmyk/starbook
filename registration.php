<?php
include('header.php');
include('models/userModel.php');
$error = '';
if (isset($_POST['name'])) {
    if ($_POST['password'] == $_POST['cpass'] && strlen($_POST['password']) >= 6) {
        if (checkEmail($_POST['email'])) {
            $error = 'Такий Email вже використовується!';     
        }
        else {
            createUser($_POST['name'], $_POST['email'], $_POST['password']);
            createSession($_POST['name'], $_POST['email'], 'default.png', 0);
            header('Location: index.php');
        }
    }
    else if (strlen($_POST['password']) < 6) {
        $error = 'Пароль повинен скаладатися щонайменше з 6 символів!';
    }
    else {
        $error = 'Паролі повинні співпадати!';
    }
}
?>
<html lang="uk">
<head>
    <meta charset="UTF-8" />
    <link rel="stylesheet" href="styles/header.css" />
    <link rel="stylesheet" href="styles/viewer.css" />
    <link rel="stylesheet" href="styles/style.css" />
    <link rel="stylesheet" href="styles/font-awesome.css" />
    <title>Реєстрація</title>
</head>
<body>
    <div class="back"></div>
    <form class="auth bbrg" method="post" action="registration.php">
        <div class="at">Реєстрація</div>
        <input type="text" class="input bbrg" name="name" placeholder="Ім'я" required value="<?=isset($_POST['name']) ? $_POST['name'] : '' ?>"/>
        <input class="input bbrg" type="email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$" name="email" placeholder="Email" required value="<?=isset($_POST['email']) ? $_POST['email'] : '' ?>"/>
        <input class="input bbrg" type="password" name="password" placeholder="Пароль" required />
        <input class="input bbrg" type="password" name="cpass" placeholder="Повторіть пароль" required />
        <?= $error != '' ? "<div class='error'>$error</div>" : '' ?>
        <a href="login.php" class="link">Увійти</a>
        <button class="btn bbrg ob" type="submit">Зареєструватись</button>
    </form>
</body>

</html>