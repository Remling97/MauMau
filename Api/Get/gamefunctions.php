<?php

session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$timeToKick = 15;

/*if((time() - $_SESSION["last_request"]) > $timeToKick && isset($_SESSION["room"])){
  unset($_SESSION["room"]);
  return null;
}*/

if(isset($_POST["type"]) && isset($_SESSION["room"])){
  $type = $_POST["type"];

  if($type == "get_all"){
    $firstcall = $_POST["firstcall"];
    echo get_all_information($firstcall);
  }
}

function get_all_information($firstcall){
  $roomHash = $_SESSION["room"];
  $playerName = $_SESSION["playerName"];

  $gameData = "";
  $error = true;

  for ($i=0; $i < 10; $i++) {
    $gameData = json_decode(file_get_contents("../Files/Gamerooms/" . $roomHash . ".json"), true);
    if($gameData != null && $gameData != "" && isset($gameData)){
      $error = false;
      break;
    }
    usleep(0.05 * 1000000);
  }

  if($error)
    return "error_reading";

  for ($i=0; $i < count($gameData["game"]["player"]); $i++) {
    if($gameData["game"]["player"][$i]["name"] != $playerName){
      unset($gameData["game"]["player"][$i]["can_draw"]);
      unset($gameData["game"]["player"][$i]["last_request"]);

      $cards = count($gameData["game"]["player"][$i]["cards"]);

      unset($gameData["game"]["player"][$i]["cards"]);
      $gameData["game"]["player"][$i]["cards"] = $cards;
      $gameData["game"]["player"][$i]["is_me"] = false;
    }else{
      $gameData["game"]["player"][$i]["is_me"] = true;
      if($firstcall == "true"){

        $gameData["game"]["player"][$i]["cards_received"] = count($gameData["game"]["player"][$i]["cards"]);
      }
      //check_for_inactive_players($i);
    }
  }

  unset($gameData["game"]["cards_left"]);
  unset($gameData["game"]["cards_played"]);
  unset($gameData["game"]["last_round_first_player"]);
  unset($gameData["game"]["decks"]);
  unset($gameData["gameroom"]["password"]);

  return json_encode($gameData);
}


function check_for_inactive_players(){
  $roomHash = $_SESSION["room"];

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



  $timeToKick = 15;

  for ($i=0; $i < count($gameData["game"]["player"]); $i++) {
    if((time() - $gameData["game"]["player"][$i]["last_request"]) > $timeToKick){
      $gameData = kick_player($gameData, $gameData["game"]["player"][$i]["name"]);
    }
  }

  return $gameData;
}

function kick_player($gameData, $playerName){

  $roomHash = $_SESSION["room"];

  $deleteRoom = false;
  if(count($gameData["game"]["player"]) == 1){
    $deleteRoom = true;
  }

  if($deleteRoom){
    unlink("../Files/Gamerooms/" . $roomHash . ".json");

    $rooms = json_decode(file_get_contents("../Files/Gamerooms/rooms.json"), true);
    unset($rooms[$roomHash]);
    $roomsJson = json_encode($rooms, JSON_PRETTY_PRINT);
    $roomsFile = fopen("../Files/Gamerooms/rooms.json", "w");
    fwrite($roomsFile, $roomsJson);
    fclose($roomsFile);
  }else{

    $key = getUserArrayId($gameData["game"]["player"], $playerName);
    unset($gameData["game"]["player"][$key]);
    $gameData["gameroom"]["version"] = $gameData["gameroom"]["version"] + 1;
    $gameData["game"]["player"] = array_values($gameData["game"]["player"]);

    if($playerName == $gameData["gameroom"]["owner"])
      $gameData["gameroom"]["owner"] = $gameData["game"]["player"][strval(rand(0, count($gameData["game"]["player"]) - 1))]["name"];

    if($key == count($gameData["game"]["player"]))
      $gameData["game"]["current_player"] = $key - 1;

    if($key == $gameData["game"]["last_round_first_player"])
      $gameData["game"]["last_round_first_player"] = $key - 1;

    if(count($gameData["game"]["player"]) == 1){
      $gameData = terminateRound($gameData);
    }

    $gameDataJson = json_encode($gameData, JSON_PRETTY_PRINT);
    $roomFile = fopen("../Files/Gamerooms/" . $roomHash . ".json", "w");
    fwrite($roomFile, $gameDataJson);
    fclose($roomFile);

    $rooms = json_decode(file_get_contents("../Files/Gamerooms/rooms.json"), true);
    $key = array_search($playerName, $rooms[$roomHash]["player"]);
    unset($rooms[$roomHash]["player"][$key]);
    $rooms[$roomHash]["version"] = $rooms[$roomHash]["version"] + 1;
    $roomsJson = json_encode($rooms, JSON_PRETTY_PRINT);
    $roomsFile = fopen("../Files/Gamerooms/rooms.json", "w");
    fwrite($roomsFile, $roomsJson);
    fclose($roomsFile);
  }

  return $gameData;
}

function terminateRound($gameData){
  $gameData["game"]["consecutive_ober"] = 0;
  $gameData["game"]["cards_played"] = array();
  $gameData["game"]["round_started"] = false;
  $gameData["game"]["current_card_color_change"] = -1;

  for ($i=0; $i < count($gameData["game"]["player"]); $i++) {
    $gameData["game"]["player"][$i]["cards"] = array();
    $gameData["game"]["player"][$i]["cards_received"] = -1;
  }

  return $gameData;
}

function getUserArrayId($array, $user){
  for ($i=0; $i < count($array); $i++) {
    if($array[$i]["name"] == $user)
      return $i;
  }
  return -1;
}
?>
