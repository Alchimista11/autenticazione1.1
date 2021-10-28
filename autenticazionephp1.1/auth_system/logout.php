<?php
require "./include/default.php";
try{
  if($auth->logout()){
    header("Location:index.php");
    exit; 
  }
} catch(PDOException $e){
  echo $e->getMessage();
}