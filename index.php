<?php include('include' . DIRECTORY_SEPARATOR . 'header.php'); ?>
<section class="jumbotron text-center bg-white mb-0">
  <div class="container">
    <h1 class="jumbotron-heading"><?php echo _('MySQL Data Merge'); ?></h1>
    <p class="lead text-muted"><?php echo _('Online Tool for Differencing & Merging MySQL Databases'); ?></p>
    <p>
      <a href="connect.php" class="btn btn-primary my-2"><?php echo _('Merge Online'); ?></a>
      <a href="download.php" class="btn btn-secondary my-2"><?php echo _('Download'); ?></a>
    </p>
  </div>
</section>
<div class="bg-light pt-3 pb-3">
  <div class="container">
    <div class="row">
      <div class="col-md-4">
        <div class="card mb-4 mt-4 box-shadow text-center">
          <div class="card-body">
            <img src="image/connect.png" alt="<?php echo _('Connect'); ?>" class="img-fluid mb-3" />
            <p class="card-text mt-1"><?php echo _('Connect Source and Target databases'); ?></p>
          </div>
        </div>
      </div>
      <div class="col-md-4 ">
        <div class="card mb-4 mt-4 box-shadow text-center">
          <div class="card-body">
            <img src="image/compare.png" alt="<?php echo _('Compare'); ?>" class="img-fluid mb-3" />
            <p class="card-text mt-1"><?php echo _('Compare database structure'); ?></p>
          </div>
        </div>
      </div>
      <div class="col-md-4 ">
        <div class="card mb-4 mt-4 box-shadow text-center">
          <div class="card-body">
            <img src="image/merge.png" alt="<?php echo _('Merge'); ?>" class="img-fluid mb-3" />
            <p class="card-text mt-1"><?php echo _('Enjoy merging results'); ?></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include('include' . DIRECTORY_SEPARATOR . 'footer.php'); ?>
