<?php

$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASSWORD = 'secret';
$DB_NAME = 'workshop';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME);
if ($conn->connect_error) 
    die("Connection failed: " . $conn->connect_error);

$sql = "SELECT LOCALTIMESTAMP() as time FROM DUAL";
$row = $conn->query($sql)->fetch_assoc();
echo $row['time'];
$conn->close();
