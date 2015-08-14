<?php $view->extend('templates/base.html.php') ?>

<?php $view->set('title', $post['title']) ?>

<?php $view->start('content') ?>
<div id="posts">
<?php $view->render('posts/post.html.php', array('post' => $post, 'links' => false)) ?>
</div>
<?php $view->stop('content') ?>
