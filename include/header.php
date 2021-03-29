<?php require_once('require' . DIRECTORY_SEPARATOR . 'bootstrap.php'); ?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <title><?php echo isset($title) ? $title : _('MySQL Data Merge - Differencing & Merging Databases Online'); ?></title>
    <meta name="description" content="<?php echo isset($description) ? $description : _('Free, Online Tool for Differencing & Merging MySQL Databases'); ?>" />
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/common.min.js?up=<?php echo rand(); ?>"></script>
    <link href="css/bootstrap.min.css" rel="stylesheet" media="screen" />
  </head>
  <body>
    <header>
      <div class="d-flex flex-column flex-md-row align-items-center p-3 px-md-4 bg-dark border-bottom box-shadow text-white">
        <div class="container">
          <div class="row">
            <div class="col-lg-6 col-md-12">
              <a class="text-white text-decoration-none" href="/"><strong><?php echo _('DataMerge'); ?></strong><sup>.online</sup></a>
            </div>
            <div class="col-lg-6 col-md-12 text-md-left text-lg-right">
              <small><?php echo sprintf(_('Successfully Merged Databases: <strong>%s</strong>, Tables: <strong>%s</strong>, Rows: <strong>%s</strong>'), $datamerge_mysql_databases, $datamerge_mysql_tables, $datamerge_mysql_rows); ?></small>
            </div>
          </div>
        </div>
      </div>
    </header>
    <main>
