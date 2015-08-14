    <div class="messageHead">
<?php $view->render('messages/messageHead.html.php', array('message' => $message)) ?>
    </div>
    <div class="messageBody">
      <div class="messageText"><?php echo $message['text'] ?></div>
<?php if ($message['updated'] != $message['created']): ?>
      <div class="messageUpdated">Updated <time datetime="<?php echo $message['updated'] ?>"><?php echo $message['updated_processed'] ?></time></div>
<?php endif ?>
      <div class="actions">
        <a class="messageEdit" href="<?php echo $view->route("/messages/{$message['id']}/edit") ?>">Edit</a>
        <a class="messageDelete" href="<?php echo $view->route("/messages/{$message['id']}/delete") ?>">Delete</a>
      </div>
    </div>
