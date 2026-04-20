<?php
session_start();
$dir = '../';
include('../header.php');
include('../models/missionModel.php');
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
    <form class="edit bbrg set" method="get" action="mission.php">
        <div class="at">Редактор місій</div>
        <div class="select-box bbrg">
            <select class="input custom-select" name="mission">
                <option value="0">Виберіть місію</option>
                <?php 
                    $missions = getMissions();
                    foreach($missions as $m) {
                        ?>
                        <option value="<?=$m['id']?>"><?=$m['name']?></option>
                        <?php
                    }
                ?>
            </select>
        </div>
        <a href="mission.php" class="btn bbrg">Додати</a>
        <button class="btn bbrg" type="submit" name="edit">Редагувати</button>
        <button class="btn bbrg" type="submit" name="del">Видалити</button>
    </form>
</body>
