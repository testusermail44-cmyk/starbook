<?php
function uploadToImgbb($fileTempPath) {
    $apiKey = getenv('IMG_API');   
    $imageData = base64_encode(file_get_contents($fileTempPath));

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.imgbb.com/1/upload?key=' . $apiKey);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, ['image' => $imageData]);

    $response = curl_exec($ch);
    curl_close($ch);

    $json = json_decode($response, true);
    return isset($json['data']['url']) ? $json['data']['url'] : false;
}

function getMissions()
{
    global $conn;
    $stmt = $conn->prepare("SELECT id, name, image, description FROM mission");
    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    return $result;
}

function getMission($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM mission WHERE id=?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function addMission($name, $description, $image){
    global $conn;
    $stmt = $conn->prepare("INSERT INTO mission (name, description, image) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $description, $image);
    $stmt->execute();
    return $stmt->insert_id;
}

function deleteMission($id){
    global $conn;
    $stmt = $conn->prepare("DELETE FROM mission WHERE id=?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

function updateMission($id, $name, $description, $image = null){
    global $conn;
    if($image == null || $image == ''){
        $stmt = $conn->prepare("UPDATE mission SET name=?, description=? WHERE id=?");
        $stmt->bind_param("ssi", $name, $description, $id);
    } else {
        $stmt = $conn->prepare("UPDATE mission SET name=?, description=?, image=? WHERE id=?");
        $stmt->bind_param("sssi", $name, $description, $image, $id);
    }

    return $stmt->execute();
}
?>