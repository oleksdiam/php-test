<?php
    // connect to the database
    $db = 'csvdata';
    $host = null;
    $port = null;
    $socket = '/var/lib/mysql/mysql.sock';
    $user = 'web';
    $pass = base64_decode(file_get_contents('/opt/tp.txt')) or die("Unable to open file!");
    $conn = mysqli_connect($host, $user, $pass, $db, $port, $socket);
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
      }
?>