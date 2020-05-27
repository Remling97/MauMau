<?php session_start();

$timeToKick = 15;

/*if((time() - $_SESSION["last_request"]) > $timeToKick && isset($_SESSION["room"])){
  unset($_SESSION["room"]);
}*/

if(isset($_SESSION["room"])){
  if(!file_exists("Api/Files/Gamerooms/" . $_SESSION["room"] . ".json")){
    unset($_SESSION["room"]);
  }
}

if(!isset($_SESSION["room"])){
  header('Location: rooms.php');
  exit();
}
?>

<head>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
  <script src="/scripts/gamelogic.js"></script>

  <link rel="stylesheet" type="text/css" href="/style/main.css">
</head>

<body>

  <input type="button" value="Raum verlassen" id="leave" />

  <div class="buttons">
    <input type="button" value="Spiel starten" id="start" />
    <input type="button" value="Karte ziehen" id="draw" />
    <input type="button" value="Runde überspringen" id="skip" /><br />
    <label class="current">Am Zug: </label><br />
    <label class="owner">Raum Admin: </label>
  </div>



  <div class="playfield">
    <div class="top">
      <div class="top_inner">

      </div>
    </div>
    <div class="mid">
      <div class="center_card">
        <img src="/images/cards/no_card.jfif" class="card">
        <div class="color_change"></div>
      </div>
    </div>
    <div class="bot">
      <div class="my_cards"></div>
    </div>
  </div>

  <div class="select_color" data-id="-1">
    <div class="select_color_inner">
      <div class="close">X</div>
      <div class="title">Bitte wähle eine Farbe aus:</div>
      <div class="colors">
        <div data-id="0" class="selected_color herz" ></div>
        <div data-id="1" class="selected_color laub" ></div>
        <div data-id="2" class="selected_color eichel" ></div>
        <div data-id="3" class="selected_color schell" ></div>
      </div>
    </div>
  </div>

  <div class="winner">
    <div class="winner_inner">
      <div class="winnter_title">Es gibt einen Gewinner!</div>
      <div class="winner_text">
        <span>Herzlichen Glückwunsch: </span>
        <span class="winner_name">Remling</span>
      </div>
      <div clasS="wait">Bitte warten bis der Raum Admin eine neue Runde gestartet hat</div>
      <div class="winner_button">
        <div class="leave">Raum verlassen</div>
      </div>
    </div>
  </div>

</body>
