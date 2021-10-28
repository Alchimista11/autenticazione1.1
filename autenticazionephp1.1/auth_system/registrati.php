<?php
  require "./include/default.php";
  $ris = "";
  if($_POST){
    $ris = $auth->registraNuovoUtente($_POST);
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
  <a href="registrati.php" class="btn btn-info">Registrati</a>
  <a href="login.php" class="btn btn-info">Login</a>
  <hr>
  <?= $ris ?>

  <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
    <div class="form-group">
      <input type="text" name="uname" class="form-control" placeholder="Username *">
    </div>
    <div class="form-group">
      <input type="password" name="pwd" class="form-control" placeholder="Password *">
    </div>
    <div class="form-group">
      <input type="password" name="re_pwd" class="form-control" placeholder="Conferma password *">
    </div>
    <div class="form-group">
      <input type="text" name="nome" class="form-control" placeholder="Nome *">
    </div>
    <div class="form-group">
      <input type="email" name="email" class="form-control" placeholder="Email *">
    </div>
    <div class="form-group">
      <input type="submit" class="btn btn-info" value="Invia">
    </div>
  </form>
</body>
</html>