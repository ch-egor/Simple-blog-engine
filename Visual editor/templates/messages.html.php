<?php $view->extend('templates/base.html.php') ?>

<?php $view->set('title', 'Messages') ?>
<?php $view->start('includeBottom') ?>
    <script src="<?php echo $view->asset('js/messages.js') ?>"></script>
<?php $view->stop() ?>

<?php $view->start('content') ?>
<?php $view->render('messages/addForm.html.php', array('message' => $addMessage)) ?>
<div id="messages">
<?php foreach ($messages as $message): ?>
  <div class="message" id="message<?php echo $message['id'] ?>">
<?php $view->render('messages/message.html.php', array('message' => $message)) ?>
  </div>
<?php endforeach ?>
  <nav id="messagesNav">
<?php for ($i = 1; $i <= $pagesCount; $i++): ?>
    <a href="?page=<?php echo $i ?>"><?php echo $i ?></a>
<?php endfor ?>
  </nav>
</div>
<?php $view->stop() ?>
