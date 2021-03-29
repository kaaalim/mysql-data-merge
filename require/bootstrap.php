<?php

/*
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
*/

define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_DATABASE', '');
define('DB_USERNAME', '');
define('DB_PASSWORD', '');

try {

    $db_datamerge = new PDO('mysql:dbname=' . DB_DATABASE . ';host=' . DB_HOST . ';port=' . DB_PORT . ';charset=utf8', DB_USERNAME, DB_PASSWORD, []);
    $db_datamerge->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db_datamerge->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $db_datamerge->setAttribute(PDO::ATTR_TIMEOUT, 600);

    $datamerge_mysql = $db_datamerge->query('SELECT * FROM `mysql`');

    if ($datamerge_mysql->rowCount() && $datamerge_mysql_info = $datamerge_mysql->fetch()) {

      $datamerge_mysql_databases = number_format((int) $datamerge_mysql_info['databases']);
      $datamerge_mysql_tables    = number_format((int) $datamerge_mysql_info['tables']);
      $datamerge_mysql_rows      = number_format((int) $datamerge_mysql_info['rows']);

    } else {

      $datamerge_mysql_databases = 0;
      $datamerge_mysql_tables    = 0;
      $datamerge_mysql_rows      = 0;
    }

} catch(PDOException $e) {
  //$error = $e->getMessage();
}
