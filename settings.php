<?php
session_start();
require_once (isset($dir) ? $dir : '').'db/connect.php';
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
        if (isset($_POST['avatar_url']) && $_POST['avatar_url'] != '') {
            $newImg = $_POST['avatar_url'];
        }

        if ($error == '') {
            updateUser($_POST['name'], $_POST['email'], $_POST['password'] != '' ? $_POST['password'] : '', $newImg);
            
            header("Location: settings.php");
            exit;
        }
    }
}
    include('header.php');
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
    <form class="auth bbrg set" method="post" action="settings.php">
        <div class="at">Налаштування</div>
        <div class="sic">
            <img class="setting-img bbrg" src="<?= (strpos($_SESSION['user']['image'], 'http') === 0) ? $_SESSION['user']['image'] : "public/images/users/" . ($_SESSION['user']['image'] ?: 'default.png') . "?v=" . time() ?>" />
        </div>
        <input type="hidden" name="avatar_url" id="avatar_url" value="">
        <input class="input bbrg" type="type" name="name" placeholder="Ім'я" required
            value="<?= isset($_POST['name']) ? $_POST['name'] : $_SESSION['user']['name'] ?>" />
        <input class="input bbrg" type="email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$" name="email"
            placeholder="Email" required
            value="<?= isset($_POST['email']) ? $_POST['email'] : $_SESSION['user']['email'] ?>" />
        <input class="input bbrg" type="password" name="password" placeholder="Пароль" />
        <input class="input bbrg" type="password" name="cpass" placeholder="Підтвердити пароль" />
        <?= $error != '' ? "<div class='error'>$error</div>" : '' ?>
        <button id='saveBtn' class="btn bbrg ob" type="submit">Зберегти</button>
    </form>
</body>
<script>
    const IMGBB_KEY = '<?= getenv('IMG_API') ?>';
    const form = document.querySelector('form.auth.set');
    const saveBtn = document.getElementById('saveBtn');
    const avatarUrlInput = document.getElementById('avatar_url');
    let selectedFile = null;

    document.querySelector('.setting-img').addEventListener('click', () => {
        let inp = document.createElement('input');
        inp.type = 'file';
        inp.accept = 'image/*';
        inp.click();

        inp.onchange = () => {
            selectedFile = inp.files[0];
            if (!selectedFile) return;

            let reader = new FileReader();
            reader.onload = e => {
                document.querySelector('.setting-img').src = e.target.result;
            };
            reader.readAsDataURL(selectedFile);
        }
    });

    form.addEventListener('submit', async function (e) {
        if (selectedFile) {
            e.preventDefault();

            saveBtn.disabled = true;
            saveBtn.style.opacity = '0.5';
            saveBtn.style.cursor = 'not-allowed';
            saveBtn.innerText = 'Завантаження...';

            try {
                const base64 = await toBase64(selectedFile);
                const formData = new FormData();
                formData.append('image', base64.split(',')[1]);

                const res = await fetch('https://api.imgbb.com/1/upload?key=' + IMGBB_KEY, {
                    method: 'POST',
                    body: formData,
                });
                const data = await res.json();

                if (data && data.data && data.data.url) {
                    avatarUrlInput.value = data.data.url;
                    form.submit();
                } else {
                    alert('Помилка при завантаженні зображення на сервіс.');
                    resetSubmitButton();
                }
            } catch (err) {
                alert('Помилка мережі при завантаженні.');
                console.error(err);
                resetSubmitButton();
            }
        } else {
            saveBtn.disabled = true;
            saveBtn.style.opacity = '0.5';
            saveBtn.style.cursor = 'not-allowed';
            saveBtn.innerText = 'Завантаження...';
        }
    });

    function resetSubmitButton() {
        saveBtn.disabled = false;
        saveBtn.style.opacity = '1';
        saveBtn.style.cursor = 'pointer';
        saveBtn.innerText = 'Зберегти';
    }

    function toBase64(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve(reader.result);
            reader.onerror = reject;
            reader.readAsDataURL(file);
        });
    }
</script>

</html>
