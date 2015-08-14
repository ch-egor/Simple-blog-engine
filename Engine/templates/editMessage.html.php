<?php $view->extend('templates/base.html.php') ?>

<?php $view->set('title', 'Edit a message') ?>

<?php $view->start('content') ?>
<div id="messages">
  <div class="message" id="message<?php echo $message['id'] ?>">
    <p id="sendError"><?php if ($view->method('POST')) $view->render('messages/errorText.html.php') ?></p>
<?php $view->render('messages/editForm.html.php', array('message' => $message)) ?>
  </div>
</div>
<?php $view->stop() ?>
