var rooms = [];

$(function() {

  $("#create_room").click(function() {
    var name = $("#cr_name").val();
    var max = $("#cr_max").val();
    var password = $("#cr_password").val();

    $.post("/Api/Set/roomfunctions.php", {
      type: "create",
      name: name,
      max: max,
      password: password
    }, function(result) {
      location.reload();
    });
  });

  $("body").on("click", ".room_connect_button", function() {
    var hash = $(this).attr("data-hash");
    var password = "";

    $.post("/Api/Set/roomfunctions.php", {
      type: "join",
      hash: hash,
      password: password,
    }, function(result) {
      //console.log(result);
      location.reload();
    });
  });

  $("#logout").click(function() {

    $.post("/Api/Set/userfunctions.php", {
      type: "logout"
    }, function(result) {
      if (result == "true")
        location.reload();
    });
  });


  /*$("#get_updates").click(function() {
    $.post("/Api/Get/roomfunctions.php", {
      type: "getRoomsUpdates",
      rooms: JSON.stringify(rooms)
    }, function(result) {
      console.log($.parseJSON(result));
      console.log(result);
    });
  });*/


  getAllRooms();
});

function str_replace_all(string, str_find, str_replace) {
  try {
    return string.replace(new RegExp(str_find, "gi"), str_replace);
  } catch (ex) {
    return string;
  }
}

function getAllRooms() {
  $.post("/Api/Get/roomfunctions.php", {
    type: "getRooms",
  }, function(result) {
    console.log(result);
    var allRooms = $.parseJSON(result);
    for (var key in allRooms) {
      var arr = {
        hash: allRooms[key]["hash"],
        version: allRooms[key]["version"]
      };
      rooms.push(arr);
    }
    jQuery.get('/templates/room_overview.html', function(data) {
      for (var key in allRooms) {
        var template_copy = data;
        template_copy = str_replace_all(template_copy, "{hash}", allRooms[key]["hash"]);
        template_copy = str_replace_all(template_copy, "{title}", allRooms[key]["roomName"]);

        var player = "";
        for (var key2 in allRooms[key]["player"]) {
          player += allRooms[key]["player"][key2] + "<br>";
        }

        template_copy = str_replace_all(template_copy, "{player}", player);

        $(".rooms").append(template_copy);
      }
    });
    console.log(allRooms);
  });
}
