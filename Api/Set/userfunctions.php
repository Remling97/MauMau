<?php

session_start();

if(isset($_POST["type"])){
  $type = $_POST["type"];
  if($type == "register"){
    $username = $_POST["username"];
    $password = $_POST["password"];
    $email = $_POST["email"];

    echo register_user($username, $password, $email);
  }else if($type == "login"){
    $username = $_POST["username"];
    $password = $_POST["password"];

    echo login_user($username, $password);
  }else if($type == "logout"){
    unset($_SESSION["playerName"]);
    echo "true";
  }
}


function register_user($username, $password, $email){

  $servername = "remling97.lima-db.de";
  $usernameDB = "USER374236";
  $passwordDB = "7CCFEDAC52B96C55";
  $dbname = "db_374236_14";

  if($username != "" && $password != "" && $email != ""){
    $salt = "7FC5388BE6ABADA4D24F5E9F11D9D";
    $salt2 = "49F6629E9A72414D6377F2B72EC5E";

    $password = hash("sha256", $salt . $password . $salt2 . $password . $salt);


    // Create connection
    $conn = new mysqli($servername, $usernameDB, $passwordDB, $dbname);
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT id FROM user WHERE username = '" . $username . "'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
      return "error_2"; //Username already Exists
    } else {
      $sql = "INSERT INTO user (username, password, email) VALUES ('" . $username . "', '" . $password . "', '" . $email . "')";
      if ($conn->query($sql) === TRUE) {
        login_user($username, $password, true);
        return "true"; //User Created
      } else {
        return "error_3"; //Could not create User
      }
    }

  }else{
    return "error_1"; //Not all fields are Set
  }
}

function login_user($username, $password, $just_created = false){

  $servername = "localhost";
  $usernameDB = "USER374236";
  $passwordDB = "7CCFEDAC52B96C55";
  $dbname = "db_374236_14";

  if($just_created){
    $_SESSION["playerName"] = $username;
  }else{
    $salt = "7FC5388BE6ABADA4D24F5E9F11D9D";
    $salt2 = "49F6629E9A72414D6377F2B72EC5E";

    $password = hash("sha256", $salt . $password . $salt2 . $password . $salt);

    // Create connection
    $conn = new mysqli($servername, $usernameDB, $passwordDB, $dbname);
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT id FROM user WHERE username = '" . $username . "' AND password = '" . $password . "'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
      $_SESSION["playerName"] = $username;
      return "true";
    } else {
      return "error_1"; //No úser with combination found
    }
  }

  return "false";
}


?>
