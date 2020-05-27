<?php

session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



if(isset($_POST["type"])){
  $type = $_POST["type"];

  if($type == "create" && !isset($_SESSION["room"])){
    $name = $_POST["name"];
    $maxPlayer = $_POST["max"];
    $password = (isset($_POST["password"])) ? $_POST["password"] : "";
    echo createRoom($name, $maxPlayer, $password);
  }
  else if($type == "join" && !isset($_SESSION["room"])){
    $roomHash = $_POST["hash"];
    $roomPW = $_POST["password"];
    echo joinRoom($roomHash, $roomPW);
  }
  else if($type == "leave" && isset($_SESSION["room"])){
    echo leaveRoom();
  }
}




function createRoom($name, $maxPlayer, $password){
  //Create Room Hash
  $roomHash = hash('sha256', hash("sha256", $name) . bin2hex(random_bytes(20)));

  //Create Room Data
  $roomData["gameroom"] = [
      "hash" => $roomHash,
      "roomName" => $name,
      "maxPlayer" => $maxPlayer,
      "hasPassword" => ($password != ""),
      "password" => ($password != "") ? hash("sha256", $password) : "",
      "owner" => $_SESSION["playerName"],
      "rules" => array(),
      "version" => 0,
  ];

  $roomData["game"] = [
    "decks" => 1,
    "cards_left" => array(),
    "cards_played" => array(),
    "round_started" => false,
    "last_round_first_player" => -1,
    "current_player" => -1,
    "is_first_card" => false,
    "current_card" => 0,
    "current_card_color_change" => -1,
    "game_state" => 0,
    "consecutive_ober" => 0,
    "games_played" => 0,
    "someone_won" => false,
    "winner" => "",
    "player" => array(
      [
        "name" => $_SESSION["playerName"],
        "cards" => array(),
        "wins" => 0,
        "last_request" => time(),
        "can_draw" => true,
        "cards_received" => 0,
      ],
    ),
    "waitlist" => array(),
  ];

  //Create Room Data for Rooms.json
  $simpleRoomData = [
    "hash" => $roomHash,
    "roomName" => $name,
    "hasPassword" => ($password != ""),
    "player" => array($_SESSION["playerName"]),
  ];

  //Create Json string from Array
  $roomDataJson = json_encode($roomData, JSON_PRETTY_PRINT);

  //Create Room File and Write Json to it
  $roomFile = fopen("../Files/Gamerooms/" . $roomHash . ".json", "w");
  fwrite($roomFile, $roomDataJson);
  fclose($roomFile);

  //Get all Rooms and Append new Room to it
  $start = microtime(true);

  $rooms = json_decode(file_get_contents("../Files/Gamerooms/rooms.json"), true);
  $rooms[$roomHash] = $simpleRoomData;
  $roomsJson = json_encode($rooms, JSON_PRETTY_PRINT);
  $roomsFile = fopen("../Files/Gamerooms/rooms.json", "w");
  fwrite($roomsFile, $roomsJson);
  fclose($roomsFile);

  //Set Room in Session
  $_SESSION["room"] = $roomHash;
  $_SESSION["last_request"] = time();

  //return microtime(true) - $start;
  return $roomHash;
}


function joinRoom($roomHash, $password){
  $roomData = "";
  $error = true;

  for ($i=0; $i < 10; $i++) {
    $roomData = json_decode(file_get_contents("../Files/Gamerooms/" . $roomHash . ".json"), true);
    if($roomData != null && $roomData != "" || isset($roomData)){
      $error = false;
      break;
    }
    usleep(0.05 * 1000000);
  }

  if($error)
    return "error_reading";
  $password = hash("sha256", $password);

  if($roomData["gameroom"]["maxPlayer"] <= (count($roomData["game"]["player"]) + count($roomData["game"]["waitlist"]))){
    return "Das Spiel ist bereits voll";
  }
  else if($roomData["gameroom"]["hasPassword"] && $roomData["gameroom"]["password"] != $password){
    return "Falsches Passwort";
  }
  else{
    if($roomData["game"]["round_started"]){
      $roomData["game"]["waitlist"][] = array(
        "name" => $_SESSION["playerName"],
        "last_request" => time(),
      );
    }else{
      $roomData["game"]["player"][] = array(
        "name" => $_SESSION["playerName"],
        "cards" => array(),
        "wins" => 0,
        "last_request" => time(),
        "can_draw" => true,
        "cards_received" => 0,
      );
    }

    $roomData["gameroom"]["version"] = $roomData["gameroom"]["version"] + 1;

    $roomDataJson = json_encode($roomData, JSON_PRETTY_PRINT);

    $roomFile = fopen("../Files/Gamerooms/" . $roomHash . ".json", "w");
    fwrite($roomFile, $roomDataJson);
    fclose($roomFile);

    $rooms = json_decode(file_get_contents("../Files/Gamerooms/rooms.json"), true);
    $rooms[$roomHash]["player"][] = $_SESSION["playerName"];
    $rooms[$roomHash]["version"] = $rooms[$roomHash]["version"] + 1;
    $roomsJson = json_encode($rooms, JSON_PRETTY_PRINT);
    $roomsFile = fopen("../Files/Gamerooms/rooms.json", "w");
    fwrite($roomsFile, $roomsJson);
    fclose($roomsFile);

    $_SESSION["room"] = $roomHash;
    $_SESSION["last_request"] = time();
  }

  return "Spiel beigetreten";
}


function leaveRoom(){
  $roomHash = $_SESSION["room"];

  $roomData = "";
  $error = true;

  for ($i=0; $i < 10; $i++) {
    $roomData = json_decode(file_get_contents("../Files/Gamerooms/" . $roomHash . ".json"), true);
    if($roomData != null && $roomData != "" || isset($roomData)){
      $error = false;
      break;
    }
    usleep(0.05 * 1000000);
  }

  if($error)
    return "error_reading";

  $on_waitlist = (getUserArrayId($roomData["game"]["waitlist"], $_SESSION["playerName"]) == -1) ? false : true;

  $deleteRoom = false;
  if(count($roomData["game"]["player"]) == 1 && !$on_waitlist){
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

    unset($_SESSION["room"]);

    return "Spiel verlassen und Raum gelöscht, weil keine Spieler mehr vorhanden";
  }else if(!$on_waitlist){

    $key = getUserArrayId($roomData["game"]["player"], $_SESSION["playerName"]);
    unset($roomData["game"]["player"][$key]);
    $roomData["gameroom"]["version"] = $roomData["gameroom"]["version"] + 1;
    $roomData["game"]["player"] = array_values($roomData["game"]["player"]);

    if($_SESSION["playerName"] == $roomData["gameroom"]["owner"])
      $roomData["gameroom"]["owner"] = $roomData["game"]["player"][strval(rand(0, count($roomData["game"]["player"]) - 1))]["name"];

    if($key == count($roomData["game"]["player"]))
      $roomData["game"]["current_player"] = $key - 1;

    if($key == $roomData["game"]["last_round_first_player"])
      $roomData["game"]["last_round_first_player"] = $key - 1;

    if(count($roomData["game"]["player"]) == 1){
      $roomData = terminateRound($roomData);
    }

    $roomDataJson = json_encode($roomData, JSON_PRETTY_PRINT);
    $roomFile = fopen("../Files/Gamerooms/" . $roomHash . ".json", "w");
    fwrite($roomFile, $roomDataJson);
    fclose($roomFile);

    $rooms = json_decode(file_get_contents("../Files/Gamerooms/rooms.json"), true);
    $key = array_search($_SESSION["playerName"], $rooms[$roomHash]["player"]);
    unset($rooms[$roomHash]["player"][$key]);
    $rooms[$roomHash]["version"] = $rooms[$roomHash]["version"] + 1;
    $roomsJson = json_encode($rooms, JSON_PRETTY_PRINT);
    $roomsFile = fopen("../Files/Gamerooms/rooms.json", "w");
    fwrite($roomsFile, $roomsJson);
    fclose($roomsFile);

    unset($_SESSION["room"]);

    return "Spiel verlassen";
  }else{
    $key = getUserArrayId($roomData["game"]["waitlist"], $_SESSION["playerName"]);
    unset($roomData["game"]["waitlist"][$key]);
    $roomData["game"]["waitlist"] = array_values($roomData["game"]["waitlist"]);
    $roomData["gameroom"]["version"] = $roomData["gameroom"]["version"] + 1;
    $roomDataJson = json_encode($roomData, JSON_PRETTY_PRINT);
    $roomFile = fopen("../Files/Gamerooms/" . $roomHash . ".json", "w");
    fwrite($roomFile, $roomDataJson);
    fclose($roomFile);

    $rooms = json_decode(file_get_contents("../Files/Gamerooms/rooms.json"), true);
    $key = array_search($_SESSION["playerName"], $rooms[$roomHash]["player"]);
    unset($rooms[$roomHash]["player"][$key]);
    $rooms[$roomHash]["version"] = $rooms[$roomHash]["version"] + 1;
    $roomsJson = json_encode($rooms, JSON_PRETTY_PRINT);
    $roomsFile = fopen("../Files/Gamerooms/rooms.json", "w");
    fwrite($roomsFile, $roomsJson);
    fclose($roomsFile);

    unset($_SESSION["room"]);

    return "Waitlist verlassen";
  }
}

function terminateRound($roomData){
  $roomData["game"]["consecutive_ober"] = 0;
  $roomData["game"]["cards_played"] = array();
  $roomData["game"]["round_started"] = false;
  $roomData["game"]["current_card_color_change"] = -1;

  for ($i=0; $i < count($roomData["game"]["player"]); $i++) {
    $roomData["game"]["player"][$i]["cards"] = array();
    $roomData["game"]["player"][$i]["cards_received"] = -1;
  }

  return $roomData;
}

function getUserArrayId($array, $user){
  for ($i=0; $i < count($array); $i++) {
    if($array[$i]["name"] == $user)
      return $i;
  }
  return -1;
}
?>
