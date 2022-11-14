<?php
    class Dbconfig {
        protected $serverName;
        protected $userName;
        protected $passCode;
        protected $dbName;
        protected $token;
        function Dbconfig() {
            $this -> serverName = 'localhost';
            $this -> userName = '';
            $this -> passCode = '';
            $this -> dbName = '';
        }
    }
?>
