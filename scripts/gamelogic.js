var ping_time = 1000;
var version = -1;
var gameStarted = false;
var playedCard = "";
var current_player = -1;
var my_player_id = -1;
var winner = false;
var my_cards = 0;

$(function() {
  $("#leave, .winner_button .leave").click(function() {
    $.post("/Api/Set/roomfunctions.php", {
      type: "leave",
    }, function(result) {
      location.reload();
    });
  });

  $("#start").click(function() {
    $.post("/Api/Set/gamefunctions.php", {
      type: "start_round",
    }, function(result) {
      console.log(result);
    });
  });

  $("#draw").click(function() {
    $.post("/Api/Set/gamefunctions.php", {
      type: "draw_card",
    }, function(result) {
      console.log(result);
    });
  });

  $("#skip").click(function() {
    $.post("/Api/Set/gamefunctions.php", {
      type: "skip_round",
    }, function(result) {
      console.log(result);
    });
  });

  $("body").on("click", ".my_cards .card", function() {
    var id = $(this).attr("data-id");
    playedCard = $(this);
    if (id == "2" || id == "10" || id == "18" || id == "26" || id == "27") {
      $(".select_color").attr("data-id", id).css("display", "flex");
    } else {
      $.post("/Api/Set/gamefunctions.php", {
        type: "play_card",
        card: $(this).attr("data-id"),
      }, function(result) {
        if (result == "true") {
          $(playedCard).remove();
        }
        console.log(result);
      });
    }
  });

  $(".selected_color").click(function() {
    $(".select_color").css("display", "none");
    $.post("/Api/Set/gamefunctions.php", {
      type: "play_card",
      card: $(this).closest(".select_color").attr("data-id"),
      color_change: $(this).attr("data-id"),
    }, function(result) {
      if (result == "true") {
        $(playedCard).remove();
      }
      console.log(result);
    });
  });

  $(".select_color .close").click(function() {
    $(".select_color").css("display", "none");
  });

  window.setInterval(function() {
    drawSceen();
  }, ping_time);

  drawSceen(true);

});


function drawSceen(firstcall = false) {
  $.post("/Api/Get/gamefunctions.php", {
    type: "get_all",
    firstcall: firstcall,
  }, function(result) {
    //console.log(result);
    //console.log($.parseJSON(result));

    var gameData = $.parseJSON(result);

    if (gameData != null && gameData["gameroom"]["version"] != version) {
      version = gameData["gameroom"]["version"];
      jQuery.get('/templates/enemy_player.html', function(data) {
        var enemys = "";
        for (var key in gameData["game"]["player"]) {

          if (!gameData["game"]["player"][key]["is_me"]) {
            var template_copy = data;
            template_copy = str_replace_all(template_copy, "{name}", gameData["game"]["player"][key]["name"]);
            template_copy = str_replace_all(template_copy, "{cards}", gameData["game"]["player"][key]["cards"]);
            template_copy = str_replace_all(template_copy, "{wins}", gameData["game"]["player"][key]["wins"]);
            template_copy = str_replace_all(template_copy, "{id}", key);
            enemys += template_copy;
          } else {
            my_player_id = key;
            var newcards = gameData["game"]["player"][key]["cards"].length - my_cards;
            my_cards = gameData["game"]["player"][key]["cards"].length;
            if (newcards > 0) {
              if (gameStarted == false && gameData["game"]["round_started"]) {
                $(".my_cards").empty();
                gameStarted = true;
              }

              var cards = gameData["game"]["player"][key]["cards"].slice(gameData["game"]["player"][key]["cards"].length - newcards);

              for (var i = 0; i < cards.length; i++) {
                $(".my_cards").append('<img src="/images/cards/' + cards[i] + '.jpg" class="card" data-id="' + cards[i] + '">');
              }
            } else if (gameData["game"]["game_state"] == -1) {
              $(".my_cards").empty();
              $(".center_card img.card").attr("src", "/images/cards/no_card.jfif");
              $(".my_cards").removeClass("current_player");
              $(".enemy.current_player").removeClass("current_player");
              my_cards = 0;
            }
          }
        }
        $(".top_inner").empty().append(enemys);

        if (current_player != gameData["game"]["current_player"]) {
          if (parseInt(my_player_id) != parseInt(gameData["game"]["current_player"]))
            $(".my_cards").removeClass("current_player");
          else
            $(".enemy.current_player").removeClass("current_player");

          current_player = gameData["game"]["current_player"];

          if (parseInt(my_player_id) == parseInt(gameData["game"]["current_player"]))
            $(".my_cards").addClass("current_player");
          else
            $(".enemy.enemy_" + current_player).addClass("current_player");
        }
      });

      gameStarted = gameData["game"]["round_started"];

      if(gameData["game"]["someone_won"]){
        $(".winner").css("display", "flex");
        $(".winner .winner_name").text(gameData["game"]["winner"]);
        winner = true;
        my_cards = 0;
      }else{
        $(".winner").css("display", "none");
        winner = false;
      }



      if (!gameData["game"]["round_started"]) {
        //Draw waitlist
        return false;
      }
      var current_player = gameData["game"]["player"][gameData["game"]["current_player"]]["name"];

      $(".buttons label.current").text("Am Zug: " + current_player);
      $(".buttons label.owner").text("Raum Admin: " + gameData["gameroom"]["owner"]);
      $(".center_card img.card").attr("src", "/images/cards/" + gameData["game"]["current_card"] + ".jpg");

      var colorChange = "";
      if (gameData["game"]["current_card_color_change"] == 0) {
        $(".center_card .color_change").show();
        $(".center_card .color_change").css("background-image", "url(/images/card_colors/herz.png)");
      } else if (gameData["game"]["current_card_color_change"] == 1) {
        $(".center_card .color_change").show();
        $(".center_card .color_change").css("background-image", "url(/images/card_colors/laub.png)");
      } else if (gameData["game"]["current_card_color_change"] == 2) {
        $(".center_card .color_change").show();
        $(".center_card .color_change").css("background-image", "url(/images/card_colors/eichel.png)");
      } else if (gameData["game"]["current_card_color_change"] == 3) {
        $(".center_card .color_change").show();
        $(".center_card .color_change").css("background-image", "url(/images/card_colors/schell.png)");
      }else{
        $(".center_card .color_change").hide();
      }

      $(".center_card .color_change").text(colorChange);
    }
  });
}

function str_replace_all(string, str_find, str_replace) {
  try {
    return string.replace(new RegExp(str_find, "gi"), str_replace);
  } catch (ex) {
    return string;
  }
}
