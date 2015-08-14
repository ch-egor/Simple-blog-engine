<?php $view->extend('templates/base.html.php') ?>

<?php $view->set('title', 'Delete a message') ?>

<?php $view->start('content') ?>
<p>Are you sure you want to delete the message below?</p>
<div id="messages">
  <div class="message" id="message<?php echo $message['id'] ?>">
    <p class="messageHead">
      <a id="<?php echo $message['id'] ?>"></a>
      <span class="messageUser"><?php echo $message['user'] ?></span>
      <time class="messageDatetime" datetime="<?php echo $message['created'] ?>"><?php echo $message['created_processed'] ?></time>
    </p>
    <div class="messageBody">
      <p class="messageText"><?php echo $message['text'] ?></p>
<?php if ($message['updated'] != $message['created']): ?>
      <p class="messageUpdated">Updated <time datetime="<?php echo $message['updated'] ?>"><?php echo $message['updated_processed'] ?></time></p>
<?php endif ?>
    </div>
  </div>
  <form action="<?php $view->route("/messages/{$message['id']}/delete") ?>" method="post">
    <input type="submit" name="submit" value="Yes">
  </form>
</div>
<p><a href="<?php echo $view->route("/messages/{$message['id']}") ?>">Back to messages</a></p>
<?php $view->stop() ?>
