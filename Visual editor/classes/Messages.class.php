<?php

/**
 * Interfaces with the "messages" table in the database
 */
class Messages {

    private $_messagesPerPage;

    public function __construct() {
        $this->_messagesPerPage = MESSAGES_PER_PAGE;
    }

    public function add(Array $message) {
        if (empty($message['user']) || empty($message['text']))
            return false;
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
        $user = $mysqli->real_escape_string($message['user']);
        $text = $mysqli->real_escape_string($message['text']);
        $query = "INSERT INTO messages (user, text) " .
                "VALUES ('{$user}', '{$text}')";
        $mysqli->query($query);
        $mysqli->close();
        return true;
    }
    
    public function update(Array $message) {
        $id = @intval($message['id']);
        if ($id <= 0 || empty($message['text']))
            return false;
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
        $text = $mysqli->real_escape_string($message['text']);
        $query = "UPDATE messages SET text = '{$text}' " .
                "WHERE message_id = {$id}";
        $mysqli->query($query);
        $mysqli->close();
        return true;
    }
    
    public function delete($messageId) {
        if (!is_int($messageId) || $messageId <= 0)
            return false;
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
        $query = "DELETE FROM messages WHERE message_id = {$messageId} LIMIT 1";
        $mysqli->query($query);
        $mysqli->close();
        return true;
    }

    public function get(Array $filter = array()) {
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
        $query = $this->generateSelectQuery($filter);
        $result = $mysqli->query($query);
        $messages = array();
        while ($row = $result->fetch_assoc()) {
            $row['id'] = intval($row['id']);
            $messages[] = $row;
        }
        $mysqli->close();
        return $messages;
    }

    public function getByPage($pageNumber) {
        $pageNumber = $this->getPageNumber($pageNumber);
        $filter = array();
        if (is_int($pageNumber) & $pageNumber > 0) {
            $filter['limit'] = $this->_messagesPerPage;
            $filter['offset'] = $this->_messagesPerPage * ($pageNumber - 1);
        }
        $filter['processDateTime'] = true;
        return $this->get($filter);
    }
    
    public function getById($messageId) {
        if (!is_int($messageId) || $messageId <= 0)
            return null;
        $filter['startId'] = $messageId;
        $filter['endId'] = $messageId;
        $filter['processDateTime'] = true;
        $messages = $this->get($filter);
        if (count($messages) == 1)
            return $messages[0];
        return null;
    }

    public function getPage($messageId) {
        if (!is_int($messageId) || $messageId <= 0)
            return null;
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
        $query = "SELECT message_id FROM messages ORDER BY message_id DESC";
        $result = $mysqli->query($query);
        $i = 0;
        while ($row = $result->fetch_row()) {
            $i++;
            if ($row[0] == $messageId)
                break;
        }
        $page = null;
        if (isset($row))
            $page = ceil($i / $this->_messagesPerPage);
        return $page;
    }

    public function count() {
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
        $query = "SELECT COUNT(*) FROM messages";
        $result = $mysqli->query($query);
        $row = $result->fetch_row();
        $count = $row[0];
        $mysqli->close();
        return $count;
    }

    public function pagesCount() {
        return ceil($this->count() / $this->_messagesPerPage);
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
        $columns = "message_id AS id";
        if (@!$filter['onlyIds']) {
            $columns .= ", user, text, created, updated";
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
            $whereConditions[] = "message_id = {$startId}";
        else {
            if ($startId > 0)
                $whereConditions[] = "message_id >= {$startId}";
            if ($endId > 0)
                $whereConditions[] = "message_id <= {$endId}";
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
        $query = "SELECT {$columns} FROM messages" .
                "{$whereClause} ORDER BY id {$order} {$limitClause}";
        $mysqli->close();
        return $query;
    }

    private function getFilterByPage($pageNumber) {
        if (!is_int($pageNumber) || $pageNumber <= 0)
            return array();
        $filter = array();
        $filter['limit'] = $this->_messagesPerPage;
        $filter['offset'] = $this->_messagesPerPage * ($pageNumber - 1);
        $filter['processDateTime'] = true;
        return $filter;
    }

}
