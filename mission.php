<?php

session_start();
require_once (isset($dir) ? $dir : '').'db/connect.php';
include('models/missionModel.php');
include('models/commentModel.php');

if (isset($_POST['text'])) {
    addComment($_GET['id'], $_SESSION['user']['email'], $_POST['text']);
    header('Location: mission.php?id='.$_GET['id']);
    exit;
}
if (isset($_POST['id'])) {
    deleteComment($_POST['id']);
    header('Location: mission.php?id='.$_GET['id']);
    exit;
}
include('header.php');
$mission = getMission($_GET['id']);
$comments = getMissionComments($_GET['id']);
?>
<html lang="uk">
<head>
    <meta charset="UTF-8" />
    <title><?= $mission['name'] ?></title>
    <link rel="stylesheet" href="styles/header.css" />
    <link rel="stylesheet" href="styles/style.css" />
        <link rel="stylesheet" href="styles/font-awesome.css" />
        <link rel="stylesheet" href="styles/viewer.css" />
</head>
<body>
<div class="back"></div>
<div class="vc c p scroll">
    <img class="big-img bbrg" src="<?= (strpos($mission['image'], 'http') === 0) ? $mission['image'] : "public/images/missions/" . $mission['image'] ?>" />
    <div class="title"><?= $mission['name'] ?></div>
    <div class="big-desc">
        <?= $mission['description'] ?>
    </div>
    <div class="vc comments">
        <div class="title">Коментарі</div>
        <?php foreach ($comments as $c) { ?>
            <div class="comment hc bbrg">
                <img class="uimg bbrg" src="<?= (filter_var($c['image'], FILTER_VALIDATE_URL)) ? $c['image'] : "public/images/users/" . $c['image'] ?>" />
                <div class="vc">
                    <div class="hc sb">
                        <div class="cname"><?= $c['name'] ?></div>
                        <div class="ctime"><?= $c['time'] ?></div>
                    </div>
                    <div class="ctext"><?= $c['text'] ?></div>

                    <?php if (isset($_SESSION['user'])) { 
                        if ($_SESSION['user']['type'] == 1 || $_SESSION['user']['email'] == $c['email']) { ?>
                            <form method="post" action="mission.php?id=<?=$_GET['id']?>">
                                <input type="hidden" name="id" value="<?= $c['id'] ?>" />
                                <button class="btn bbrg" type="submit">Видалити</button>
                            </form>
                        <?php } ?>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>
        <?php if (isset($_SESSION['user'])) { ?>
        <form class="comment-add vc" method="post" action="mission.php?id=<?=$_GET['id']?>">
            <textarea class="input bbrg" name="text" placeholder="Залишити коментар..." required></textarea>
            <button class="btn bbrg" type="submit">Надіслати</button>
        </form>
        <?php } else { ?>
            <div class="need-auth">Авторизуйтесь щоб залишити коментар</div>
        <?php } ?>
    </div>
</div>
<div id="imgModal" class="modal">
   <span class="close">&times;</span>
   <img class="modal-content" id="modalImg">
</div>

<script>
const bigImg = document.querySelector('.big-img');
const modal = document.getElementById('imgModal');
const modalImg = document.getElementById('modalImg');
const closeBtn = document.querySelector('.modal .close');

bigImg.addEventListener('click', () => {
    modal.style.display = "flex";
    modalImg.src = bigImg.src;
});

closeBtn.addEventListener('click', () => modal.style.display = "none");
modal.addEventListener('click', (e) => {
    if(e.target === modal) modal.style.display = "none";
});
</script>
</body>
</html>
