<?php

  $error = false;

  function columnExists($search, $columns, $table) {

    if ($columns && isset($columns[$table])) {
      foreach ($columns[$table] as $column) {
        if ($column['Field'] == $search) {
          return true;
        }
      }
    }

    return false;
  }

  function typeEqual($name, $value, $columns, $table) {

    if ($columns && isset($columns[$table])) {

      foreach ($columns[$table] as $column) {
        if (isset($column[$name]) && $column[$name] === $value) {
          return true;
        }
      }

      foreach ($columns[$table] as $column) {
        if (is_null($column[$name]) && is_null($value)) {
          return true;
        }
      }
    }

    return false;
  }

  switch (false) {
    case isset($_POST['source_host']) && $_POST['source_host']:
    case isset($_POST['source_port']) && $_POST['source_port']:
    case isset($_POST['source_database']) && $_POST['source_database']:
    case isset($_POST['source_username']) && $_POST['source_username']:
    case isset($_POST['source_password']) && $_POST['source_password']:
    case isset($_POST['target_host']) && $_POST['target_host']:
    case isset($_POST['target_port']) && $_POST['target_port']:
    case isset($_POST['target_database']) && $_POST['target_database']:
    case isset($_POST['target_username']) && $_POST['target_username']:
    case isset($_POST['target_password']) && $_POST['target_password']:
      $error = _('Could not connect to the database!');
      break;
      default:

        // Set cookies
        setcookie('source_host', $_POST['source_host']);
        setcookie('source_port', $_POST['source_port']);
        setcookie('source_database', $_POST['source_database']);
        setcookie('source_username', $_POST['source_username']);
        setcookie('source_password', $_POST['source_password']);
        setcookie('source_init_queries', $_POST['source_init_queries']);

        setcookie('target_host', $_POST['target_host']);
        setcookie('target_port', $_POST['target_port']);
        setcookie('target_database', $_POST['target_database']);
        setcookie('target_username', $_POST['target_username']);
        setcookie('target_password', $_POST['target_password']);
        setcookie('target_init_queries', $_POST['target_init_queries']);

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

        // Get source tables
        $source_tables = [];
        try {
            $db = new PDO('mysql:dbname=' . $_POST['source_database'] . ';host=' . $_POST['source_host'] . ';port=' . $_POST['source_port'] . ';charset=utf8', $_POST['source_username'], $_POST['source_password'], $source_init_queries);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $db->setAttribute(PDO::ATTR_TIMEOUT, 600);

            foreach ($db->query('SHOW TABLES')->fetchAll() as $table) {

              if (isset($table[sprintf('Tables_in_%s', $_POST['source_database'])])) { // @TODO prepare

                $table_name = $table[sprintf('Tables_in_%s', $_POST['source_database'])];

                foreach($db->query('SHOW COLUMNS FROM ' . $table_name)->fetchAll() as $columns) {
                  $source_tables[$table_name][] = $columns; // @TODO sort by name
                }
              }
            }

        } catch(PDOException $e) {
          $error = $e->getMessage();
        }

        // Get target tables
        $target_tables = [];
        try {
            $db = new PDO('mysql:dbname=' . $_POST['target_database'] . ';host=' . $_POST['target_host'] . ';port=' . $_POST['target_port'] . ';charset=utf8', $_POST['target_username'], $_POST['target_password'], $target_init_queries);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $db->setAttribute(PDO::ATTR_TIMEOUT, 600);

            foreach ($db->query('SHOW TABLES')->fetchAll() as $table) {

              if (isset($table[sprintf('Tables_in_%s', $_POST['target_database'])])) { // @TODO compatibility

                $table_name = $table[sprintf('Tables_in_%s', $_POST['target_database'])];

                foreach($db->query('SHOW COLUMNS FROM ' . $table_name)->fetchAll() as $columns) {
                  $target_tables[$table_name][] = $columns; // @TODO sort by name
                }
              }
            }

        } catch(PDOException $e) {
          $error = $e->getMessage();
        }

        // Collect all tables
        $tables = array_merge($source_tables, $target_tables);

        ksort($tables);

        foreach ($tables as $name => $values) {
          $tables[$name] = [
            'exists' => [
              'source' => isset($source_tables[$name]) ? true : false,
              'target' => isset($target_tables[$name]) ? true : false,
            ]
          ];
        }
  }

  $title = _('MySQL Data Merge - Compare Databases');

  include('include' . DIRECTORY_SEPARATOR . 'header.php');
?>
<?php  ?>
<div class="d-flex flex-column flex-md-row align-items-center p-3 px-md-4 bg-white border-bottom box-shadow">
  <div class="container">
    <small>
      <span class="text-dark mr-2"><?php echo _('Connect'); ?></span>
      <span class="text-muted mr-2">&gt;</span>
      <strong class="mr-2"><?php echo _('Compare'); ?></strong>
      <span class="text-muted mr-2">&gt;</span>
      <span class="text-secondary"><?php echo _('Merge'); ?></span>
    </small>
  </div>
</div>
<div class="bg-light pt-4 pb-4">
  <div class="container">
    <h1 class="h4 text-dark mb-4"><?php echo _('Compare Databases'); ?></h1>
    <div class="jumbotron pt-3 pb-1 alert-success">
      <ul>
        <li class="pt-1 pb-1"><small><?php echo _('Select Source tables and fields to <strong>merge</strong>'); ?></small></li>
        <li class="pt-1 pb-1"><small><?php echo _('Select Target tables that should be <strong>truncated</strong>'); ?></small></li>
        <li class="pt-1 pb-1"><small><?php echo _('Set <strong>Rows per Query Limit</strong> if your host sensetive to executable timeouts (1000 Rows per Query by default)'); ?></small></li>
        <li class="pt-1 pb-1"><small><?php echo _('Set <strong>Timeout in Seconds</strong> between queries if your host has connection quantity limits (0 Seconds by default)'); ?></small></li>
        <li class="pt-1 pb-1"><small><?php echo _('Make sure similar tables does not have <strong>structure conflicts</strong>'); ?></small></li>
        <li class="pt-1 pb-1"><small><?php echo _('<strong>New tables</strong> with all fields will be created in the Target database automatically, if the Merge option is checked'); ?></small></li>
        <li class="pt-1 pb-1"><small><?php echo _('At this moment, service does not support <strong>new fields</strong> auto creation for existing Target tables'); ?></small></li>
        <li class="pt-1 pb-1"><small><?php echo _('This software is under development and testing, we do not provide any guarantees for result success'); ?></small></li>
        <li class="pt-1 pb-1"><small><?php echo _('<strong>Merge</strong> action will overwrite the Target database, make sure <strong>backup</strong> at hand!'); ?></small></li>
      </ul>
    </div>
    <div class="pb-5 mb-3">
      <?php if ($error) { ?>
        <div class="alert alert-danger text-center mt-4 mb-4" role="alert">
          <?php echo $error; ?>
        </div>
      <?php } else { ?>
        <form name="database" method="post" action="merge.php">
          <table class="table table-bordered mt-4 mb-4">
            <thead class="thead-dark">
              <tr>
                <th><?php echo _('Source'); ?></th>
                <th><?php echo _('Target'); ?></th>
                <th class="text-center">
                  <div class="form-check">
                    <input name="merge_all" type="checkbox" class="form-check-input" value="1" title="<?php echo _('Merge All'); ?>" onchange="$('.merge input[type=checkbox]').prop('checked', $(this).is(':checked') ? true : false)" />
                    <?php echo _('Merge'); ?>
                  </div>
                </th>
                <th class="text-center">
                  <div class="form-check">
                    <input name="truncate_all" type="checkbox" class="form-check-input" value="1" title="<?php echo _('Truncate All'); ?>" onchange="$('.truncate input[type=checkbox]').prop('checked', $(this).is(':checked') ? true : false)" />
                    <?php echo _('Truncate'); ?>
                  </div>
                </th>
              </tr>
            </thead>
            <tbody>
              <?php $structure_conflicts_total = 0; ?>
              <?php foreach ($tables as $table => $value) { ?>
                <tr>
                  <td>
                    <?php if (!$value['exists']['source']) {  ?>
                      &nbsp;
                    <?php } else {  ?>
                      <strong><?php echo $table; ?></strong>
                    <?php } ?>
                    <table class="table table-bordered mt-3 mb-3 d-none" id="source-<?php echo md5($table); ?>">
                      <?php $new_columns = 0; ?>
                      <?php if (isset($source_tables[$table])) { ?>
                        <?php foreach ($source_tables[$table] as $columns) { ?>
                          <tr>
                            <th colspan="2">
                              <?php if (!columnExists($columns['Field'], $target_tables, $table)) {  // @TODO Field compatibility ?>
                                <?php $new_columns++; ?>
                                <?php if (!$value['exists']['target']) {  ?>
                                  <i class="text-success"> + <?php echo $columns['Field']; ?></i>
                                  <input name="tables[<?php echo $table; ?>][columns][<?php echo $columns['Field']; ?>]" type="hidden" value="1" />
                                <?php } else {  ?>
                                  <i class="text-success"><s><?php echo $columns['Field']; ?></s></i>
                                <?php } ?>
                              <?php } else { ?>
                                <i><?php echo $columns['Field']; ?></i>
                                <div class="float-right">
                                  <div class="form-check">
                                    <input name="tables[<?php echo $table; ?>][columns][<?php echo $columns['Field']; ?>]" type="checkbox" class="form-check-input" value="1" checked="checked" title="<?php echo _('Merge'); ?>" />
                                  </div>
                                </div>
                              <?php } ?>
                            </th>
                          </tr>
                          <?php foreach ($columns as $column => $column_data) { ?>
                            <?php if (!in_array($column, ['Field'])) { ?>
                              <?php if (columnExists($columns['Field'], $target_tables, $table) && !typeEqual($column, $column_data, $target_tables, $table)) { ?>
                                <?php $structure_conflict = true; ?>
                              <?php } else { ?>
                                <?php $structure_conflict = false; ?>
                              <?php } ?>
                              <tr>
                                <td><?php echo $column; ?></td>
                                <td<?php echo ($structure_conflict ? ' class="text-danger"' : false); ?>><?php echo(is_null($column_data) ? 'NULL' : (empty($column_data) ? 'Empty' : $column_data)); ?></td>
                              </tr>
                            <?php } ?>
                          <?php } ?>
                        <?php } ?>
                      <?php } ?>
                    </table>
                    <?php if ($new_columns) { ?>
                      <div>
                        <small class="text-success"><?php echo sprintf(_('New columns: %s'), $new_columns); ?></small>
                      </div>
                    <?php } ?>
                  </td>
                  <td>
                    <?php if (!$value['exists']['target']) {  ?>
                      <strong class="text-success"> + <?php echo $table; ?></strong>
                    <?php } else if (!$value['exists']['source']) {  ?>
                      <strong class="text-muted"><?php echo $table; ?></strong>
                    <?php } else {  ?>
                      <strong><?php echo $table; ?></strong>
                    <?php } ?>
                    <?php $structure_conflicts = 0; ?>
                    <table class="table table-bordered mt-3 mb-3 d-none" id="target-<?php echo md5($table); ?>">
                      <?php if (isset($target_tables[$table])) { ?>
                        <?php foreach ($target_tables[$table] as $columns) { ?>
                          <tr>
                            <th colspan="2">
                              <?php if (!columnExists($columns['Field'], $source_tables, $table)) { ?>
                                <i class="text-muted"><?php echo $columns['Field']; ?></i>
                              <?php } else { ?>
                                <i><?php echo $columns['Field']; ?></i>
                              <?php } ?>
                            </th>
                          </tr>
                          <?php foreach ($columns as $column => $column_data) { ?>
                            <?php if (!in_array($column, ['Field'])) { ?>
                              <?php if (columnExists($columns['Field'], $source_tables, $table) && !typeEqual($column, $column_data, $source_tables, $table)) { ?>
                                <?php $structure_conflicts++; ?>
                                <?php $structure_conflicts_total++; ?>
                                <?php $structure_conflict = true; ?>
                              <?php } else { ?>
                                <?php $structure_conflict = false; ?>
                              <?php } ?>
                              <tr>
                                <td><?php echo $column; ?></td>
                                <td <?php echo ($structure_conflict ? ' class="text-danger"' : false); ?>><?php echo(is_null($column_data) ? 'NULL' : (empty($column_data) ? 'Empty' : $column_data)); ?></td>
                              </tr>
                            <?php } ?>
                          <?php } ?>
                        <?php } ?>
                      <?php } ?>
                    </table>
                    <?php if ($structure_conflicts) { ?>
                      <div>
                        <small class="text-danger"><?php echo sprintf(_('Structure conflicts: %s'), $structure_conflicts); ?></small>
                      </div>
                    <?php } ?>
                  </td>
                  <td class="text-center merge">
                    <div class="form-check">
                      <?php if (!$value['exists']['source']) {  ?>
                        <?php if (columnExists($columns['Field'], $source_tables, $table)) { ?>
                          <input name="tables[<?php echo $table; ?>][merge]" type="checkbox" class="form-check-input" value="1" title="<?php echo _('Table Already Exists'); ?>" />
                        <?php } else { ?>
                          <input name="tables[<?php echo $table; ?>][merge]" type="checkbox" class="form-check-input" value="1" title="<?php echo _('Table Already Exists'); ?>" disabled="disabled" />
                        <?php } ?>
                      <?php } else {  ?>
                        <input name="tables[<?php echo $table; ?>][merge]" type="checkbox" class="form-check-input" value="1" onchange="if($(this).is(':checked')){$('#source-<?php echo md5($table); ?>,#target-<?php echo md5($table); ?>').removeClass('d-none')}else{$('#source-<?php echo md5($table); ?>,#target-<?php echo md5($table); ?>').addClass('d-none')}" title="<?php echo _('Merge'); ?>" />
                      <?php } ?>
                    </div>
                  </td>
                  <td class="text-center truncate">
                    <div class="form-check">
                      <?php if (!$value['exists']['source']) {  ?>
                        <input name="tables[<?php echo $table; ?>][truncate]" type="checkbox" class="form-check-input" value="1" title="<?php echo _('Table Already Exists'); ?>" />
                      <?php } else {  ?>
                        <input name="tables[<?php echo $table; ?>][truncate]" type="checkbox" class="form-check-input" value="1" title="<?php echo _('Truncate'); ?>" />
                      <?php } ?>
                    </div>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
          <div class="float-right">
            <div class="input-group">
              <input type="text" name="rows" class="form-control" placeholder="<?php echo _('1000 rows / query'); ?>" />
              <input type="text" name="timeout" class="form-control" placeholder="<?php echo _('0 seconds step timeout'); ?>" />
              <div class="input-group-append">
                <button type="submit" class="btn <?php echo ($structure_conflicts_total ? _('btn-danger') : _('btn-success')); ?>" onclick="return confirm('<?php echo ($structure_conflicts_total ? _('Unresolved conflicts detected. Continue at your own risk!') : _('Target database will be updated!')); ?>')"><?php echo _('Merge'); ?></button>
              </div>
            </div>
          </div>
        </form>
      <?php } ?>
      <div class="float-left">
        <a href="connect" class="btn btn-secondary"><?php echo _('Back'); ?></a>
      </div>
    </div>
  </div>
</div>
<?php include('include' . DIRECTORY_SEPARATOR . 'footer.php'); ?>
