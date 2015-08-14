<?php $view->extend('templates/base.html.php') ?>

<?php $view->set('title', 'Add post') ?>

<?php $view->start('content') ?>
<form id="addPostForm" action="<?php echo $view->route('/add'); ?>" method="post">
  <p>Your name: <input type="text" name="user" value="<?php echo $post['user'] ?>" placeholder="Your name"></p>
  <p>Title: <input type="text" name="title" value="<?php echo $post['title'] ?>" placeholder="Post title"></p>
  <p>Content:</p>
  <p><textarea name="content" placeholder="Post content"><?php echo $post['content'] ?></textarea></p>
  <p><input type="submit" name="submit" value="Add"></p>
</form>
<?php $view->stop() ?>
