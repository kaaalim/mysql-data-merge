<?php

require_once('..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'require' . DIRECTORY_SEPARATOR . 'bootstrap.php');

function prepareSelectColumns($columns) {

  $result = [];

  if ($columns) {
    foreach ($columns as $column => $value) {
      $result[] = "`{$column}`";
    }

    return implode(',', $result);
  }

  return false;
}

function prepareInsertColumnNames($columns) {

  $result = [];

  if ($columns) {
    foreach ($columns as $column => $value) {
      $result[] = "`{$column}` = ?";
    }

    return implode(',', $result);
  }

  return false;
}

function prepareInsertColumnValues($columns) {

  if ($columns) {

    foreach ($columns as $column => $value) {
      $result[] = $value;
    }

    return $result;
  }

  return false;
}

switch (false) {
  case isset($_POST['table']):
  case isset($_POST['columns']):
  case isset($_POST['offset']):
  case isset($_POST['limit']):
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
    $message[] = [
      'text'   => _('Could not connect to the database!'),
      'label'  => 'text-danger',
      'error'  => true,
      'update' => false
    ];
    break;
  default:

      $message        = [];
      $fields_merged  = isset($_POST['offset']) ? (int) $_POST['offset'] : 0;

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

        } catch(PDOException $e) {
          $message[] = [
            'text'   => sprintf(_('<strong>%s</strong>: %s'), $_POST['table'], $e->getMessage()),
            'label'  => 'text-danger',
            'error'  => true,
            'update' => false
          ];
        }

        if ($select_columns = prepareSelectColumns($_POST['columns'])) {

          $select_columns_query = $db_source->query(' SELECT  ' . $select_columns . '
                                                      FROM  `' . $_POST['table'] . '`
                                                      LIMIT ' . (int) $_POST['offset'] . ',' . (int) $_POST['limit'])->fetchAll();

          if ($select_columns_query) {

            $success = false;
            foreach ($select_columns_query as $source_columns) {

              $insert_column_names  = prepareInsertColumnNames($_POST['columns']);
              $insert_column_values = prepareInsertColumnValues($source_columns);

              if ($insert_column_names && $insert_column_values) {

                try {

                  $insert_query = $db_target->prepare("INSERT INTO `{$_POST['table']}` SET {$insert_column_names}");
                  $insert_query->execute($insert_column_values);

                  if ($insert_query->rowCount()) {

                    $success = true;
                    $fields_merged++;
                  }

                } catch(PDOException $e) {

                  $message[] = [
                    'text'   => sprintf(_('<strong>%s</strong>: %s'), $_POST['table'], $e->getMessage()),
                    'label'  => 'text-danger',
                    'error'  => true,
                    'update' => false
                  ];

                }

                // Update stats
                if ($success) {
                  $db_datamerge->query('UPDATE `mysql` SET `rows` = `rows` + 1');
                }

              } else {
                $message[] = [
                  'text'   => sprintf(_('Could not receive fields data from <strong>%s</strong> source table'), $_POST['table']),
                  'label'  => 'text-danger',
                  'error'  => true,
                  'update' => false
                ];
              }
            }

            $message[] = [
              'text'   => sprintf(_('Merged %s rows for <strong>%s</strong> table'), $fields_merged, $_POST['table']),
              'label'  => 'text-success',
              'error'  => false,
              'update' => true
            ];

          } else {
            $message[] = [
              'text'   => sprintf(_('Data for <strong>%s</strong> table merged successfully'), $_POST['table']),
              'label'  => 'text-success',
              'error'  => false,
              'update' => false
            ];
          }

        } else {
          $message[] = [
            'text'   => sprintf(_('Nothing to merge for <strong>%s</strong> table'), $_POST['table']),
            'label'  => 'text-success',
            'error'  => false,
            'update' => false
          ];
        }
}

if ($message) {
  $response = [
    'success' => true,
    'message' => $message,
    'offset'  => $fields_merged == $_POST['offset'] ? false : $fields_merged
  ];
} else {
  $response = [
    'success' => true,
    'message' => _('Internal server error'),
    'offset'  => $fields_merged == $_POST['offset'] ? false : $fields_merged
  ];
}


header('Content-Type: application/json');
echo json_encode($response);
