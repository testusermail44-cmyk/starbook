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

    if (isset($json['data']['url'])) {
        return $json['data']['url'];  
    }
    return false;
}

function checkEmail($email): bool
{
    global $conn;
    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return is_array($result);
}

function checkUser($email, $password)
{
    global $conn;
    $stmt = $conn->prepare("SELECT name, email, image, type, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    if ($user && password_verify($password, $user['password'])) {
        createSession($user['name'], $user['email'], $user['image'], $user['type']);
        return true;
    }
    return false;
}

function createUser($name, $email, $password)
{
    global $conn;
    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $pass = password_hash($password, PASSWORD_DEFAULT);
    $stmt->bind_param("sss", $name, $email, $pass);
    $stmt->execute();
}

function createSession($name, $email, $image, $type)
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $_SESSION['user'] = [
        'name' => $name,
        'email' => $email,
        'image' => $image,
        'type' => $type
    ];
}

function updateUser($name, $email, $password = '', $image = '')
{
    global $conn;
    $sql = "UPDATE users SET name = ?, email = ?";
    if ($password != '') {
        $sql .= ", password = ?";
    }
    if ($image != '') {
        $sql .= ", image = ?";
    }

    $sql .= " WHERE email = ?";
    if ($password != '' && $image != '') {
        $pass = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $name, $email, $pass, $image, $_SESSION['user']['email']);
    } else if ($password != '') {
        $pass = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $name, $email, $pass, $_SESSION['user']['email']);
    } else if ($image != '') {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $name, $email, $image, $_SESSION['user']['email']);
    } else {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $name, $email, $_SESSION['user']['email']);
    }

    $stmt->execute();
    if ($password != '') {
        $passLocal = $pass;
    }
    createSession($name, $email, $image != '' ? $image : $_SESSION['user']['image'], $_SESSION['user']['type']);
}
?>
