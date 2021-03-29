<?php

require_once('..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'require' . DIRECTORY_SEPARATOR . 'bootstrap.php');

$error    = false;
$message  = [];

switch (false) {
  case isset($_POST['table']):
    $error = _('Could not connect to the database!');
    break;
  case isset($_COOKIE['source_host']):
  case isset($_COOKIE['source_port']):
  case isset($_COOKIE['source_database']):
  case isset($_COOKIE['source_username']):
  case isset($_COOKIE['source_password']):
  case isset($_COOKIE['target_host']):
  case isset($_COOKIE['target_port']):
  case isset($_COOKIE['target_database']):
  case isset($_COOKIE['target_username']):
  case isset($_COOKIE['target_password']):
    $error = _('Could not connect to the database!');
    break;
  default:

      $source_init_queries = [];
      if (isset($_COOKIE['source_init_queries'])) {
        foreach (explode(';', $_COOKIE['source_init_queries']) as $query) {
          $source_init_queries[PDO::MYSQL_ATTR_INIT_COMMAND] = $query;
        }
      }

      $target_init_queries = [];
      if (isset($_COOKIE['target_init_queries'])) {
        foreach (explode(';', $_COOKIE['target_init_queries']) as $query) {
          $target_init_queries[PDO::MYSQL_ATTR_INIT_COMMAND] = $query;
        }
      }

      try {

        $db_source = new PDO('mysql:dbname=' . $_COOKIE['source_database'] . ';host=' . $_COOKIE['source_host'] . ';port=' . $_COOKIE['source_port'] . ';charset=utf8', $_COOKIE['source_username'], $_COOKIE['source_password'], $source_init_queries);
        $db_source->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db_source->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $db_source->setAttribute(PDO::ATTR_TIMEOUT, 600);

        $db_target = new PDO('mysql:dbname=' . $_COOKIE['target_database'] . ';host=' . $_COOKIE['target_host'] . ';port=' . $_COOKIE['target_port'] . ';charset=utf8', $_COOKIE['target_username'], $_COOKIE['target_password'], $target_init_queries);
        $db_target->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db_target->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $db_target->setAttribute(PDO::ATTR_TIMEOUT, 600);

        // Clone source table if target not exists
        $target_table_exists = $db_target->prepare('SHOW TABLES LIKE ?');
        $target_table_exists->execute([$_POST['table']]);

        // Clone new table
        if (!$target_table_exists->rowCount()) {

          $create_table_sql = $db_source->query('SHOW CREATE TABLE ' . $_POST['table'])->fetch();

          if (isset($create_table_sql['Create Table'])) {

            $db_target->query($create_table_sql['Create Table']);

            $message[] = [
              'text'  => sprintf(_('Table <strong>%s</strong> successfully created'), $_POST['table']),
              'label' => 'text-success',
              'error' => false,
            ];

          } else {
            $error = sprintf(_('Could not create <strong>%s</strong> table!'), $_POST['table']);
          }
        } else {
          $message[] = [
            'text'  => sprintf(_('Table <strong>%s</strong> already exists'), $_POST['table']),
            'label' => 'text-success',
            'error' => false,
          ];
        }

        // Truncate table if selected
        if (isset($_POST['truncate']) && $_POST['truncate'] == 'true') {

          $db_target->query("TRUNCATE TABLE {$_POST['table']}");

          $message[] = [
            'text'  => sprintf(_('Table <strong>%s</strong> truncated'), $_POST['table']),
            'label' => 'text-warning',
            'error' => false,
          ];
        }

      } catch(PDOException $e) {
        $error = $e->getMessage();
      }
}

if ($error) {

  $response = [
    'success' => false,
    'message' => $error,
    'error'   => false,
  ];

} else {

  $response = [
    'success' => true,
    'message' => $message,
  ];

  // Update stats
  $db_datamerge->query('UPDATE `mysql` SET `databases` = (`databases` + 2), `tables` = `tables` + 1');
}

header('Content-Type: application/json');
echo json_encode($response);
