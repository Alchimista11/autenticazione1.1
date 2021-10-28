<?php
  require "./include/default.php";
  if($_POST) {
    try {
      if($auth->login($_POST['username'],$_POST['password'])){
        header("location:profilo.php");
        exit;
      }
      
    } catch(PDOException $e){
      echo $e->getMessage();
    }
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
  <link rel="stylesheet" href="css/stile.css">
  <title></title>
</head>
<body>
  <a href="index.php" class="btn btn-info">Home</a>
  <a href="Registrati.php" class="btn btn-info">Registrati</a>
  <a href="login.php" class="btn btn-info">Login</a>
  <hr>

  <form action="<?= $_SERVER['PHP_SELF'] ?>" method="POST">
    <div class="form-group">
      <input type="text" name="username" placeholder="Inserisci username" class="form-control">
    </div>
    <div class="form-group">
      <input type="password" name="password" placeholder="Inserisci password" class="form-control ">
    </div>
    <div class="form-group">
      <input type="submit" value="Effettua il login" class="btn btn-info">
    </div>
  </form>
</body>
</html>