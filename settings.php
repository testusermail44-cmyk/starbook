<?php
include('header.php');
include('models/userModel.php');
$error = '';
if ($_POST) {
    if ($_POST['password'] != $_POST['cpass']) {
        $error = 'Паролі повинні співпадати!';
    } else if ($_POST['password'] != '' && strlen($_POST['password']) < 6) {
        $error = 'Пароль повинен складатися щонайменше з 6 символів!';
    }
    
    if ($_POST['email'] != $_SESSION['user']['email'] && checkEmail($_POST['email'])) {
        $error = 'Такий Email вже використовується!';
    }

    if ($error == '') {
        $newImg = '';
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {

            $uploadedUrl = uploadToImgbb($_FILES['avatar']['tmp_name']);

            if ($uploadedUrl) {
                $newImg = $uploadedUrl;  
            } else {
                $error = 'Помилка при завантаженні зображення на сервіс.';
            }
        }

        if ($error == '') {

            updateUser($_POST['name'], $_POST['email'], $_POST['password'] != '' ? $_POST['password'] : '', $newImg);
            
            header("Location: settings.php");
            exit;
        }
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
    <title>Налаштування</title>
</head>

<body>
    <div class="back"></div>
    <form class="auth bbrg set" method="post" action="settings.php" enctype="multipart/form-data">
        <div class="at">Налаштування</div>
        <div class="sic">
            <img class="setting-img bbrg" src="<?= (strpos($_SESSION['user']['image'], 'http') === 0) ? $_SESSION['user']['image'] : "public/images/users/" . ($_SESSION['user']['image'] ?: 'default.png') . "?v=" . time() ?>" />
        </div>
        <input class="input bbrg" type="type" name="name" placeholder="Ім'я" required
            value="<?= isset($_POST['name']) ? $_POST['name'] : $_SESSION['user']['name'] ?>" />
        <input class="input bbrg" type="email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$" name="email"
            placeholder="Email" required
            value="<?= isset($_POST['email']) ? $_POST['email'] : $_SESSION['user']['email'] ?>" />
        <input class="input bbrg" type="password" name="password" placeholder="Пароль" />
        <input class="input bbrg" type="password" name="cpass" placeholder="Підтвердити пароль" />
        <?= $error != '' ? "<div class='error'>$error</div>" : '' ?>
        <button class="btn bbrg ob" type="submit">Зберегти</button>
    </form>
</body>
<script>
    document.querySelector('.setting-img').addEventListener('click', () => {
        let inp = document.createElement('input');
        inp.type = 'file';
        inp.accept = 'image/*';
        inp.click();

        inp.onchange = () => {
            let file = inp.files[0];
            let reader = new FileReader();
            reader.onload = e => {
                document.querySelector('.setting-img').src = e.target.result;
            };
            reader.readAsDataURL(file);
            let form = document.querySelector('form.auth.set');
            inp.name = 'avatar';
            form.appendChild(inp);
        }
    });
</script>

</html>