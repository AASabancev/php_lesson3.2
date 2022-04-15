<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'lesson3_2');
define('DB_USER', 'root');
define('DB_PASS', '');


global $dbConnection;
$dbConnection = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
