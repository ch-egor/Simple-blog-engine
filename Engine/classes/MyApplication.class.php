<?php

/**
 * Maps URIs to action methods, also contains actions themselves
 */
class MyApplication extends Application {

    public function init() {
        $this->match('/', 'index');
        $this->match('/messages/', 'messages');
        $this->match('/messages/api', 'messagesApi');
        $this->match('/messages/{messageId}/', 'findMessage');
        $this->match('/messages/{messageId}/edit', 'editMessage');
        $this->match('/messages/{messageId}/delete', 'deleteMessage');
        $this->match('/add', 'addPost');
        $this->match('/api', 'postsApi');
        $this->match('/{postId}/', 'viewPost');
        $this->match('/{postId}/edit', 'editPost');
        $this->match('/{postId}/delete', 'deletePost');
    }

    public function indexAction() {
        $posts = new Posts();
        $allPosts = $posts->get(array('processDateTime' => 1));
        $this->render('posts.html.php', array('posts' => $allPosts));
    }
    
    public function addPostAction() {
        $ajax = !!$this->post('ajax');
        $post = array('user' => '', 'title' => '', 'content' => '');
        if ($this->method('post')) {
            $post = array('user' => $this->post('user'), 
					'title' => $this->escape($this->post('title')), 
					'content' => $this->escape($this->post('content')));
            // if all goes well, put the post into the database
            if (preg_match('/^[\w ]+$/', $post['user']) && 
                    !empty($post['title']) && !empty($post['content'])) {
                $posts = new Posts();
                $posts->add($post);
                // if not an AJAX request, redirect
                if (!$ajax)
                    $this->redirect("/");
                exit();
            }
        }
        $this->render('addPost.html.php', array('post' => $post));
    }
    
    public function postsApiAction() {
        
    }

    public function viewPostAction($postId) {
        $posts = new Posts();
        $post = $posts->getById(intval($postId));
        if (!$post) {
            //header('Location: ' . $this->route('/'));
            exit();
        }
        $this->render('viewPost.html.php', array('post' => $post));
    }
    
    public function editPostAction($postId) {
        $ajax = !!$this->post('ajax');
        $postId = intval($postId);
        $posts = new Posts();
        $post = $posts->getById($postId);
        // quit if the post does not exist
        if (!isset($post)) {
            // if this is not an AJAX request, redirect
            if ($this->method('get') || !$ajax)   
                $this->redirect('/');
            exit();
        }
        if ($this->method('post')) {
            $post = array('id' => $postId, 
                'title' => $this->escape($this->post('title')), 
                'content' => $this->escape($this->post('content')));
            $error = empty($post['title']) || empty($post['content']);
            if (!$error) {
                $posts->update($post);
                if (!$ajax)
                    $this->redirect("/{$postId}/");
                exit();
            }
            // if this is an AJAX request, do not redirect
            if ($ajax) {
                $this->render('posts/errorText.html.php');
                exit();
            }
        }
        $this->render('editPost.html.php', array('post' => $post));
    }
    
    public function deletePostAction($postId) {
        $ajax = !!$this->post('ajax');
        $postId = intval($postId);
        $posts = new Posts();
        $post = $posts->getById($postId);
        // quit if the message does not exist
        if (!isset($post)) {
            // if this is not an AJAX request, redirect
            if ($this->method('get') || !$ajax)   
                $this->redirect('/');
            exit();
        }
        if ($this->method(array('post', 'delete'))) {
            $posts->delete($postId);
            // redirect if it is not an AJAX request
            if (!$ajax) {
                $this->redirect('/');
                exit();
            }
        }
        $this->render('deletePost.html.php', array('post' => $post));
    }

    public function messagesAction() {
        $ajax = !!$this->post('ajax');
        $message = array('user' => '', 'text' => '');
        $messages = new Messages();
        // if the method is POST, process submitted data
        if ($this->method('post')) {
            $message = array('user' => $this->post('user'), 
                'text' => $this->escape($this->post('text')));
            // if all is well, include the message into the DB
            if (preg_match('/^[\w ]+$/', $message['user']) && 
                    !empty($message['text'])) {
                $messages->add($message);
                // if not an AJAX request, redirect
                if (!$ajax)
                    $this->redirect('/messages/');
                exit();
            }
            // if this is an AJAX request, just send the error message
            if ($ajax) {
                $this->render('messages/errorText.html.php');
                exit();
            }
        }
        $pageNumber = $messages->getPageNumber(intval($this->get('page')));
        $args = array('addMessage' => $message, 
            'messages' => $messages->getByPage($pageNumber), 
            'pagesCount' => $messages->pagesCount());
        $this->render('messages.html.php', $args);
    }

    public function messagesApiAction() {
        $messages = new Messages();
        $onlyIds = !!$this->get('onlyIds');
        $editId = intval($this->get('editId'));
		$startId = $editId ? $editId : intval($this->get('startId'));
		$endId = $editId ? $editId : intval($this->get('endId'));
        $renderHtml = strcasecmp($this->get('format'),'html') == 0 || $editId;
        $filter = array('onlyIds' => $onlyIds, 
				'startId' => $startId,
				'endId' => $endId,
				'updatedAfter' => $this->get('updatedAfter'), 
				'olderFirst' => $this->get('olderFirst'), 
				'offset' => intval($this->get('offset')), 
				'limit' => intval($this->get('limit')), 
				'processDateTime' => $renderHtml);
        $messagesArray = $messages->get($filter);
		// if only IDs were requested, compile a list of them
        if ($onlyIds) {
            $ids = array();
            foreach ($messagesArray as $message)
                $ids[] = $message['id'];
            $messagesArray = $ids;
        }
		// generate HTML for messages if necessary
        else if ($renderHtml) {
            for ($i = 0; $i < count($messagesArray); $i++) {
                $id = $messagesArray[$i]['id'];
                ob_start();
                if ($id != $editId)
                    $this->render('messages/message.html.php', 
                            array('message' => $messagesArray[$i]), true);
                else
                    $this->render('messages/editForm.html.php', 
                            array('message' => $messagesArray[$i]), true);
                $html = ob_get_clean();
                $html = preg_replace('/="([^"]*)"/', "='$1'", $html);
				$messagesArray[$i] = array('id' => $id, 'html' => $html);
            }
        }
        $json = json_encode($messagesArray);
        header('Cache-Control: no-cache');
        $callback = $this->get('callback');
		// check if this is a JSONP request
        if (preg_match('/^[a-zA-Z_][\w_]*$/', $callback)) {
            $json = "{$callback}({$json});";
            header('Content-Type: application/javascript; charset=utf-8');
        } else
            header('Content-Type: application/json; charset=utf-8');
        echo $json;
    }

    public function findMessageAction($messageId) {
        $messages = new Messages();
        $page = $messages->getPage(intval($messageId));
        $uriParams = '';
        if (isset($page))
            $uriParams = "?page={$page}#{$messageId}";
        $uri = $this->route("/messages/{$uriParams}");
        header("Location: {$uri}");
    }
    
    public function editMessageAction($messageId) {
        $ajax = !!$this->post('ajax');
        $messageId = intval($messageId);
        $messages = new Messages();
        $message = $messages->getById($messageId);
        // quit if the message does not exist
        if (!isset($message)) {
            // if this is not an AJAX request, redirect
            if ($this->method('get') || !$ajax)   
                $this->redirect('/messages/');
            exit();
        }
        if ($this->method('post')) {
            $message = array('id' => $messageId, 
					'text' => $this->escape($this->post('text')));
            $error = empty($message['text']);
            if (!$error) {
                $messages->update($message);
                if (!$ajax)
                    $this->redirect("/messages/{$messageId}/");
                exit();
            }
            // if submit is not set, this is an AJAX request, so do not redirect
            if ($ajax) {
                $this->render('messages/errorText.html.php');
                exit();
            }
        }
        $this->render('editMessage.html.php', array('message' => $message));
    }
    
    public function deleteMessageAction($messageId) {
        $ajax = !!$this->post('ajax');
        $messageId = intval($messageId);
        $messages = new Messages();
        $message = $messages->getById($messageId);
        // quit if the message does not exist
        if (!isset($message)) {
            // if this is not an AJAX request, redirect
            if ($this->method('get') || !$ajax)   
                $this->redirect('/messages/');
            exit();
        }
        if ($this->method(array('post', 'delete'))) {
            $messages->delete($messageId);
            // redirect if it is not an AJAX request
            if (!$ajax) {
                $this->redirect('/messages/');
                exit();
            }
        }
        $this->render('deleteMessage.html.php', array('message' => $message));
    }

    public function notFoundAction() {
        header("HTTP/1.1 404 Not Found");
        header("Refresh: 10; url={$this->route('/')}");
        $page = array('title' => 'Not found', 
				'content' => 'The page that you are trying to obtain has not been found. ' .
                'You will be taken to the main page in 10 seconds.');
        $this->render('base.html.php', $page);
    }

}
