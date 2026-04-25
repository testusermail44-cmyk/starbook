<?php
session_start();

function createObjectPanel($id, $name, $image, $type, $description)
{
    ?>
    <div class="hc o-p bbrg">
        <img class="img bbrg" src="<?= (strpos($image, 'http') === 0) ? $image : "public/images/objects/" . ($image ?: 'default.png') ?>" />
        <div class="vc max j">
            <div class="hv">
                <div class="hc nf">
                    <div class="o-p-type"><?= $type ?></div>
                    <div class="o-p-name"><?= $name ?></div>
                </div>
                <div class="t">
                    <?= trimText($description, 100) ?>
                </div>
            </div>
            <div class="e">
                <a class="btn bbrg ob" href="spaceObject.php?id=<?= $id ?>">Переглянути</a>
            </div>
        </div>
    </div>
    <?php
}
?>
<htmL lang="uk">

<head>
    <title>Обсерваторія</title>
    <meta charset="UTF-8" />
    <link rel="stylesheet" href="styles/header.css" />
    <link rel="stylesheet" href="styles/style.css" />
    <link rel="stylesheet" href="styles/font-awesome.css" />
</head>
<dody>
    <?php
    include('header.php');
    include('models/objectModel.php');
    include('handlers/text.php');
    $objects = isset($_GET['search']) ? getSearchObjects($_GET['search']) : ((isset($_GET['type']) || isset($_GET['atmosphere'])) ? getObjectsByFilterValues(isset($_GET['type']) ? $_GET['type'] : '', isset($_GET['atmosphere']) ? $_GET['atmosphere'] : '') : getObjects());
    $types = getTypes();
    ?>
    <div class="back"></div>
    <div class="hc">
        <form class="filter" method="get">
            <div class="filter-t">Тип об'єкта</div>
            <?php
            foreach ($types as $t) {
                ?>
                <label class="custom-checkbox">
                    <input type="checkbox" name="type[]" value="<?= $t['name']?>" <?= isset($_GET['type']) ? (in_array($t['name'], $_GET['type']) ? 'checked' : '') : '' ?> />
                    <span class="checkmark bbrg"></span>
                    <?= $t['name'] ?>
                </label>
                <?php
            }
            ?>
            <div class="filter-t">Наявність атмосфери</div>
            <label class="custom-checkbox">
                <input type="radio" name="atmosphere" value="Так" <?= isset($_GET['atmosphere']) ? ($_GET['atmosphere'] == 'Так' ? 'checked' : '') : '' ?>/>
                <span class="checkmark bbrg"></span>
                Так
            </label>
            <label class="custom-checkbox">
                <input type="radio" name="atmosphere" value="Ні" <?= isset($_GET['atmosphere']) ? ($_GET['atmosphere'] == 'Ні' ? 'checked' : '') : '' ?> />
                <span class="checkmark bbrg"></span>
                Ні
            </label>
            <button class="btn bbrg" type='submit'>Знайти</button>
        </form>
        <div class="vc f">
            <div class="object-con">
                <?php
                if (isset($_GET['search'])) {
                    if ($_GET['search'] != '') {
                        ?>
                        <div class="hc o-p bbrg sn">
                            <div class="sn">За вашим запитом «<?= $_GET['search'] ?>»</div>
                            <div class="sn">знайдено
                                <?= count($objects) . " " . getPlurals(count($objects), "об'єкт", "об'єкти", "об'єктів") ?>
                            </div>
                        </div>
                        <?php
                    }
                } else if (empty($objects)) {
                    ?>
                        <div class="hc o-p bbrg sn">
                            <div class="sn">Об'єкти не знайдено!</div>
                        </div>
                    <?php
                }
                if (count($objects) > 0) {
                    foreach ($objects as $o) {
                        createObjectPanel($o['id'], $o['name'], $o['image'], $o['type'], $o['description']);
                    }
                }
                ?>
            </div>
        </div>
    </div>
</dody>

</htmL>
