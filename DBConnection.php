<?php

class DBConnection {

    private $host = "localhost";
    private $user = "root";
    private $password = "my@dm1n!";
    private $dbName = "bkash_transaction";

    public function getConnection() {
        $connection = new mysqli($this->host, $this->user, $this->password, $this->dbName);
        return $connection;
    }

}

