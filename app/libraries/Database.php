<?php
    /**
     * PDO Database class
     * Connexion à la BD
     * Execution des requêtes
     */
    
    class Database{
        private $host = DB_HOST;
        private $user = DB_USER;
        private $pass = DB_PASS;
        private $dbname = DB_NAME;

        private $dbh;
        private $stmt;
        private $error;

        public function __construct(){
            //Set DSN
            $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname.';charset=utf8';
            //options
            $options = array(
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            );

            //pdof instance
            try {
                $this->dbh = new PDO($dsn,$this->user,$this->pass,$options);
            } catch (PDOException $e) {
                $this->error = $e->getMessage();
                echo $this->error;
            }
        }

        //Requêtes
        public function query($sql){
            $this->stmt = $this->dbh->prepare($sql);
        }
        //Bind values
        public function bind($param, $value, $type = null){
            if(is_null($type)){
                switch (true) {
                    case is_int($value):
                        $type = PDO::PARAM_INT;
                        break;
                    case is_bool($value):
                        $type = PDO::PARAM_BOOL;
                        break;
                    case is_null($value):
                        $type = PDO::PARAM_NULL;
                        break;
                    default:
                        $type = PDO::PARAM_STR;
                }
            }
            $this->stmt->bindValue($param,$value,$type);
        }
        //Execute
        public function execute(){
            return $this->stmt->execute();
        }
        //get results 
        public function resultSet(){
            $this->execute();
            return $this->stmt->fetchAll(PDO::FETCH_OBJ);
        }
        //get single record
        public function single(){
            $this->execute();
            return $this->stmt->fetch(PDO::FETCH_OBJ);
        }
        //row count
        public function rowCount(){
            return $this->stmt->rowCount();
        }

        //LastInsertId

        public function lastInsertId()
        {
            return $this->dbh->lastInsertId();
        }


    }