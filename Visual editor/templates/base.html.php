<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php $view->output('title', 'Default Title') ?></title>
    <link rel="stylesheet" href="<?php echo $view->asset('css/style.css') ?>">
    <!--[if lt IE 9]>
      <script src="<?php echo $view->asset('js/html5shiv.min.js') ?>"></script>
      <script src="<?php echo $view->asset('js/respond.min.js') ?>"></script>
    <![endif]-->
<?php $view->output('includeTop') ?>
  </head>
  <body>
    <header><?php $view->render('base/header.html.php') ?></header>
    <div id="content">

<?php $view->output('content') ?>

    </div>
    <footer><?php $view->render('base/footer.html.php') ?></footer>
<?php $view->output('includeBottom') ?>
  </body>
</html>