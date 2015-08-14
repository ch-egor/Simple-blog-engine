<?php

/**
 * Interfaces with the "posts" table in the database
 */
class Posts {

    private $_postsPerPage;

    public function __construct() {
        $this->_postsPerPage = POSTS_PER_PAGE;
    }

    function add(Array $post) {
        if (empty($post['user']) || empty($post['title']) || empty($post['content']))
            return false;
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
        $user = $mysqli->real_escape_string($post['user']);
        $title = $mysqli->real_escape_string($post['title']);
        $content = $mysqli->real_escape_string($post['content']);
        $query = "INSERT INTO posts (user, title, content) " .
                "VALUES ('{$user}', '{$title}', '{$content}')";
        $mysqli->query($query);
        $mysqli->close();
        return true;
    }

    public function update(Array $post) {
        $id = @intval($post['id']);
        if ($id <= 0 || empty($post['title']) || empty($post['content']))
            return false;
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
        $title = $mysqli->real_escape_string($post['title']);
        $content = $mysqli->real_escape_string($post['content']);
        $query = "UPDATE posts SET title = '{$title}', content = '{$content}' " .
                "WHERE post_id = {$id}";
        $mysqli->query($query);
        $mysqli->close();
        return true;
    }

    public function delete($postId) {
        if (!is_int($postId) || $postId <= 0)
            return false;
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
        $query = "DELETE FROM posts WHERE post_id = {$postId} LIMIT 1";
        $mysqli->query($query);
        $mysqli->close();
        return true;
    }

    public function get(Array $filter = array()) {
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
        $query = $this->generateSelectQuery($filter);
        $result = $mysqli->query($query);
        $posts = array();
        while ($row = $result->fetch_assoc()) {
            $row['id'] = intval($row['id']);
            $posts[] = $row;
        }
        $mysqli->close();
        return $posts;
    }

    public function getByPage($pageNumber) {
        $pageNumber = $this->getPageNumber($pageNumber);
        $filter = array();
        if (is_int($pageNumber) & $pageNumber > 0) {
            $filter['limit'] = $this->_postsPerPage;
            $filter['offset'] = $this->_postsPerPage * ($pageNumber - 1);
        }
        $filter['processDateTime'] = true;
        return $this->get($filter);
    }

    public function getById($postId) {
        if (!is_int($postId) || $postId <= 0)
            return null;
        $filter['startId'] = $postId;
        $filter['endId'] = $postId;
        $filter['processDateTime'] = true;
        $posts = $this->get($filter);
        if (count($posts) == 1)
            return $posts[0];
        return null;
    }

    public function getPage($postId) {
        if (!is_int($postId) || $postId <= 0)
            return null;
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
        $query = "SELECT post_id FROM posts ORDER BY post_id DESC";
        $result = $mysqli->query($query);
        $i = 0;
        while ($row = $result->fetch_row()) {
            $i++;
            if ($row[0] == $postId)
                break;
        }
        $page = null;
        if (isset($row))
            $page = ceil($i / $this->_postsPerPage);
        return $page;
    }

    public function count() {
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
        $query = "SELECT COUNT(*) FROM posts";
        $result = $mysqli->query($query);
        $row = $result->fetch_row();
        $count = $row[0];
        $mysqli->close();
        return $count;
    }

    public function pagesCount() {
        return ceil($this->count() / $this->_postsPerPage);
    }

    public function getPageNumber($requestedNumber) {
        if (!is_int($requestedNumber))
            $requestedNumber = 1;
        $pageNumber = min(max(1, $requestedNumber), $this->pagesCount());
        return $pageNumber;
    }

    private function generateSelectQuery(Array $filter = array()) {
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
        // defining columns being selected
        $columns = "post_id AS id";
        if (@!$filter['onlyIds']) {
            $columns .= ", user, title, content, created, updated";
            if (@$filter['processDateTime'])
                $columns .= ", DATE_FORMAT(created, '%d.%m.%Y %T') AS created_processed, " .
                        "DATE_FORMAT(updated, '%d.%m.%Y %T') AS updated_processed";
        }

        // enforcing WHERE-conditions upon selection
        $whereClause = '';
        $whereConditions = array();
        $startId = @intval($filter['startId']);
        $endId = @intval($filter['endId']);
        if ($startId == $endId && $startId > 0)
            $whereConditions[] = "post_id = {$startId}";
        else {
            if ($startId > 0)
                $whereConditions[] = "post_id >= {$startId}";
            if ($endId > 0)
                $whereConditions[] = "post_id <= {$endId}";
        }
        if (isset($filter['updatedAfter'])) {
            $updatedAfter = $mysqli->escape_string($filter['updatedAfter']);
            $whereConditions[] = "updated > '{$updatedAfter}'";
        }
        if (count($whereConditions) > 0)
            $whereClause = ' WHERE ' . implode(' AND ', $whereConditions);

        // choosing order
        if (!@$filter['olderFirst'])
            $order = 'DESC';
        else
            $order = 'ASC';

        // setting up the limit and the offset
        $limitClause = '';
        $limit = @intval($filter['limit']);
        $offset = @intval($filter['offset']);
        if ($limit > 0) {
            $limitClause = "LIMIT {$limit}";
            if ($offset > 0)
                $limitClause .= " OFFSET {$offset}";
        }

        // piecing the query together
        $query = "SELECT {$columns} FROM posts" .
                "{$whereClause} ORDER BY id {$order} {$limitClause}";
        $mysqli->close();
        return $query;
    }

    private function getFilterByPage($pageNumber) {
        if (!is_int($pageNumber) || $pageNumber <= 0)
            return array();
        $filter = array();
        $filter['limit'] = $this->_postsPerPage;
        $filter['offset'] = $this->_postsPerPage * ($pageNumber - 1);
        $filter['processDateTime'] = true;
        return $filter;
    }

}
