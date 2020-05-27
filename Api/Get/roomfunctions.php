<?php

session_start();

if(isset($_POST["type"])){
  $type = $_POST["type"];

  if($type == "getRooms"){
    echo getRooms();
  }
  else if($type == "getRoomsUpdates"){
    $rooms = $_POST["rooms"];
    echo getRoomsUpdates($rooms);
  }
}


function getRooms(){
  return file_get_contents("../Files/Gamerooms/rooms.json");
}

function getRoomsUpdates($oldRooms){
  $updates = array();
  $newRooms = array();
  $deletedRooms = array();
  $updatedRooms = array();

  $oldRooms = json_decode($oldRooms, true);
  $rooms = json_decode(file_get_contents("../Files/Gamerooms/rooms.json"), true);


  for ($i=0; $i < count($oldRooms); $i++) {
    $value = $oldRooms[$i]["hash"];

    if($rooms[$value] == false){
      $deletedRooms[] = $oldRooms[$i];
    }
    else if($oldRooms[$i]["version"] != $rooms[$oldRooms[$i]["hash"]]["version"]){
      $updatedRooms[] = $rooms[$value];
      unset($rooms[$value]);
    }
    else{
      unset($rooms[$value]);
    }
  }

  $newRooms = $rooms;

  $updates["updatedRooms"] = $updatedRooms;
  $updates["newRooms"] = $newRooms;
  $updates["deletedRooms"] = $deletedRooms;

  return json_encode($updates);
}
?>
