<?php $view->extend('templates/base.html.php') ?>

<?php $view->set('title', 'All posts') ?>

<?php $view->start('content') ?>
<div id="posts">
<?php foreach ($posts as $post): ?>
<?php $view->render('posts/post.html.php', array('post' => $post, 'links' => true)) ?>
<?php endforeach ?>
</div>
<?php $view->stop('content') ?>
