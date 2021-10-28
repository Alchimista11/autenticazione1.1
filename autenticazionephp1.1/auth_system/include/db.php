<?php
$dsn = "mysql:host=localhost;dbname=auth_system;charset=utf8";
try{
  $PDO = new PDO($dsn, "root", "root");
  $PDO->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
  echo "Errore connessione al database";
}