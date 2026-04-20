<?php
session_start();
$dir = '../';
include('../header.php');
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

    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        $uploadedUrl = uploadToImgbb($_FILES['avatar']['tmp_name']);
        if ($uploadedUrl) {
            $newImg = $uploadedUrl;
        }
    }

    if ($id > 0) {
        updateMission($id, $name, $description, $newImg);
    } else {
        addMission($name, $description, $newImg != '' ? $newImg : 'default.png');
    }

    header("Location: missionEditor.php");
    exit;
}
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
        <form class="edit bbrg set big" method="post" action="<?= $actionUrl ?>" enctype="multipart/form-data">
            <div class="at">Редактор місій</div>
            <div class="sic">
                <img class="edit-img bbrg"
                    src="<?= (isset($_GET['edit']) && strpos($mission['image'], 'http') === 0) ? $mission['image'] : "../public/images/missions/" . ((isset($_GET['edit']) ? $mission['image'] : 'default.png') ?: 'default.png') . "?v=" . time() ?>" />
            </div>
            <input class="input bbrg" type="type" name="name" placeholder="Назва" required
                value="<?= isset($_GET['edit']) ? $mission['name'] : '' ?>" />
            <textarea class="input bbrg tar" name="description" placeholder="Опис..."
                required><?= isset($_GET['edit']) ? $mission['description'] : '' ?></textarea>
            <button class="btn bbrg ob" type="submit">Зберегти</button>
        </form>
    </div>
    <script>
        document.querySelector('.edit-img').addEventListener('click', () => {
            let inp = document.createElement('input');
            inp.type = 'file';
            inp.accept = 'image/*';
            inp.click();

            inp.onchange = () => {
                let file = inp.files[0];
                let reader = new FileReader();
                reader.onload = e => {
                    document.querySelector('.edit-img').src = e.target.result;
                };
                reader.readAsDataURL(file);
                let form = document.querySelector('form.edit.set');
                inp.name = 'avatar';
                form.appendChild(inp);
            }
        });
    </script>
</body>

</html>
