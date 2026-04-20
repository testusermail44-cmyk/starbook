<?php
$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$password = getenv('DB_PASS');
$dbname = getenv('DB_NAME');


$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die($conn->connect_error);
}

?>