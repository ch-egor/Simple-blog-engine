    <div class="messageHead">
<?php $view->render('messages/messageHead.html.php', array('message' => $message)) ?>
    </div>
    <form id="editMessageForm" action="<?php echo $this->route("/messages/{$message['id']}/edit"); ?>" method="post">
      <div><textarea name="text" required placeholder="Type in your message here"><?php echo $message['text'] ?></textarea></div>
      <p class="actions">
        <input type="submit" name="submit" value="Save"> 
        <a class="messageCancel" href="<?php echo $view->route("/messages/{$message['id']}/") ?>">Cancel</a>
      </p>
    </form>
