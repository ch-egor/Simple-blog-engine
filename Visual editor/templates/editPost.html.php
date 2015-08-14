<?php $view->extend('templates/base.html.php') ?>

<?php $view->set('title', 'Edit post') ?>

<?php $view->start('content') ?>
<div id="posts">
    <form id="editPostForm" action="<?php echo $view->route("/{$post['id']}/edit"); ?>" method="post">
    <p>Title: <input type="text" name="title" value="<?php echo $post['title'] ?>" placeholder="Post title"></p>
    <p>Content:</p>
    <div><textarea name="content" placeholder="Post content"><?php echo $post['content'] ?></textarea></div>
    <p class="actions">
      <input type="submit" name="submit" value="Save">
      <a class="postCancel" href="<?php echo $view->route("/{$post['id']}/") ?>">Cancel</a>
    </p>
  </form>
</div>
<?php $view->stop() ?>
