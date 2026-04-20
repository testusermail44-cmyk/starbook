<?php
function createMissionPanel($id, $name, $image, $description)
{
    ?>
    <div class="hc o-p bbrg">
        <img class="img bbrg" src="<?= (strpos($image, 'http') === 0) ? $image : "public/images/missions/" . ($image ?: 'default.png') ?>" />
        <div class="vc max j">
            <div class="hv">
                <div class="hc nf">
                    <div class="o-p-name"><?= $name ?></div>
                </div>
                <div class="t">
                    <?= trimText($description, 150) ?>
                </div>
            </div>
            <div class="e">
                <a class="btn bbrg ob" href="mission.php?id=<?= $id ?>">Переглянути</a>
            </div>
        </div>
    </div>
    <?php
}
?>
<html lang="uk">

<head>
    <title>Космічні місії</title>
    <meta charset="UTF-8" />
    <link rel="stylesheet" href="styles/header.css" />
    <link rel="stylesheet" href="styles/style.css" />
    <link rel="stylesheet" href="styles/font-awesome.css" />
</head>

<body>
    <?php
    include('header.php');
    include('models/missionModel.php');
    include('handlers/text.php');

    $missions = getMissions();
    ?>
    <div class="back"></div>
    <div class="hc">
        <div class="vc f">
            <div class="object-con">
                <?php
                if (empty($missions)) {
                    ?>
                    <div class="hc o-p bbrg sn">
                        <div class="sn">Місії не знайдено!</div>
                    </div>
                    <?php
                }

                if (count($missions) > 0) {
                    foreach ($missions as $m) {
                        createMissionPanel($m['id'], $m['name'], $m['image'], $m['description']);
                    }
                }
                ?>
            </div>
        </div>
    </div>
</body>

</html>
