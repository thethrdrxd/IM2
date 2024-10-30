<?php
if (!class_exists('Connection')) {
    class Connection {
        private $server = "mysql:host=localhost;dbname=prelim";
        private $user = "root";
        private $pass = "";

        private $options = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
        );
        
        protected $con;

        // Function to open the database connection
        public function OpenConnection() {
            try {
                $this->con = new PDO($this->server, $this->user, $this->pass, $this->options);
                return $this->con;
            } catch (PDOException $e) {
                echo "There is some problem in the connection: " . $e->getMessage();
            }
        }

        // Function to close the database connection
        public function closeConnection() {
            $this->con = null;
        }
    }
}
?>
