<?php session_start();

$timeToKick = 15;
/*if((time() - $_SESSION["last_request"]) > $timeToKick && isset($_SESSION["room"])){
  unset($_SESSION["room"]);
}*/

if(isset($_SESSION["room"])){
  header('Location: game.php');
  exit();
}else if(!isset($_SESSION["playerName"])){
  header("Location: index.php");
  exit();
}
?>

<head>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
  <script src="/scripts/room_logic.js"></script>

  <link rel="stylesheet" type="text/css" href="/style/main.css">

</head>

<body>

  <div class="box">
    <input type="text" id="cr_name" placeholder="name"/>
    <input type="text" id="cr_max" placeholder="max" />
    <input type="text" id="cr_password" placeholder="password"/>
    <input type="button" value="create" id="create_room">
  </div>
  <br />

  <input type="button" value="Ausloggen" id="logout" />

  <br />

  <div class="rooms">

  </div>
</body>
