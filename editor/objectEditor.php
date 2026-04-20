<?php
session_start();
$dir = '../';
include('../header.php');
include('../models/objectModel.php');
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
    <form class="edit bbrg set" method="get" action="object.php">
        <div class="at">Редактор об'єктів</div>
        <div class="select-box bbrg">
            <select class="input custom-select" name="object">
                <option value="0">Виберіть об'єкт</option>
                <?php 
                    $objects = getObjects();
                    foreach($objects as $o) {
                        ?>
                        <option value="<?=$o['id']?>"><?=$o['name']?></option>
                        <?php
                    }
                ?>
            </select>
        </div>
        <a href="object.php" class="btn bbrg">Додати</a>
        <button class="btn bbrg" type="submit" name="edit">Редагувати</button>
        <button class="btn bbrg" type="submit" name="del">Видалити</button>
    </form>
</body>
