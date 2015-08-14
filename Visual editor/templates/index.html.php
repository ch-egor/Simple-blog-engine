<?php $view->extend('templates/base.html.php') ?>

<?php $view->set('title', 'Main page') ?>

<?php $view->start('content') ?>
<p>This is the main page</p>
<p><a href="<?php echo $this->route('/messages/') ?>">Go to messages</a></p>
<?php $view->stop() ?>
