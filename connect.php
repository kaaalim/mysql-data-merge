<?php $title = _('MySQL Data Merge - Connect Databases'); ?>
<?php include('include' . DIRECTORY_SEPARATOR . 'header.php'); ?>
<div class="d-flex flex-column flex-md-row align-items-center p-3 px-md-4 bg-white border-bottom box-shadow">
  <div class="container">
    <small>
      <strong class="mr-2"><?php echo _('Connect'); ?></strong>
      <span class="text-muted mr-2">&gt;</span>
      <span class="text-secondary mr-2"><?php echo _('Compare'); ?></span>
      <span class="text-muted mr-2">&gt;</span>
      <span class="text-secondary"><?php echo _('Merge'); ?></span>
    </small>
  </div>
</div>
<div class="bg-light pt-4 pb-4">
  <div class="container">
    <h1 class="h4 text-dark mb-4"><?php echo _('Connect Databases'); ?></h1>
    <div class="jumbotron pt-3 pb-1 alert-success">
      <ul>
        <li class="pt-1 pb-1"><small><?php echo _('Make sure your host supports remote connections'); ?></small></li>
        <li class="pt-1 pb-1"><small><?php echo _('The <strong>Source</strong> is database from the data will be read'); ?></small></li>
        <li class="pt-1 pb-1"><small><?php echo _('The <strong>Target</strong> is database where data will be written'); ?></small></li>
        <li class="pt-1 pb-1"><small><?php echo _('Optionally provide <strong>Initial Queries</strong> - encoding, sql_mode using native SQL syntax'); ?></small></li>
        <li class="pt-1 pb-1"><small><?php echo _('<strong>Compare</strong> action is safe and does not make any changes in <strong>Source</strong> and <strong>Target</strong> databases'); ?></small></li>
        <li class="pt-1 pb-1"><small><?php echo _('For security reasons, use temporary credentials, hosts and tables without critical fields, e.g. emails, passwords, credit card details etc'); ?></small></li>
        <li class="pt-1 pb-1"><small><?php echo _('We respect your privacy and do not collect any private data and do not track you by using Ads/Analytics JavaScript on this page'); ?></small></li>
      </ul>
    </div>
    <form name="database" method="post" action="compare">
      <div class="card-deck mt-4 mb-4">
        <div class="card box-shadow">
          <div class="card-header text-center pt-2 pb-2 bg-dark text-white">
            <h4 class="mb-0 h5"><?php echo _('Source'); ?></h4>
          </div>
          <div class="card-body">
            <div class="form-group">
              <label for="source-host"><?php echo _('Host'); ?></label>
              <input type="text" name="source_host" class="form-control" id="source-host" value="<?php echo (isset($_COOKIE['source_host']) ? $_COOKIE['source_host'] : false); ?>" />
            </div>
            <div class="form-group">
              <label for="source-port"><?php echo _('Port'); ?></label>
              <input type="text" name="source_port" class="form-control" id="source-port" value="<?php echo (isset($_COOKIE['source_port']) ? $_COOKIE['source_port'] : false); ?>" placeholder="3306" />
            </div>
            <div class="form-group">
              <label for="source-database"><?php echo _('Database'); ?></label>
              <input type="text" name="source_database" class="form-control" id="source-database" value="<?php echo (isset($_COOKIE['source_database']) ? $_COOKIE['source_database'] : false); ?>" />
            </div>
            <div class="form-group">
              <label for="source-username"><?php echo _('Username'); ?></label>
              <input type="text" name="source_username" class="form-control" id="source-username" value="<?php echo (isset($_COOKIE['source_username']) ? $_COOKIE['source_username'] : false); ?>" autocomplete="off" />
            </div>
            <div class="form-group">
              <label for="source-password"><?php echo _('Password'); ?></label>
              <input type="password" name="source_password" class="form-control" id="source-password" value="<?php echo (isset($_COOKIE['source_password']) ? $_COOKIE['source_password'] : false); ?>" autocomplete="off" />
            </div>
            <div class="form-group">
              <label for="source-init-queries"><span class="text-muted"><?php echo _('Initial Queries (optional)'); ?></span></label>
              <textarea name="source_init_queries" class="form-control" id="source-init-queries"><?php echo (isset($_COOKIE['source_init_queries']) ? $_COOKIE['source_init_queries'] : false); ?></textarea>
            </div>
          </div>
        </div>
        <div class="card box-shadow">
          <div class="card-header text-center pt-2 pb-2 bg-dark text-white">
            <h4 class="mb-0 h5"><?php echo _('Target'); ?></h4>
          </div>
          <div class="card-body">
            <div class="form-group">
              <label for="target-host"><?php echo _('Host'); ?></label>
              <input type="text" name="target_host" class="form-control" id="target-host" value="<?php echo (isset($_COOKIE['target_host']) ? $_COOKIE['target_host'] : false); ?>" />
            </div>
            <div class="form-group">
              <label for="target-port"><?php echo _('Port'); ?></label>
              <input type="text" name="target_port" class="form-control" id="target-port" value="<?php echo (isset($_COOKIE['target_port']) ? $_COOKIE['target_port'] : false); ?>" placeholder="3306" />
            </div>
            <div class="form-group">
              <label for="target-database"><?php echo _('Database'); ?></label>
              <input type="text" name="target_database" class="form-control" id="target-database" value="<?php echo (isset($_COOKIE['target_database']) ? $_COOKIE['target_database'] : false); ?>" />
            </div>
            <div class="form-group">
              <label for="target-username"><?php echo _('Username'); ?></label>
              <input type="text" name="target_username" class="form-control" id="target-username" value="<?php echo (isset($_COOKIE['target_username']) ? $_COOKIE['target_username'] : false); ?>" autocomplete="off" />
            </div>
            <div class="form-group">
              <label for="target-password"><?php echo _('Password'); ?></label>
              <input type="password" name="target_password" class="form-control" id="target-password" value="<?php echo (isset($_COOKIE['target_password']) ? $_COOKIE['target_password'] : false); ?>" autocomplete="off" />
            </div>
            <div class="form-group">
              <label for="target-init-queries"><span class="text-muted"><?php echo _('Initial Queries (optional)'); ?></span></label>
              <textarea name="target_init_queries" class="form-control" id="target-init-queries"><?php echo (isset($_COOKIE['target_init_queries']) ? $_COOKIE['target_init_queries'] : false); ?></textarea>
            </div>
          </div>
        </div>
      </div>
      <div class="text-right">
        <button type="submit" class="btn btn-dark"><?php echo _('Compare'); ?></button>
      </div>
    </form>
  </div>
</div>
<?php include('include' . DIRECTORY_SEPARATOR . 'footer.php'); ?>
