$(function(){
  $("#login").click(function(e){
    e.preventDefault();
    var username = $("#log_user").val();
    var password = $("#log_password").val();

    $.post("/Api/Set/userfunctions.php", {
      type: "login",
      username: username,
      password: password
    }, function(result) {
      if(result == "true")
        location.reload();
    });
  });

  $("#register").click(function(e){
    e.preventDefault();
    var username = $("#reg_user").val();
    var password = $("#reg_password").val();
    var email = $("#reg_email").val();

    $.post("/Api/Set/userfunctions.php", {
      type: "register",
      username: username,
      password: password,
      email: email
    }, function(result) {
      if(result == "true")
        location.reload();
    });
  });
});
