<?php
require_once (isset($dir) ? $dir : '').'db/connect.php';
?>
<header class="hc">
    <div class='vc ming headerc'>
        <a class='o-n logo' href="<?= isset($dir) ? $dir : '' ?>index.php">Star Book</a>
        <div class="t s">Довідник об'єктів Всесвіту</div>
    </div>
    <div class="hc c">
        <div class="hc c">
            <a class="hbtn" href="<?= isset($dir) ? $dir : '' ?>observatory.php">Обсерваторія</a>
            <a class="hbtn" href="<?= isset($dir) ? $dir : '' ?>missions.php">Місії</a>
            <a class="hbtn" href="<?= isset($dir) ? $dir : '' ?>galery.php">Галерея</a>
        </div>
        <div class="hc c ch">
            <form class="search bbrg r" method="get" action="<?= isset($dir) ? $dir : '' ?>observatory.php">
                <input class="is" type="text" name="search" placeholder="Пошук..." />
                <button class="btn btn-search" type="submit"></button>
            </form>
        </div>
    </div>

    <?php
    if (isset($_SESSION['user'])) {
        ?>
        <div class="dropmenu">
            <div class="dropmenu-user">
                <img class="dropmenu-img bbrg" src="<?= isset($dir) ? $dir : '' ?>public/images/users/<?= $_SESSION['user']['image'] ?>" />
                <?= $_SESSION['user']['name'] ?>
            </div>
            <div class="dropmenu-content">
                <a href="<?= isset($dir) ? $dir : '' ?>settings.php" class="dropmenu-item">Налаштування</a>
                <?php 
                    if ($_SESSION['user']['type'] == 1) {
                        ?>
                            <a href="<?= isset($dir) ? $dir : '' ?>editor/missionEditor.php" class="dropmenu-item">Редактор місій</a>
                            <a href="<?= isset($dir) ? $dir : '' ?>editor/objectEditor.php" class="dropmenu-item">Редактор об'єктів</a>
                        <?php
                    }
                ?>
                <a href="<?= isset($dir) ? $dir : '' ?>logout.php" class="dropmenu-item">Вихід</a>
            </div>
        </div>
        <?php
    } else {
        ?>
        <div class="hb">
            <a href="<?= isset($dir) ? $dir : '' ?>login.php" class="btn log bbrg r">Увійти</a>
        </div>
        <?php
    }
    ?>
</header>
