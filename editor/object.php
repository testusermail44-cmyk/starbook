<?php
ini_set('upload_max_filesize', '32M');
ini_set('post_max_size', '64M');
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
        return $oldValue;
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
    $color = str_replace('#', '0x', ($_POST['color'] ?? '#000000') ?: '0x000000');
    $halo  = str_replace('#', '0x', ($_POST['halo'] ?? '#000000') ?: '0x000000');

    $newImg = (isset($_POST['image_url']) && $_POST['image_url'] != '') ? $_POST['image_url'] : ($old['image'] ?? 'default.png');
    $newTexture = (isset($_POST['texture_url']) && $_POST['texture_url'] != '') ? $_POST['texture_url'] : ($oldStar['texture'] ?? 'star.jpg');
    $newDefuse = (isset($_POST['defuse_url']) && $_POST['defuse_url'] != '') ? $_POST['defuse_url'] : ($old['defuse'] ?? 'defuse.jpg');
    $newNormal = (isset($_POST['normal_url']) && $_POST['normal_url'] != '') ? $_POST['normal_url'] : ($old['normal'] ?? 'normal.jpg');
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
            
            <input type="hidden" name="image_url" id="hidden_image" value="">
            <input type="hidden" name="texture_url" id="hidden_texture" value="">
            <input type="hidden" name="defuse_url" id="hidden_defuse" value="">
            <input type="hidden" name="normal_url" id="hidden_normal" value="">

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
        const IMGBB_KEY = '<?= getenv('IMG_API') ?>';
        const select = document.querySelector('.custom-select');
        const container = document.querySelector('.editcontainer');
        const saveBtn = document.getElementById('saveBtn');
        const form = document.getElementById('editForm');

        function enablePicker(selector, hiddenInputId) {
            const img = document.querySelector(selector);
            if (!img) return;

            img.addEventListener('click', () => {
                let inp = document.createElement('input');
                inp.type = 'file';
                inp.accept = 'image/*';
                inp.click();

                inp.onchange = async () => {
                    let file = inp.files[0];
                    if (!file) return;

                    let reader = new FileReader();
                    reader.onload = e => {
                        const targetImg = document.querySelector(selector);
                        if (targetImg) targetImg.src = e.target.result;
                    };
                    reader.readAsDataURL(file);

                    saveBtn.disabled = true;
                    saveBtn.style.opacity = '0.5';
                    saveBtn.style.cursor = 'not-allowed';
                    saveBtn.innerText = 'Завантаження...';

                    try {
                        const base64 = await toBase64(file);
                        const formData = new FormData();
                        formData.append('image', base64.split(',')[1]);

                        const res = await fetch('https://api.imgbb.com/1/upload?key=' + IMGBB_KEY, {
                            method: 'POST',
                            body: formData,
                        });
                        const data = await res.json();

                        if (data && data.data && data.data.url) {
                            document.getElementById(hiddenInputId).value = data.data.url;
                        } else {
                            alert('Помилка при завантаженні зображення на сервіс.');
                        }
                    } catch (err) {
                        alert('Помилка мережі при завантаженні файлу.');
                        console.error(err);
                    } finally {
                        saveBtn.disabled = false;
                        saveBtn.style.opacity = '1';
                        saveBtn.style.cursor = 'pointer';
                        saveBtn.innerText = 'Зберегти';
                    }
                }
            });
        }

        function toBase64(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = () => resolve(reader.result);
                reader.onerror = reject;
                reader.readAsDataURL(file);
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
                        <input type="color" id="star-color" name="color" class="color-picker bbrg" value="<?= isset($_GET['edit']) ? (isset($star) ? '#' . ltrim($star['color'], '0x') : '#ffffff') : '#ffffff' ?>">
                        <div class="t ac sr">Колір гало</div>
                        <input type="color" id="halo-color" name="halo" class="color-picker bbrg" value="<?= isset($_GET['edit']) ? (isset($star) ? '#' . ltrim($star['halo'], '0x') : '#ffffff') : '#ffffff' ?>">
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

            enablePicker('.edit-img', 'hidden_image');
            if (value == 3) {
                enablePicker('#texture-img', 'hidden_texture');
            }
            else if (value != 3 && value != 8 && value != 2) {
                enablePicker('#defuse-img', 'hidden_defuse');
                enablePicker('#normal-img', 'hidden_normal');
            }
        }

        updateEditor();
        select.addEventListener('change', updateEditor);

        form.addEventListener('submit', function (event) {
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

            saveBtn.disabled = true;
            saveBtn.style.opacity = '0.5';
            saveBtn.style.cursor = 'not-allowed';
            saveBtn.innerText = 'Завантаження...';
        });
    </script>

    <script src="../js/text.js"></script>
</body>

</html>
