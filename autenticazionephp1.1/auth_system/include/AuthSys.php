<?php
class AuthSys
{
  private $PDO;
  private $mail;

  public function __construct($PDOconn, $mail)
  {
    $this->PDO = $PDOconn;
    $this->mail = $mail;
  }

  //VIENE INVOCATO DA REGISTRANUOVOUTENTE
  public function usernameExists($in_uname)
  {
    $q = "SELECT * FROM Utenti WHERE (username = :uname)";
    $rq = $this->PDO->prepare($q);
    $rq->bindParam(":uname", $in_uname, PDO::PARAM_STR);
    $rq->execute();
    if ($rq->rowCount() > 0) {
      return true;
    }
    return false;
  }

  //VIENE INVOCATO DA REGISTRANUOVOUTENTE
  public function checkModuli($post)
  {
    //CONTROLLIAMO SE LO USERNAME INSERITO HA LETTERE E NUMERI E SIA COMPRESO TRA GLI 8 E I 12 CARATTERI
    if (!(ctype_alnum($post['uname']) && mb_strlen($post['uname']) >= 8 && mb_strlen($post['uname']) <= 12)) {
      throw new Exception("Username non valida");
    }

    //ASSICURIAMOCI CHE LA PASSWORD INSERITA ABBIA SOLO LETTERE, NUMERI ED ALCUNI CARATTERI SPECIALI
    if (!preg_match('/^[a-zA-Z0-9_\-\$@#!]{8,}$/', $post['pwd'])) {
      throw new Exception("Password non valida");
    }

    //CONTROLLIAMO CHE PASSWORD E CONFERMA PASSWORD COINCIDANO
    if (strcmp($post['pwd'], $post['re_pwd']) !== 0) {
      throw new Exception("Password e conferma password non coincidono");
    }

    //CONTROLLIAMO CHE LA EMAIL PASSATA SIA VALIDA
    if (!filter_var($post['email'], FILTER_VALIDATE_EMAIL)) {
      throw new Exception("Email non valdia");
    }

    //CONTROLLIAMO CHE SIA PRESENTE IL NOME
    if (mb_strlen($post['nome']) == 0) {
      throw new Exception("Nome non indicato");
    }
  }

  //VIENE INVOCATO DA REGISTRANUOVOUTENTE
  public function addUser($post, $pwd_hash, $token): int
  {
    $q = "INSERT INTO Utenti (username, password, nome, email, token) VALUES (:uname, :pwd , :nome, :email, :token)";
    $rq = $this->PDO->prepare($q);
    $rq->bindParam(":uname", $post['uname'], PDO::PARAM_STR);
    $rq->bindParam(":pwd", $pwd_hash, PDO::PARAM_STR);
    $rq->bindParam(":nome", $post['nome'], PDO::PARAM_STR);
    $rq->bindParam(":email", $post['email'], PDO::PARAM_STR);
    $rq->bindParam(":token", $token, PDO::PARAM_STR);
    $rq->execute();
    return $this->PDO->lastInsertId();
  }

  public function registraNuovoUtente($post)
  {
    /* CONTROLLI
     * [OK] username non sia già presente e abbia solo lettere e numeri da 8 a 12 caratteri
     * [OK] password che abbia solo lettere, numeri ed alcuni caratteri speciali
     * [OK] password e conderma password devono coincidere
     * [OK] email passata valida
     * [] presenza nome
     */

    //TOGLIAMO EVENTUALI SPAZI CHE CI POSSONO ESSERE PRIMA O DOPO LA DIGITAZIONE
    foreach ($post as $key => $value) {
      $post[$key] = trim($value);
    }

    try {
      //CONTROLLIAMO SE LO USERNAME DATO E' GIA' PRESENTE NEL DATABASE
      if ($this->usernameExists($post['uname'])) {
        return "L'username indicata è già presente";
      }

      $this->checkmoduli($post);

      //CRIPTIAMO LA PASSWORD
      $pwd_hash = password_hash($post['pwd'], PASSWORD_DEFAULT);
      $token = bin2hex(random_bytes(32));

      //INSERIAMO GLI ELEMENTI NEL DATABASE
      $id = $this->addUser($post, $pwd_hash, $token);

      //INVIO MAIL CON IL LINK DI ATTIVAZIONE
      $queryString = ['id' => $id, 'token' => $token];
      $linkAttivazione = "http://localhost:8888/autenticazionephp1.1/auth_system/attivazione.php?" .
        http_build_query($queryString);
      $this->inviaEmailAttivazione($post['email'], $linkAttivazione);
    } catch (PDOException $e) {
      return "Sembra esserci un problema, Riprova tra alcuni minuti";
    } catch (Exception $e) {
      return $e->getMessage();
    }

    return "Sei stato correttamente registrato";
  }

  //VIENE INVOCATO DA REGISTRANUOVOUTENTE
  public function inviaEmailAttivazione($toEmail, $linkAttivazione)
  {
    $mail = &$this->mail;
    $mail->IsSMTP();
    $mail->SMTPAuth = TRUE;
    $mail->SMTPsecure = $mail::ENCRYPTION_SMTPS;
    $mail->Host = "smtp.gmail.com";
    $mail->Port = 465;
    $mail->IsHTML(true);
    $mail->Username = "";
    $mail->Password = "";
    $mail->SetFrom("", "");
    $mail->AddAddress($toEmail);
    $mail->Subject = "Attivazione account";
    $mail->Body = "<h3>E' necessario confermare la registrazione</h3>".
    "<p>Clicca al seguente link: <a href='$linkAttivazione'>conferma registrazione</a></p>";
    if(!$mail->send()){
      throw new Exception($mail->ErrorInfo);
    }
    return TRUE;
  }

  public function login(string $username, string $password)
  {
    try {

      // controllo corrispondenza username e password
      $q = "SELECT * FROM Utenti WHERE username = :username";
      $rq = $this->PDO->prepare($q);
      $rq->bindParam(":username", $username, PDO::PARAM_STR);
      $rq->execute();
      if ($rq->rowCount() == 0) {
        throw new Exception("I dati forniti non sono validi per il login");
      }
      $record = $rq->fetch(PDO::FETCH_ASSOC);
      if (!password_verify($password, $record['password'])) {
        throw new Exception("I dati forniti non sono validi per il login");
      }

      //logghiamo l'utente
      $session_id = session_id();
      $user_id = $record['id'];
      $q = "INSERT INTO UtentiLoggati (session_id, user_id) VALUES (:sessionid, :userid)";
      $rq = $this->PDO->prepare($q);
      $rq->bindParam(":sessionid", $session_id, PDO::PARAM_STR);
      $rq->bindParam(":userid", $user_id, PDO::PARAM_INT);
      $rq->execute();

      return true;
    } catch (PDOException $e) {
      echo "Errore login";
    }
  }

  public function logout()
  {
    try {
      $q = "DELETE FROM UtentiLoggati WHERE session_id = :sessionid";
      $rq = $this->PDO->prepare($q);
      $session_id = session_id();
      $rq->bindParam(":sessionid", $session_id, PDO::PARAM_STR);
      $rq->execute();
    } catch (PDOException $e) {
      echo "Errore logout";
    }
    return true;
  }

  public function utenteLoggato()
  {
    // try catch
    $q = "SELECT * FROM UtentiLoggati WHERE session_id = :sessionid";
    $rq = $this->PDO->prepare($q);
    $session_id = session_id();
    $rq->bindParam("sessionid", $session_id, PDO::PARAM_STR);
    $rq->execute();
    if ($rq->rowCount() == 0) {
      return false;
    } else {
      return true;
    }
  }
}
