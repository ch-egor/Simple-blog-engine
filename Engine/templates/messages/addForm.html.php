<p id="sendError"><?php if ($view->method('POST')) $view->render('messages/errorText.html.php') ?></p>
<form id="addMessageForm" action="<?php echo $this->route('/messages/'); ?>" method="post">
  <input type="hidden" name="redirect" value="1">
  <p>Your name: <input type="text" name="user" value="<?php echo $message['user'] ?>" placeholder="Your name" required pattern="^[\w ]+$"></p>
  <p>Message:</p>
  <div><textarea name="text" placeholder="Type in your message here" required><?php echo $message['text'] ?></textarea></div>
  <p><input type="submit" name="submit" value="Send"></p>
</form>
