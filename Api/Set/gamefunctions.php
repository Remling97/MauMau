<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// TODO: Join game
// TODO: Play Card

if(isset($_POST["type"]) && isset($_SESSION["room"])){
  $type = $_POST["type"];

  if($type == "start_round"){
    echo start_round();
  }else if($type == "play_card"){
    $card = $_POST["card"];
    $colorChange = (isset($_POST["color_change"])) ? $_POST["color_change"] : "";
    echo play_card($card, $colorChange);
  }else if($type == "draw_card"){
    echo draw_card();
  }else if($type == "skip_round"){
    echo skip_round();
  }
  else{
    echo "error";
  }
}

function skip_round(){
  $roomHash = $_SESSION["room"];
  $playerName = $_SESSION["playerName"];

  $gameData = "";
  $error = true;

  for ($i=0; $i < 10; $i++) {
    $gameData = json_decode(file_get_contents("../Files/Gamerooms/" . $roomHash . ".json"), true);
    if($gameData != null && $gameData != "" || isset($gameData)){
      $error = false;
      break;
    }
    usleep(0.05 * 1000000);
  }

  $currentPlayer = $gameData["game"]["current_player"];

  if($error)
    return "error_reading";

  //Check if game is in Progress
  if(!$gameData["game"]["round_started"]){
    return "error_1"; //Game is not in Progress
  }

  $key = getUserArrayId($gameData["game"]["player"], $_SESSION["playerName"]);
  if($gameData["game"]["current_player"] != $key){
    return "error_2"; //Its not his Turn
  }

  if($gameData["game"]["player"][$currentPlayer]["can_draw"]){
    return "error_3";
  }

  $gameData["game"]["current_player"] = (($gameData["game"]["current_player"] + 1) >= count($gameData["game"]["player"])) ? 0 : $gameData["game"]["current_player"] + 1;
  $gameData["gameroom"]["version"] = $gameData["gameroom"]["version"] + 1;
  $gameData["game"]["player"][$key]["can_draw"] = true;

  //Create Json string from Array
  $gameDataJson = json_encode($gameData, JSON_PRETTY_PRINT);

  //Create Room File and Write Json to it
  $roomFile = fopen("../Files/Gamerooms/" . $roomHash . ".json", "w");
  fwrite($roomFile, $gameDataJson);
  fclose($roomFile);

  return "true";
}

function draw_card(){
  $roomHash = $_SESSION["room"];
  $playerName = $_SESSION["playerName"];

  $gameData = "";
  $error = true;

  for ($i=0; $i < 10; $i++) {
    $gameData = json_decode(file_get_contents("../Files/Gamerooms/" . $roomHash . ".json"), true);
    if($gameData != null && $gameData != "" || isset($gameData)){
      $error = false;
      break;
    }
    usleep(0.05 * 1000000);
  }

  if($error)
    return "error_reading";

  //Check if game is in Progress
  if(!$gameData["game"]["round_started"]){
    return "error_1"; //Game is not in Progress
  }

  $key = getUserArrayId($gameData["game"]["player"], $_SESSION["playerName"]);
  if($gameData["game"]["current_player"] != $key){
    return "error_2"; //Its not his Turn
  }

  $currentPlayer = $gameData["game"]["current_player"];

  if($gameData["game"]["player"][$currentPlayer]["can_draw"]){

    $gameData["game"]["player"][$currentPlayer]["can_draw"] = false;

    if(count($gameData["game"]["cards_left"]) <= 1){
      $gameData["game"]["cards_left"] = $gameData["game"]["cards_played"];
      $gameData["game"]["cards_played"] = array();
      $gameData["game"]["cards_played"][] = $gameData["game"]["cards_played"][0];
      shuffle($gameData["game"]["cards_left"]);
    }

    $gameData["game"]["player"][$currentPlayer]["cards"][] = array_pop($gameData["game"]["cards_left"]);

    $gameData["gameroom"]["version"] = $gameData["gameroom"]["version"] + 1;

    //Create Json string from Array
    $gameDataJson = json_encode($gameData, JSON_PRETTY_PRINT);

    //Create Room File and Write Json to it
    $roomFile = fopen("../Files/Gamerooms/" . $roomHash . ".json", "w");
    fwrite($roomFile, $gameDataJson);
    fclose($roomFile);
  }

  return "true";
}

function getUserArrayId($array, $user){
  for ($i=0; $i < count($array); $i++) {
    if($array[$i]["name"] == $user)
      return $i;
  }
}

function play_card($card, $colorChange){
  $roomHash = $_SESSION["room"];
  $playerName = $_SESSION["playerName"];

  $gameData = "";
  $error = true;

  for ($i=0; $i < 10; $i++) {
    $gameData = json_decode(file_get_contents("../Files/Gamerooms/" . $roomHash . ".json"), true);
    if($gameData != null && $gameData != "" || isset($gameData)){
      $error = false;
      break;
    }
    usleep(0.05 * 1000000);
  }

  if($error)
    return "error_reading";

  //Check if game is in Progress
  if(!$gameData["game"]["round_started"]){
    return "error_1"; //Game is not in Progress
  }

  $key = getUserArrayId($gameData["game"]["player"], $_SESSION["playerName"]);
  if($gameData["game"]["current_player"] != $key){
    return "error_2"; //Its not his Turn
  }

  $cards = json_decode(file_get_contents("../Files/cards.json"), true);

  $playedCard = $cards[$card];
  $currCard = $cards[$gameData["game"]["current_card"]];

  if($gameData["game"]["current_card_color_change"] > -1){
    $currCard["color"] = $gameData["game"]["current_card_color_change"];
    $gameData["game"]["current_card_color_change"] = -1;
  }

  if($currCard["type"] == 5 && $playedCard["type"] != 5 && $gameData["game"]["consecutive_ober"] > 0){
    return "error_3";
  }

  if($currCard["type"] == "2" && $gameData["game"]["is_first_card"]){}
  else if($playedCard["color"] != $currCard["color"] && $playedCard["type"] != $currCard["type"] && $playedCard["type"] != "2"){
    return "error_4"; //Played an Invalid Card
  }


  if($playedCard["type"] == "2"){
    $gameData["game"]["current_card_color_change"] = $colorChange;
    $gameData["game"]["current_player"] = (($gameData["game"]["current_player"] + 1) >= count($gameData["game"]["player"])) ? 0 : $gameData["game"]["current_player"] + 1;
  }

  else if($playedCard["type"] == "7"){
    $currPlayer = $gameData["game"]["current_player"];
    if($currPlayer == count($gameData["game"]["player"]) -1)
      $gameData["game"]["current_player"] = 1;
    else if($currPlayer == count($gameData["game"]["player"]) -2)
      $gameData["game"]["current_player"] = 0;
    else
      $gameData["game"]["current_player"] = $currPlayer + 2;
  }
  else if($playedCard["type"] == "5"){
    $gameData["game"]["current_player"] = (($gameData["game"]["current_player"] + 1) >= count($gameData["game"]["player"])) ? 0 : $gameData["game"]["current_player"] + 1;

    $gameData["game"]["consecutive_ober"] = $gameData["game"]["consecutive_ober"] + 1;

    $nextPlayerCards = $gameData["game"]["player"][$gameData["game"]["current_player"]]["cards"];
    if(!findCardType($nextPlayerCards, $cards, "5")){
      $cardsToPickUp = $gameData["game"]["consecutive_ober"] * 2;

      if(count($gameData["game"]["cards_left"]) <= $cardsToPickUp){
        $gameData["game"]["cards_left"] = $gameData["game"]["cards_played"];
        $gameData["game"]["cards_played"] = array();
        $gameData["game"]["cards_played"][] = $gameData["game"]["cards_played"][0];
        shuffle($gameData["game"]["cards_left"]);
      }

      for ($i=0; $i < $cardsToPickUp; $i++) {
        $gameData["game"]["player"][$gameData["game"]["current_player"]]["cards"][] = array_pop($gameData["game"]["cards_left"]);
      }

      $gameData["game"]["consecutive_ober"] = 0;
    }
  }
  else{
    $gameData["game"]["current_player"] = (($gameData["game"]["current_player"] + 1) >= count($gameData["game"]["player"])) ? 0 : $gameData["game"]["current_player"] + 1;
  }

  $cardKey = array_search($card, $gameData["game"]["player"][$key]["cards"]);

  unset($gameData["game"]["player"][$key]["cards"][$cardKey]);
  $gameData["game"]["player"][$key]["cards"] = array_values($gameData["game"]["player"][$key]["cards"]);

  $gameData["game"]["current_card"] = intval($card);
  $gameData["game"]["cards_played"][] = intval($card);
  $gameData["gameroom"]["version"] += 1;

  if($gameData["game"]["is_first_card"])
    $gameData["game"]["is_first_card"] = false;

  $gameData["game"]["player"][$key]["can_draw"] = true;

  $gameData = check_game_state($gameData, $key);

  //Create Json string from Array
  $gameDataJson = json_encode($gameData, JSON_PRETTY_PRINT);

  //Create Room File and Write Json to it
  $roomFile = fopen("../Files/Gamerooms/" . $roomHash . ".json", "w");
  fwrite($roomFile, $gameDataJson);
  fclose($roomFile);

  return "true";
}


function check_game_state($gameData, $player_key){
  if(count($gameData["game"]["player"][$player_key]["cards"]) > 0){
    return $gameData;
  }

  $gameData["game"]["someone_won"] = true;
  $gameData["game"]["winner"] = $_SESSION["playerName"];
  $gameData["game"]["games_played"] += 1;
  $gameData["game"]["player"][$player_key]["wins"] += 1;
  $gameData["game"]["consecutive_ober"] = 0;
  $gameData["game"]["cards_played"] = array();
  $gameData["game"]["round_started"] = false;
  $gameData["game"]["current_card_color_change"] = -1;
  $gameData["game"]["game_state"] = -1;

  for ($i=0; $i < count($gameData["game"]["player"]); $i++) {
    $gameData["game"]["player"][$i]["cards"] = array();
  }

  return $gameData;
}


function start_round(){
  $roomHash = $_SESSION["room"];
  $playerName = $_SESSION["playerName"];

  $gameData = "";
  $error = true;

  for ($i=0; $i < 10; $i++) {
    $gameData = json_decode(file_get_contents("../Files/Gamerooms/" . $roomHash . ".json"), true);
    if($gameData != null && $gameData != "" || isset($gameData)){
      $error = false;
      break;
    }
    usleep(0.05 * 1000000);
  }

  if($error)
    return "error_reading";

  //Check if user is Owner of Game
  if($gameData["gameroom"]["owner"] !== $playerName){
    return "error_1"; //Player is not Owner of the Game
  }

  //Check if game is Already in Progress
  if($gameData["game"]["round_started"]){
    return "error_2"; //Game is already in Progress
  }

  $gameData = joinPlayersFromWaitlist($gameData);

  //Check if atleast 2 players are in the Game
  if(count($gameData["game"]["player"]) < 2){
    return "error_3"; //Not enough Players
  }

  //Set Game to Started
  $gameData["game"]["round_started"] = true;
  $gameData["game"]["someone_won"] = false;
  $gameData["game"]["winner"] = "";
  $gameData["game"]["is_first_card"] = true;

  //Calculate Decks and Cards for Game
  $decks = ceil(count($gameData["game"]["player"]) / 3);

  $gameData["game"]["decks"] = $decks;

  $gameData = generateCards($gameData);

  $gameData["game"]["current_card"] = array_pop($gameData["game"]["cards_left"]);
  $gameData["game"]["cards_played"][] = $gameData["game"]["current_card"];

  $curPlayer = ($gameData["game"]["last_round_first_player"] == count($gameData["game"]["player"]) - 1) ? "0" : $gameData["game"]["last_round_first_player"] + 1;
  $gameData["game"]["current_player"] = $curPlayer;
  $gameData["game"]["last_round_first_player"] = $curPlayer;

  $gameData["gameroom"]["version"] = $gameData["gameroom"]["version"] + 1;
  $gameData["game"]["game_state"] = 0;


  $cards = json_decode(file_get_contents("../Files/cards.json"), true);

  $playedCard = $cards[$gameData["game"]["current_card"]];

  if($playedCard["type"] == "7"){
    $gameData["game"]["current_player"] = (($gameData["game"]["current_player"] + 1) >= count($gameData["game"]["player"])) ? 0 : $gameData["game"]["current_player"] + 1;
  }
  else if($playedCard["type"] == "5"){

    $nextPlayerCards = $gameData["game"]["player"][$gameData["game"]["current_player"]]["cards"];
    if(!findCardType($nextPlayerCards, $cards, "5")){
      for ($i=0; $i < 2; $i++) {
        $gameData["game"]["player"][$gameData["game"]["current_player"]]["cards"][] = array_pop($gameData["game"]["cards_left"]);
      }
    }else{
      $gameData["game"]["consecutive_ober"] = $gameData["game"]["consecutive_ober"] + 1;
    }
  }

  //Create Json string from Array
  $gameDataJson = json_encode($gameData, JSON_PRETTY_PRINT);

  //Create Room File and Write Json to it
  $roomFile = fopen("../Files/Gamerooms/" . $roomHash . ".json", "w");
  fwrite($roomFile, $gameDataJson);
  fclose($roomFile);

  return "true";
}

function findCardType($array, $cards, $type){
  for ($i=0; $i < count($array); $i++) {
    if($cards[$array[$i]]["type"] == $type)
      return true;
  }
  return false;
}

function generateCards($gameData){
  $cards = $gameData["game"]["decks"] * 33;

  $deck = array();

  $count = 0;
  for ($i=0; $i < $cards; $i++) {
    $deck[] = $count;

    if($count == 32)
      $count = 0;
    else
      $count++;
  }

  shuffle($deck);

  for ($i=0; $i < count($gameData["game"]["player"]); $i++) {
    for ($a=0; $a < 5; $a++) {
      $gameData["game"]["player"][$i]["cards"][] = array_pop($deck);
    }
  }

  $gameData["game"]["cards_left"] = $deck;

  return $gameData;
}

function joinPlayersFromWaitlist($gameData){

  for ($i=0; $i < count($gameData["game"]["waitlist"]); $i++) {
    $gameData["game"]["player"][] = array(
      "name" => $gameData["game"]["waitlist"][$i]["name"],
      "cards" => array(),
      "wins" => 0,
      "last_request" => $gameData["game"]["waitlist"][$i]["last_request"],
      "can_draw" => true,
    );
  }

  $gameData["game"]["waitlist"] = array();

  return $gameData;
}

?>
