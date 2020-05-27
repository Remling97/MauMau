<?php session_start();

if(isset($_SESSION["playerName"])){
  header('Location: rooms.php');
  exit();
}


?>

<head>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
  <script src="/scripts/main.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.97.5/js/materialize.min.js"></script>

  <link rel="stylesheet" type="text/css" href="/style/main.css">
  <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.97.5/css/materialize.min.css">
  <link rel="stylesheet" type="text/css" href="/style/login-register.css">

</head>

<body>
  <div class="container_outer">
    <div class="container white z-depth-2">
      <ul class="tabs teal">
        <li class="tab col s3"><a class="white-text active" href="#login">Anmelden</a></li>
        <li class="tab col s3"><a class="white-text" href="#register">Registrieren</a></li>
      </ul>
      <div id="login" class="col s12">
        <form class="col s12">
          <div class="form-container">
            <h3 class="teal-text">Anmelden</h3>
            <div class="row">
              <div class="input-field col s12">
                <input id="log_user" type="text" class="validate">
                <label for="log_user">Spielername</label>
              </div>
            </div>
            <div class="row">
              <div class="input-field col s12">
                <input id="log_password" type="password" class="validate">
                <label for="log_password">Passwort</label>
              </div>
            </div>
            <br>
            <center>
              <button class="btn waves-effect waves-light teal" type="submit" name="action">Einloggen</button>
            </center>
          </div>
        </form>
      </div>
      <div id="register" class="col s12">
        <form class="col s12">
          <div class="form-container">
            <h3 class="teal-text">Registrieren</h3>
            <div class="row">
              <div class="input-field col s12">
                <input id="reg_user" type="text" class="validate">
                <label for="reg_user">Spielername</label>
              </div>
            </div>
            <div class="row">
              <div class="input-field col s12">
                <input id="reg_email" type="email" class="validate">
                <label for="reg_email">Email</label>
              </div>
            </div>
            <div class="row">
              <div class="input-field col s12">
                <input id="reg_password" type="password" class="validate">
                <label for="reg_password">Passwort</label>
              </div>
            </div>
            <center>
              <button class="btn waves-effect waves-light teal" type="submit" name="action" id="login">Einloggen</button>
            </center>
          </div>
        </form>
      </div>
    </div>
  </div>
</body>
