<?php
// Oracle Database connection settings
$host = 'localhost';      // Oracle host
$port = '1521';           // Default Oracle port
$service_name = 'FREEPDB1'; // Your Oracle service name
$username = 'bunga_admin';  // Oracle username
$password = 'project123';   // Oracle password

$conn = oci_connect($username, $password, 'localhost:1521/FREEPDB1');

if (!$conn) {
    throw new Exception('Database connection failed!');
}
?>
