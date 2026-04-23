<?php
session_start();
$dir = '../';
require_once (isset($dir) ? $dir : '').'db/connect.php';
include('../models/objectModel.php');
$types = getTypes();
if (isset($_GET['edit'])) {
    $object = getInfoAboutObject($_GET['object']);
    if ($object['tid'] == 3) {
        $star = getInfoAboutStar($object['id']);
    }
}
if (isset($_GET['del'])) {
    $delId = intval($_GET['object']);
    $objectToDel = getInfoAboutObject($delId);
    if ($objectToDel) {
        if ($objectToDel['tid'] != 3 && $objectToDel['tid'] != 2 && $objectToDel['tid'] != 8) {
            deleteModel($objectToDel['mid']);
        }
        deleteStar($delId);
        deleteObject($delId);
    }
    header("Location: objectEditor.php");
    exit;
}

function uploadFile($file, $oldValue, $localPath = "../public/models/") {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return $oldValue;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];

    if (in_array($ext, $imageExtensions)) {
        $apiKey = getenv('IMG_API');
        $imageData = base64_encode(file_get_contents($file['tmp_name']));
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.imgbb.com/1/upload?key=' . $apiKey);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['image' => $imageData]);
        $response = curl_exec($ch);
        curl_close($ch);
        $json = json_decode($response, true);
        return $json['data']['url'] ?? $oldValue;
    } 
    
    $fileName = time() . '_' . $file['name'];
    move_uploaded_file($file['tmp_name'], $localPath . $fileName);
    return $fileName;
}

if (isset($_POST['name'])) {
    $type = $_POST['type'];
    $id = isset($_GET['edit']) ? intval($_GET['object']) : 0;
    
    $old = ($id > 0) ? getInfoAboutObject($id) : null;
    $oldStar = ($id > 0 && $type == 3) ? getInfoAboutStar($id) : null;

    $name = $_POST['name'];
    $description = $_POST['description'];
    $parameters = $_POST['parameters'];
    $color = str_replace('#', '0x', $_POST['color'] ?: '0x000000');
    $halo = str_replace('#', '0x', $_POST['halo'] ?: '0x000000');

    $newImg = uploadFile($_FILES['image'] ?? null, $old['image'] ?? 'default.png');
    $newTexture = uploadFile($_FILES['texture'] ?? null, $oldStar['texture'] ?? 'star.jpg');
    $newDefuse = uploadFile($_FILES['defuse'] ?? null, $old['defuse'] ?? 'defuse.jpg');
    $newNormal = uploadFile($_FILES['normal'] ?? null, $old['normal'] ?? 'normal.jpg');
    $newModel = uploadFile($_FILES['model'] ?? null, $old['model'] ?? 'sphere', "../public/models/");

    if ($id > 0) {
        if ($type == 3) {
            $oStar = getInfoAboutStar($id);
            if (!$oStar) {
                addStar($id, $color, $halo, $newTexture);
            } else {
                updateStar($id, $color, $halo, $newTexture);
            }
        }
        updateObject($id, $name, $type, (isset($_POST['atmosphere']) ? 1 : 0), $description, $newImg, $parameters);
        if ($type != 3 && $type != 2 && $type != 8) {
            updateModel($old['mid'], $newModel, $newDefuse, $newNormal, $color);
        }
    } else {
        $modelId = 1;
        if ($type != 3 && $type != 2 && $type != 8) {
            $modelId = addModel($newModel, $newDefuse, $newNormal, $color);
        }
        $objectId = addObject($name, $type, (isset($_POST['atmosphere']) ? 1 : 0), $description, $modelId, $newImg, $parameters);
        if ($type == 3) {
            addStar($objectId, $color, $halo, $newTexture);
        }
    }
    header("Location: objectEditor.php");
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
        $actionUrl = 'object.php';
        if (isset($_GET['object'])) {
            $actionUrl .= '?object=' . intval($_GET['object']);
            if (isset($_GET['edit'])) {
                $actionUrl .= '&edit';
            } elseif (isset($_GET['del'])) {
                $actionUrl .= '&del';
            }
        }
        ?>
        <form class="edit bbrg set big" id="editForm" method="post" action="<?= $actionUrl ?>"
            enctype="multipart/form-data">
            <div class="at">Редактор об'єктів</div>
            <div class="editcontainer">
                <div class="sic"><img class="edit-img bbrg"
                        src="<?= (isset($_GET['edit']) && strpos($object['image'], 'http') === 0) ? $object['image'] : "../public/images/objects/" . ((isset($_GET['edit']) ? $object['image'] : 'default.png') ?: 'default.png') . "?v=" . time() ?>" />
                </div>
            </div>
            <input class="input bbrg" type="type" name="name" placeholder="Назва" required
                value="<?= isset($_GET['edit']) ? $object['name'] : '' ?>" />
            <div class="select-box bbrg">
                <select class="input custom-select" name="type">
                    <option value="0">Виберіть тип</option>
                    <?php
                    foreach ($types as $t) {
                        ?>
                        <option value="<?= $t['id'] ?>" <?= isset($_GET['edit']) ? ($object['tid'] == $t['id'] ? 'selected' : '') : '' ?>><?= $t['name'] ?></option>
                        <?php
                    }
                    ?>
                </select>
            </div>
            <div class="t sr">Параметри об'єкта (кожен параметр з нового рядка)</div>
            <textarea name="parameters"
                class="input bbrg editor-input"><?= isset($_GET['edit']) ? htmlspecialchars($object['parameters']) : '' ?></textarea>
            <div class='vc'>
                <div class='hc'>
                    <button onclick='addTitle()' type="button" class='btn bbrg'>Додати заголовок</button>
                </div>
                <textarea name="description" id="dfield" class="hidden-textarea" style="visibility:hidden;">
                    <?= isset($_GET['edit']) ? htmlspecialchars($object['description']) : '' ?>
                </textarea>
                <div class='bbrg'>
                    <div contenteditable="true" id="description" class="input editor-input">
                        <?= (isset($_GET['edit'])) ? $object['description'] : '' ?>
                    </div>
                </div>
            </div>
            <button id='saveBtn' class="btn bbrg ob" type="submit">Зберегти</button>
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
    <script>
        const select = document.querySelector('.custom-select');
        const container = document.querySelector('.editcontainer');

        function enablePicker(selector, inputName) {
            const img = document.querySelector(selector);

            img.addEventListener('click', () => {
                let inp = document.createElement('input');
                inp.type = 'file';
                inp.accept = 'image/*';
                inp.click();

                inp.onchange = () => {
                    let file = inp.files[0];
                    let reader = new FileReader();
                    reader.onload = e => {
                        img.src = e.target.result;
                    };
                    reader.readAsDataURL(file);

                    let form = document.querySelector('form.edit.set');
                    inp.name = inputName;
                    form.appendChild(inp);
                }
            });
        }

        function updateEditor() {
            const value = select.value;
            let planet = false;
            if (value == "8" || value == "2") {
                planet = false;
                container.innerHTML = `<div class="sic"><img class="edit-img bbrg" src="<?= (isset($_GET['edit']) && strpos($object['image'], 'http') === 0) ? $object['image'] : "../public/images/objects/" . ((isset($_GET['edit']) ? $object['image'] : 'default.png') ?: 'default.png') . "?v=" . time() ?>" /></div>`;
            }
            else if (value == "3") {
                planet = false;
                container.innerHTML = `<div class="hc">
                    <div class="sic">
                        <img class="edit-img bbrg"
                        src="<?= (isset($_GET['edit']) && strpos($object['image'], 'http') === 0) ? $object['image'] : "../public/images/objects/" . ((isset($_GET['edit']) ? $object['image'] : 'default.png') ?: 'default.png') . "?v=" . time() ?>" />
                    </div>
                    <div class="vc">
                        <div class="t ac sr">Текстура</div>
                        <img class="texture-img bbrg" id="texture-img" src="<?= (isset($_GET['edit']) && isset($star)) ? ( (strpos($star['texture'], 'http') === 0) ? $star['texture'] : "../public/images/textures/objects/" . ($star['texture'] ?: 'star.jpg') ) : "../public/images/textures/objects/star.jpg" ?>"/>
                    </div>
                    <div class="vc">
                        <div class="t ac sr">Колір зірки</div>
                        <input type="color" id="atmosphere-color" name="color" class="color-picker bbrg" value="<?= isset($_GET['edit']) ? (isset($star) ? '#' . ltrim($star['color'], '0x') : '#ffffff') : '#ffffff' ?>">
                        <div class="t ac sr">Колір гало</div>
                        <input type="color" id="atmosphere-color" name="halo" class="color-picker bbrg" value="<?= isset($_GET['edit']) ? (isset($star) ? '#' . ltrim($star['halo'], '0x') : '#ffffff') : '#ffffff' ?>">
                    </div>
                </div>`;
            }
            else {
                planet = true;
                container.innerHTML = `<div class="hc">
                    <div class="sic">
                        <img class="edit-img bbrg"
                        src="<?= (isset($_GET['edit']) && strpos($object['image'], 'http') === 0) ? $object['image'] : "../public/images/objects/" . ((isset($_GET['edit']) ? $object['image'] : 'default.png') ?: 'default.png') . "?v=" . time() ?>" />
                    </div>
                    <div class="vc">
                        <div class="t ac sr">Текстура</div>
                        <img class="texture-img bbrg" id="defuse-img" src="<?= (isset($_GET['edit'])) ? ( (strpos(isset($star) ? 'defuse.jpg' : $object['defuse'], 'http') === 0) ? (isset($star) ? 'defuse.jpg' : $object['defuse']) : "../public/images/textures/objects/" . (isset($star) ? 'defuse.jpg' : $object['defuse']) ) : "../public/images/textures/objects/defuse.jpg" ?>"/>
                    </div>
                    <div class="vc">
                        <div class="t ac sr">Нормаль</div>
                        <img class="texture-img bbrg" id="normal-img" src="<?= (isset($_GET['edit'])) ? ( (strpos(isset($star) ? 'normal.jpg' : $object['normal'], 'http') === 0) ? (isset($star) ? 'normal.jpg' : $object['normal']) : "../public/images/textures/objects/" . (isset($star) ? 'normal.jpg' : $object['normal']) ) : "../public/images/textures/objects/normal.jpg" ?>"/>
                    </div>
                    <div class="vc">
                        <label class="custom-checkbox">
                            <input id="atmosphere-checkbox" type="checkbox" name="atmosphere" value="1" <?= isset($_GET['edit']) ? ($object['atmosphere'] == 1 ? 'checked' : '') : '' ?> />
                            <span class="checkmark bbrg"></span>
                            Наявність атмосфери
                        </label>
                        <div class="t ac sr">Колір атмосфери</div>
                        <input type="color" id="atmosphere-color" name="color" class="color-picker bbrg" value="<?= isset($_GET['edit']) ? '#' . ltrim($object['atmosphereColor'], '0x') : '0x000000' ?>">
                    </div>
                    <div class="vc">
                        <div class="t ac sr">Оберіть модель</div>
                        <div class="t ac sr">За замовчуванням модель буде сферою</div>
                        <label class="file-picker bbrg btn">
                            <span class="file-label">Оберіть FBX файл</span>
                            <input type="file" name="model" accept=".fbx" class="file-input">
                        </label>
                    </div>
                </div>`;
            }

            if (planet) {
                const checkbox = document.getElementById('atmosphere-checkbox');
                const colorInput = document.getElementById('atmosphere-color');
                colorInput.disabled = !checkbox.checked;
                checkbox.addEventListener('change', () => {
                    colorInput.disabled = !checkbox.checked;
                });

                const fileInput = document.querySelector('.file-input');
                const fileLabel = document.querySelector('.file-label');

                fileInput.addEventListener('change', () => {
                    if (fileInput.files.length > 0) {
                        fileLabel.textContent = fileInput.files[0].name;
                    } else {
                        fileLabel.textContent = "Оберіть FBX файл";
                    }
                });
            }
            enablePicker('.edit-img', 'image');
            if (value == 3) {
                enablePicker('#texture-img', 'texture');
            }
            else if (value != 3 && value != 8 && value != 2) {
                enablePicker('#defuse-img', 'defuse');
                enablePicker('#normal-img', 'normal');
            }

        }
        updateEditor();
        select.addEventListener('change', updateEditor);
        document.getElementById('editForm').addEventListener('submit', function (event) {
            const desc = document.getElementById("description");
            const parameters = document.querySelector('textarea[name="parameters"]');
            const typeSelect = document.querySelector('select[name="type"]');
            document.getElementById("dfield").value = desc.innerHTML;
            if (typeSelect.value == "0") {
                alert("Оберіть тип!");
                event.preventDefault();
                return;
            }
            if (desc.innerText.trim() === '') {
                alert("Опис не може бути пустий!");
                event.preventDefault();
                return;
            }
            if (parameters.value.trim() === '') {
                alert("Параметри не можуть бути пустими!");
                event.preventDefault();
                return;
            }
        });
        const form = document.querySelector('form.edit.set');
        const saveBtn = document.getElementById('saveBtn');
        form.addEventListener('submit', () => {
            saveBtn.disabled = true;
            saveBtn.style.opacity = '0.5';
            saveBtn.style.cursor = 'not-allowed';
            saveBtn.innerText = 'Завантаження...';
        });
    </script>

    <script src="../js/text.js"></script>
</body>

</html>
