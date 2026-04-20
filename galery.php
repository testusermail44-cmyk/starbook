<?php
session_start();
include('header.php');

include_once('models/objectModel.php');
include_once('models/missionModel.php');

$objects = getObjects(); 
$missions = getMissions();

function getFullImgPath($image, $dir) {
    if (empty($image) || $image == 'default.png') {
        return $dir . '/default.png';
    }
    if (strpos($image, 'http') === 0) {
        return $image;
    }
    return $dir . '/' . $image;
}
?>
<html lang="uk">
<head>
    <meta charset="UTF-8" />
    <link rel="stylesheet" href="styles/header.css" />
    <link rel="stylesheet" href="styles/viewer.css" />
    <link rel="stylesheet" href="styles/style.css" />
    <link rel="stylesheet" href="styles/galery.css" />
    <link rel="stylesheet" href="styles/font-awesome.css" />
    <title>Галерея</title>
</head>
<body>
    <div class="back"></div>
    <div class="galery">
        <?php
        foreach ($objects as $obj) {
            $src = getFullImgPath($obj['image'], 'public/images/objects');
            ?>
            <img src='<?= $src ?>' class="bbrg galery-i" title="<?= htmlspecialchars($obj['name']) ?>">
            <?php
        }
        foreach ($missions as $mis) {
            $src = getFullImgPath($mis['image'], 'public/images/missions');
            ?>
            <img src='<?= $src ?>' class="bbrg galery-i" title="<?= htmlspecialchars($mis['name']) ?>">
            <?php
        }
        ?>
    </div>
    <div id="imgModal" class="modal">
        <span class="close">&times;</span>
        <img class="modal-content" id="modalImg">
    </div>
    <script>
        const modal = document.getElementById('imgModal');
        const modalImg = document.getElementById('modalImg');
        const closeBtn = document.querySelector('.close');
        document.querySelectorAll('.galery-i').forEach(img => {
            img.addEventListener('click', () => {
                modal.style.display = "flex";
                modalImg.src = img.src;
            });
        });
        closeBtn.addEventListener('click', () => {
            modal.style.display = "none";
        });
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = "none";
            }
        });
    </script>
</body>
</html>
