<?php $view->extend('templates/base.html.php') ?>

<?php $view->set('title', 'Delete a post') ?>

<?php $view->start('content') ?>
<p>Are you sure you want to delete the post below?</p>
<div id="posts">
  <div class="post">
    <h1><?php echo $post['title'] ?></h1>
    <div id="postMetadata"><?php echo $post['created_processed'] ?> by <?php echo $post['user'] ?></div>
    <div id="postContent"><?php echo $post['content'] ?></div>
  </div>
  <form action="<?php $view->route("/{$post['id']}/delete") ?>" method="post">
    <p class="actions">
      <input type="submit" name="submit" value="Yes">
      <a href="<?php echo $view->route("/{$post['id']}/") ?>">No</a>
    </p>
  </form>
</div>

<?php $view->stop() ?>
