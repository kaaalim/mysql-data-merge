<?php

  $error = false;

  switch (false) {
    case isset($_POST['tables']) || (isset($_POST['tables']) && $_POST['tables']):
      $error = _('Nothing to merge!');
      break;
    case isset($_COOKIE['source_host']) && $_COOKIE['source_host'] != 'localhost' && $_COOKIE['source_host'] != '127.0.0.1' && $_COOKIE['source_host']:
    case isset($_COOKIE['source_port']) && $_COOKIE['source_port']:
    case isset($_COOKIE['source_database']) && $_COOKIE['source_database']:
    case isset($_COOKIE['source_username']) && $_COOKIE['source_username']:
    case isset($_COOKIE['source_password']) && $_COOKIE['source_password']:
    case isset($_COOKIE['target_host']) && $_COOKIE['target_host'] != 'localhost' && $_COOKIE['target_host'] != '127.0.0.1' && $_COOKIE['target_host']:
    case isset($_COOKIE['target_port']) && $_COOKIE['target_port']:
    case isset($_COOKIE['target_database']) && $_COOKIE['target_database']:
    case isset($_COOKIE['target_username']) && $_COOKIE['target_username']:
    case isset($_COOKIE['target_password']) && $_COOKIE['target_password']:

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
            $db = new PDO('mysql:dbname=' . $_COOKIE['source_database'] . ';host=' . $_COOKIE['source_host'] . ';port=' . $_COOKIE['source_port'] . ';charset=utf8', $_COOKIE['source_username'], $_COOKIE['source_password'], $source_init_queries);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $db->setAttribute(PDO::ATTR_TIMEOUT, 600);
        } catch(PDOException $e) {
          $error = $e->getMessage();
        }

        try {
            $db = new PDO('mysql:dbname=' . $_COOKIE['target_database'] . ';host=' . $_COOKIE['target_host'] . ';port=' . $_COOKIE['target_port'] . ';charset=utf8', $_COOKIE['target_username'], $_COOKIE['target_password'], $target_init_queries);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $db->setAttribute(PDO::ATTR_TIMEOUT, 600);
        } catch(PDOException $e) {
          $error = $e->getMessage();
        }

        $merge_tables = 0;
        foreach ($_POST['tables'] as $key => $table) {

          if (isset($table['merge'])) {
            $merge_tables++;
          } else {
            unset($_POST['tables'][$key]);
          }
        }

        if (!$merge_tables) {
          $error = _('Nothing to merge!');
        }
  }

$title = _('MySQL Data Merge - Merge Databases');

include('include' . DIRECTORY_SEPARATOR . 'header.php');

?>
<div class="d-flex flex-column flex-md-row align-items-center p-3 px-md-4 bg-white border-bottom box-shadow">
  <div class="container">
    <small>
      <span class="text-dark mr-2"><?php echo _('Connect'); ?></span>
      <small class="text-muted mr-2">&gt;</small>
      <span class="text-dark mr-2"><?php echo _('Compare'); ?></span>
      <small class="text-muted mr-2">&gt;</small>
      <strong><?php echo _('Merge'); ?></strong>
    </small>
  </div>
</div>
<div class="bg-light pt-4 pb-4">
  <div class="container">
    <h1 class="h4 text-dark mb-4"><?php echo _('Merge Databases'); ?></h1>
    <?php if ($error) { ?>
      <div class="alert alert-danger text-center mt-4 mb-4" role="alert">
        <?php echo $error; ?>
      </div>
      <a href="/connect" class="btn btn-secondary"><?php echo _('Connect'); ?></a>
    <?php } else { ?>
      <pre style="height:400px;overflow:auto" class="border rounded bg-light p-3 mt-3 mb-3 bg-dark" id="log"><div class="log-item"><div class="text-success"><?php echo _('Connection... Do not close your browser'); ?></div></div></pre>
      <script>

        $(document).ready(function () {

          DataMerge.MySQL.limit = <?php echo (isset($_POST['rows']) && $_POST['rows'] > 1 ? (int) $_POST['rows'] : 1000); ?>;
          DataMerge.MySQL.timeout = <?php echo (isset($_POST['timeout']) && $_POST['timeout'] > 0 ? (int) ($_POST['timeout'] * 1000) : 0); ?>;

      <?php foreach ($_POST['tables'] as $table => $value) { ?>
          DataMerge.MySQL.tables.push({
            table: "<?php echo $table ?>",
            truncate: <?php echo isset($value['truncate']) ? 'true' : 'false' ?>,
            columns: <?php echo isset($value['columns']) ? json_encode($value['columns']) : '{}' ?>,
          });

      <?php } ?>

          if (DataMerge.MySQL.tables.length > 0) {

            $('#log').prepend(
              $('<div/>', {
                'class': 'log-item'
              }).append(
                $('<div/>', {
                  'class': 'text-success',
                }).append('<?php echo _('Merging tables structure...'); ?>')
              )
            );

            DataMerge.MySQL.mergeStructure(DataMerge.MySQL.tables[0]['table'], DataMerge.MySQL.tables[0]['truncate']);

          } else {

            $('#log').prepend(
              $('<div/>', {
                'class': 'log-item'
              }).append(
                $('<div/>', {
                  'class': 'text-danger',
                }).append('<?php echo _('Nothing to merge!'); ?>')
              )
            );
          }
        });

      </script>
    <?php } ?>
  </div>
</div>
<?php include('include' . DIRECTORY_SEPARATOR . 'footer.php'); ?>
