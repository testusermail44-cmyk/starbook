<?php
function getObjects()
{
    global $conn;
    $stmt = $conn->prepare("SELECT o.id, o.name, t.name as type, o.description, o.image FROM object as o, type as t WHERE t.id = o.type");
    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    return $result;
}

function getSearchObjects($search)
{
    global $conn;
    $stmt = $conn->prepare("SELECT o.id, o.name, t.name as type, o.description, o.image FROM object as o, type as t WHERE t.id = o.type AND (o.name LIKE ? OR o.description LIKE ? OR t.name LIKE ?)");
    $search = '%' . $search . '%';
    $stmt->bind_param("sss", $search, $search, $search);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getTypes()
{
    global $conn;
    $stmt = $conn->prepare("SELECT id, name, image, description FROM type");
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getObjectsByFilterValues($types, $atmosphere)
{
    global $conn;
    $filter = '';
    if (!is_string($types)) {
        if (count($types) > 0) {
            $filter = "AND (t.name = '" . implode("' OR t.name = '", $types) . "')";
        }
    }
    if ($atmosphere != '') {
        $filter .= " AND o.atmosphere = " . ($atmosphere == 'Так' ? 1 : 0);
    }
    $stmt = $conn->prepare("SELECT o.id, o.name, t.name as type, o.description, o.image FROM object as o, type as t WHERE t.id = o.type " . $filter);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getInfoAboutObject($id)
{
    global $conn;
    $stmt = $conn->prepare("SELECT o.id, o.name, t.name as type, t.id as tid, o.description, o.image, o.parameters, o.atmosphere, m.`3d` as model, m.id as mid, m.defuse, m.normal, m.atmosphereColor FROM object as o, type as t, model as m WHERE o.model = m.id AND t.id = o.type AND o.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result;
}

function addObject($name, $type, $atmosphere, $description, $modelId, $image, $parameters){
    global $conn;
    if ($type == 0) {
        $type == 1;
    }
    $stmt = $conn->prepare("INSERT INTO object (name, type, atmosphere, description, model, image, parameters) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("siisiss", $name, $type, $atmosphere, $description, $modelId, $image, $parameters);
    $stmt->execute();
    $inserted_id = $conn->insert_id;
    return $inserted_id;
}

function updateObject($id, $name, $type, $atmosphere, $description, $image, $parameters){
    global $conn;
    $stmt = $conn->prepare("UPDATE object SET name = ?, type = ?, atmosphere = ?, description = ?, image = ?, parameters = ? WHERE id = ?");
    $stmt->bind_param("siisssi", $name, $type, $atmosphere, $description, $image, $parameters, $id);
    $stmt->execute();
}

function deleteObject($id){
    global $conn;
    $stmt = $conn->prepare("DELETE FROM object WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

function addModel($model, $defuse, $normal, $color)
{
    global $conn;
    if (empty($model))
        $model = 'sphere';
    if (empty($defuse))
        $defuse = 'defuse.jpg';
    if (empty($normal))
        $normal = 'normal.jpg';
    if (empty($color))
        $color = '0x000000';

    $stmt = $conn->prepare("INSERT INTO model (`3d`, defuse, normal, atmosphereColor) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $model, $defuse, $normal, $color);
    $stmt->execute();
    $inserted_id = $conn->insert_id;
    return $inserted_id;
}

function updateModel($modelId, $model, $defuse, $normal, $color)
{
    global $conn;
    $stmt = $conn->prepare("UPDATE model SET `3d` = ?, defuse = ?, normal = ?, atmosphereColor = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $model, $defuse, $normal, $color, $modelId);
    $stmt->execute();
}

function deleteModel($id)
{
    global $conn;
    $stmt = $conn->prepare("DELETE FROM model WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

function getInfoAboutStar($id)
{
    global $conn;
    $stmt = $conn->prepare("SELECT color, halo, texture FROM star WHERE object_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result;
}

function addStar($object_id, $color, $halo, $texture = '')
{
    global $conn;
    if (empty($texture)) {
        $texture = 'star.jpg';
    }
    $stmt = $conn->prepare("INSERT INTO star (object_id, color, halo, texture) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $object_id, $color, $halo, $texture);
    $stmt->execute();
}

function updateStar($object_id, $color, $halo, $texture)
{
    global $conn;
    $stmt = $conn->prepare("UPDATE star SET color = ?, halo = ?, texture = ? WHERE object_id = ?");
    $stmt->bind_param("sssi", $color, $halo, $texture, $object_id);
    $stmt->execute();
}

function deleteStar($object_id)
{
    global $conn;
    $stmt = $conn->prepare("DELETE FROM star WHERE object_id = ?");
    $stmt->bind_param("i", $object_id);
    $stmt->execute();
}
?>