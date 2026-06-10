<?php
session_start();
$dir = '../';
 require_once (isset($dir) ? $dir : '').'db/connect.php';
include('../models/missionModel.php');
if (isset($_GET['edit'])) {
    $mission = getMission($_GET['mission']);
}
if (isset($_GET['del'])) {
    $delId = intval($_GET['mission']);
    deleteMission($delId);
    header("Location: missionEditor.php");
    exit;
}

if (isset($_POST['name'])) {
    $id = isset($_GET['edit']) ? intval($_GET['mission']) : 0;
    $name = $_POST['name'];
    $description = $_POST['description'];
    $newImg = '';

    if (isset($_POST['avatar_url']) && $_POST['avatar_url'] != '') {
        $newImg = $_POST['avatar_url'];
    }

    if ($id > 0) {
        updateMission($id, $name, $description, $newImg);
    } else {
        addMission($name, $description, $newImg != '' ? $newImg : 'default.png');
    }

    header("Location: missionEditor.php");
    exit;
}
    include('../header.php');
?>
<!DOCTYPE html>
<html lang="uk">

<head>
    <meta charset="UTF-8" />
    <link rel="stylesheet" href="../styles/header.css" />
    <link rel="stylesheet" href="../styles/viewer.css" />
    <link rel="stylesheet" href="../styles/style.css" />
    <link rel="stylesheet" href="../styles/font-awesome.css" />
    <title>Редактор</title>
</head>

<body>
    <div class="back"></div>
    <div class="scroll-e">
        <?php
        $actionUrl = 'mission.php';
        if (isset($_GET['mission'])) {
            $actionUrl .= '?mission=' . intval($_GET['mission']);
            if (isset($_GET['edit'])) {
                $actionUrl .= '&edit';
            } elseif (isset($_GET['del'])) {
                $actionUrl .= '&del';
            }
        }
        ?>
        <form class="edit bbrg set big" method="post" action="<?= $actionUrl ?>">
            <div class="at">Редактор місій</div>
            <div class="sic">
                <img class="edit-img bbrg"
                    src="<?= (isset($_GET['edit']) && strpos($mission['image'], 'http') === 0) ? $mission['image'] : "../public/images/missions/" . ((isset($_GET['edit']) ? $mission['image'] : 'default.png') ?: 'default.png') . "?v=" . time() ?>" />
            </div>
            <input type="hidden" name="avatar_url" id="avatar_url" value="">
            <input class="input bbrg" type="type" name="name" placeholder="Назва" required
                value="<?= isset($_GET['edit']) ? $mission['name'] : '' ?>" />
            <textarea class="input bbrg tar" name="description" placeholder="Опис..."
                required><?= isset($_GET['edit']) ? $mission['description'] : '' ?></textarea>
            <button id="saveBtn" class="btn bbrg ob" type="submit">Зберегти</button>
        </form>
    </div>
    <script>
        const IMGBB_KEY = '<?= getenv('IMG_API') ?>';
        const form = document.querySelector('form.edit.set');
        const saveBtn = document.getElementById('saveBtn');
        const avatarUrlInput = document.getElementById('avatar_url');
        let selectedFile = null;

        document.querySelector('.edit-img').addEventListener('click', () => {
            let inp = document.createElement('input');
            inp.type = 'file';
            inp.accept = 'image/*';
            inp.click();

            inp.onchange = () => {
                selectedFile = inp.files[0];
                if (!selectedFile) return;

                let reader = new FileReader();
                reader.onload = e => {
                    document.querySelector('.edit-img').src = e.target.result;
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
</body>

</html>
