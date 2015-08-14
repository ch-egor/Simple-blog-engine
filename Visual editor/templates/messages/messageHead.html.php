      <a id="<?php echo $message['id'] ?>"></a>
      <span class="user"><?php echo $message['user'] ?></span>
      <span class="messageInfo">
        <time class="datetime" datetime="<?php echo $message['created'] ?>"><?php echo $message['created_processed'] ?></time>
        <a class="messageLink" href="<?php echo $view->route("/messages/{$message['id']}") ?>">#<?php echo $message['id'] ?></a>
      </span>
