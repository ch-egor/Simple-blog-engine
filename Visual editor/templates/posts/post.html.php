  <div class="post">
    <h1>
<?php if ($links): ?>
      <a href="<?php echo $view->route("/{$post['id']}/") ?>">
<?php endif ?>
      <?php echo $post['title'] ?>
<?php if ($links): ?>
      </a>
<?php endif ?>
    </h1>
    <p id="postHead"><span class="datetime"><?php echo $post['created_processed'] ?></span> by <span class="user"><?php echo $post['user'] ?></span></p>
    <div id="postBody"><?php echo $post['content'] ?></div>
    <div class="actions">
      <a class="postEdit" href="<?php echo $view->route("/{$post['id']}/edit") ?>">Edit</a>
      <a class="postDelete" href="<?php echo $view->route("/{$post['id']}/delete") ?>">Delete</a>
    </div>
    <hr>
  </div>
