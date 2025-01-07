<?php

error_reporting(E_ALL);
ini_set('display_errors', 2);

$servername = "localhost";
$username = "iee2020038";
$password = "1234";
$dbname = "blockus"; /* TODO: rename */
$socket = "/home/student/iee/2020/iee2020038/mysql/run/mysql.sock";

$conn = new mysqli(null, $username, $password, $dbname, null, $socket);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

