/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * toybattle implementation : © <Mathieu Chatrain> <mathieu.chatrain@gmail.com> && <Yannick Briol> <camertwo@hotmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * toybattle.js
 *
 * toybattle user interface script
 *
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */
const TOOLTIP_DELAY = 500;

define(["dojo", "dojo/_base/declare", "ebg/core/gamegui", "ebg/counter"], function (dojo, declare, counter) {
  return declare("bgagame.toybattle", ebg.core.gamegui, {
    constructor: function () {
      console.log("toybattle constructor");
    },

    /////////////////////////////////////////////////////////////////////////////////
    //    _____                      _____        _
    //   / ____|                    |  __ \      | |
    //  | |  __  __ _ _ __ ___   ___| |  | | __ _| |_ __ _ ___
    //  | | |_ |/ _` | '_ ` _ \ / _ \ |  | |/ _` | __/ _` / __|
    //  | |__| | (_| | | | | | |  __/ |__| | (_| | || (_| \__ \
    //   \_____|\__,_|_| |_| |_|\___|_____/ \__,_|\__\__,_|___/
    //
    /////////////////////////////////////////////////////////////////////////////////

    setup: function (gamedatas) {
      console.log("Starting game setup");

      console.log("gamedatas");
      console.log(gamedatas);

      this.boards = ["castle", "pool", "clouds", "jungle", "cemetery", "carribean", "station", "battlefield", "christmas", "croisette"];
      this.medals_to_win = [7, 6, 8, 7, 7, 5, 7, 8, 7, 5];

      this.BLUE_COLOR = "4f66a2";
      this.RED_COLOR = "d1553e";
      this.BLUE = 0;
      this.RED = 1;

      this.TROOP_WIDTH = 66;
      this.TROOP_HEIGHT = 88;
      this.BOARD_WIDTH = 500;
      this.BOARD_HEIGHT = 833.5;
      this.GOODIE_WIDTH = 54;
      this.GOODIE_HEIGHT = 652;
      this.MEDAL_WIDTH = 26;
      this.RACK_WIDTH = 600;
      this.RACK_HEIGHT = 60;
      this.LINE_WIDTH = 670;
      this.LINE_HEIGHT = 110;
      this.VICTORY_WIDTH = 953;
      this.VICTORY_HEIGHT = 400;
      this.DECK_COUNTER_SIZE = 16;

      this.players = gamedatas.players; // A RAJOUTER/NE PAS SUPPRIMER POUR MOTEUR (UTILITY METHODS)

      this.connections = [];

      this._connections = [];

      this.bases = gamedatas.bases;
      this.regions = gamedatas.regions; //USELESS
      this.medals = gamedatas.medals;
      this.goodies = gamedatas.goodies;
      this.troop_types = gamedatas.troop_types;
      this.board_type = gamedatas.board_type;
      this.board_types = gamedatas.board_types;
      this.board_name = gamedatas.board_name;
      this.board_id = gamedatas.board_id;

      // delays notification to let animations finish.
      this.DELAY_JUNGLE = this.board_id == 4 ? 1000 : 1;
      this.DELAY_BATTLEFIELD = this.board_id == 8 || this.board_id == 10 ? 1000 : 1;

      //TODO check if spectator is always BLUE
      this.opponent_id = gamedatas.opponent_id;
      this.spectator_id = gamedatas.spectator_id;
      this.other_player_id = gamedatas.other_player_id;

      //211224this.board_troops = gamedatas.board_troops;

      this.my_hand = gamedatas.my_hand;
      this.your_hand = gamedatas.your_hand;
      this.my_discard = gamedatas.my_discard;
      this.your_discard = gamedatas.your_discard;

      this.troops_on_bases = [];

      this.nb_decks = [gamedatas.nb_deck_blue, gamedatas.nb_deck_red];

      this.troops_blocked = [gamedatas.blue_blocked, gamedatas.red_blocked];

      this.setupPlayersBoard();
      this.setupBoard();
      this.setupCounters();
      this.setupTooltips();

      this.addHelp();

      // Setup game notifications to handle (see "setupNotifications" method below)
      this.setupNotifications();

      console.log("Ending game setup");
    },

    /////////////////////////////////////////////////////////////////////////////////
    //         _____ _        _
    //        / ____| |      | |
    //       | (___ | |_ __ _| |_ ___  ___
    //        \___ \| __/ _` | __/ _ \/ __|
    //        ____) | || (_| | ||  __/\__ \
    //       |_____/ \__\__,_|\__\___||___/
    //
    /////////////////////////////////////////////////////////////////////////////////

    // onEnteringState: this method is called each time we are entering into a new game state.
    //                  You can use this method to perform some user interface changes at this moment.
    //
    onEnteringState: function (stateName, args) {
      if (stateName != "pending") {
        console.log("Entering state: " + stateName, args);
      }

      switch (stateName) {
        case "playerTurn":
          this.args = args.args;
          if (this.isCurrentPlayerActive()) {
            this.args.selectable.forEach((sid) => {
              const element = document.getElementById(sid); //on va recupere le div complet de l'element

              if (element.classList.contains("troop") || element.classList.contains("deck") || element.classList.contains("troop_x")) {
                this.addSVGs(element, "selectable");
              } else {
                dojo.addClass(sid, "selectable");
              }
            });
            this.args.selected.forEach((sid) => {
              const element = document.getElementById(sid); //on va recupere le div complet de l'element
              if (element.classList.contains("troop") || element.classList.contains("deck") || element.classList.contains("troop_x")) {
                this.addSVGs(element, "selected");
              } else {
                dojo.addClass(sid, "selected");
              }
            });

            this.setupConnections(this.args.selectable);

            if (args.args.titleyou != null) {
              $("pagemaintitletext").innerHTML = this.format_string_recursive(
                _(args.args.titleyou)
                  .replace("${you}", this.divYou())
                  .replace(/#opponent#/g, args.args.opponent)
                  .replace("#nb#", args.args.nb)
                  .replace("#nb2#", args.args.nb2)
                  .replace("#icon#", args.args.icon)
                  .replace("#icon2#", args.args.icon2),
                args.args,
              );
            }
          } else {
            if (args.args.title != null) {
              $("pagemaintitletext").innerHTML = this.format_string_recursive(
                _(args.args.title)
                  .replace("${actplayer}", this.divActPlayer())
                  .replace("#nb#", args.args.nb)
                  .replace("#nb2#", args.args.nb2)
                  .replace("#icon#", args.args.icon)
                  .replace("#icon2#", args.args.icon2),
                args.args,
              );
            }
          }

          break;
        case "dummmy":
          break;
      }
    },

    // onLeavingState: this method is called each time we are leaving a game state.
    //                 You can use this method to perform some user interface changes at this moment.
    //

    onLeavingState: function (stateName) {
      if (stateName != "pending") {
        console.log("Leaving state: " + stateName);
      }

      dojo.query(".selectable").removeClass("selectable");
      dojo.query(".selected").removeClass("selected");
      this.removeSVGs();

      switch (stateName) {
        case "playerTurn":
          this.removeConnections();
          break;
        case "dummy":
          break;
      }
    },

    // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
    //                        action status bar (ie: the HTML links in the status bar).
    //

    onUpdateActionButtons: function (stateName, args) {
      console.log("onUpdateActionButtons: " + stateName, args);

      if (this.isCurrentPlayerActive()) {
        switch (stateName) {
          case "playerTurn":
            for (let button of args.buttons) {
              switch (button) {
                case "btn_cancel":
                  this.addActionButton("btn_cancel", _("Cancel"), "onOpButton", null, null, "red");
                  break;
                case "btn_pass":
                  this.addActionButton("btn_pass", _("Pass"), "onOpButton", null, null, "red");
                  break;
                case "btn_continue":
                  this.addActionButton("btn_continue", _("Continue"), "onOpButton", null, null, "blue");
                  break;
                case "btn_draw_2":
                  this.addActionButton("btn_draw_2", _("Draw 2 Troops"), "onOpButton", null, null, "blue");
                  //dojo.removeClass('btn_draw_2', 'bgabutton_blue');
                  //dojo.addClass('btn_draw_2', 'bgabutton_orange');
                  break;
                case "btn_draw_1":
                  this.addActionButton("btn_draw_1", _("Draw 1 Troop"), "onOpButton", null, null, "blue");
                  //dojo.removeClass('btn_draw_1', 'bgabutton_blue');
                  //dojo.addClass('btn_draw_1', 'bgabutton_orange');
                  break;
                case "btn_place_troop":
                  this.addActionButton("btn_place_troop", _("Place 1 Troop"), "onOpButton", null, null, "blue");
                  //dojo.removeClass('btn_place_troop', 'bgabutton_blue');
                  //dojo.addClass('btn_place_troop', 'bgabutton_khakhi');
                  break;
                case "btn_yes":
                  this.addActionButton("btn_yes", _("Yes"), "onOpButton", null, null, "blue");
                  this.startActionTimer("btn_yes", 5, 1);
                  break;
                case "btn_no":
                  this.addActionButton("btn_no", _("No"), "onOpButton", null, null, "red");
                  break;
                case "btn_discard":
                  this.addActionButton("btn_discard", _("Discard Troop"), "onOpButton", null, null, "blue");
                  break;
                case "btn_point":
                  this.addActionButton("btn_point", _("Point Troop"), "onOpButton", null, null, "blue");
                  break;
              }
            }
            break;
        }
      }
    },

    /////////////////////////////////////////////////////////////////////////////////
    //   _    _ _   _ _ _ _                          _   _               _
    //  | |  | | | (_) (_) |                        | | | |             | |
    //  | |  | | |_ _| |_| |_ _   _   _ __ ___   ___| |_| |__   ___   __| |___
    //  | |  | | __| | | | __| | | | | '_ ` _ \ / _ \ __| '_ \ / _ \ / _` / __|
    //  | |__| | |_| | | | |_| |_| | | | | | | |  __/ |_| | | | (_) | (_| \__ \
    //   \____/ \__|_|_|_|\__|\__, | |_| |_| |_|\___|\__|_| |_|\___/ \__,_|___/
    //                         __/ |
    //                        |___/
    /////////////////////////////////////////////////////////////////////////////////

    divYou: function () {
      const color = this.players[this.player_id].color;
      const color_bg = "";
      const you = '<span style="font-weight:bold;color:#' + color + ";" + color_bg + '">' + _("You") + "</span>";
      return you;
    },

    divActPlayer: function () {
      const color = this.players[this.getActivePlayerId()].color;
      const name = this.players[this.getActivePlayerId()].name;
      const color_bg = "";
      const you = '<span style="font-weight:bold;color:#' + color + ";" + color_bg + '">' + name + "</span>";
      return you;
    },

    format_string_recursive: function (log, args) {
      try {
        if (log && args && !args.processed) {
          args.processed = true;
        }
      } catch (e) {
        console.error(log, args, "Exception thrown", e.stack);
      }
      return this.inherited(arguments);
    },

    addSVGs: function (image, type) {
      if (this.gamedatas.players[this.getActivePlayerId()].color == this.BLUE_COLOR) {
        // Créer un SVG avec le path pour le contour
        const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
        svg.setAttribute("viewBox", "0 0 709 945"); // Dimensions originales du PNG

        // Créer un path pour un contour
        const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
        path.setAttribute(
          "d",
          "M 35.491522,942.07978 C 23.624815,939.29461 11.444392,929.03145 5.3325369,916.66798 L 1.5112321,908.93796 V 590.20163 271.46527 l 3.7536732,-7.59319 c 6.1213237,-12.38264 15.8105097,-20.78854 28.4713127,-24.70042 5.837234,-1.80357 9.380906,-1.95574 45.544214,-1.95574 h 39.214488 l 0.27046,-63.4997 0.27046,-63.4997 3.2299,-6.55818 c 2.29409,-4.658061 6.31563,-9.800429 13.88011,-17.748617 41.97018,-44.099052 98.80953,-71.214525 170.63059,-81.4000971 18.00898,-2.5540102 76.6838,-2.5363174 94.52981,0.028485 47.52846,6.8307941 84.84403,19.5580121 119.95702,40.9137591 27.51567,16.73507 57.48146,44.189214 64.7601,59.33216 l 3.11097,6.47224 0.2842,62.97981 0.2842,62.97983 h 39.33702 c 37.1769,0 39.67716,0.1136 45.53046,2.06891 15.46952,5.16763 27.01465,17.42438 31.19373,33.11654 1.14802,4.31077 1.36725,55.66089 1.35675,317.80027 -0.008,207.25117 -0.35233,314.13177 -1.02008,317.02817 -3.55255,15.40614 -16.55893,29.29712 -31.9014,34.07105 -5.89919,1.83559 -14.54136,1.88378 -320.45191,1.78646 -185.33552,-0.0588 -315.976376,-0.47253 -318.255788,-1.00753 z",
        ); // code path

        // Créer un path pour un contour
        const path2 = document.createElementNS("http://www.w3.org/2000/svg", "path");
        path2.setAttribute(
          "d",
          "M 35.491522,942.07978 C 23.624815,939.29461 11.444392,929.03145 5.3325369,916.66798 L 1.5112321,908.93796 V 590.20163 271.46527 l 3.7536732,-7.59319 c 6.1213237,-12.38264 15.8105097,-20.78854 28.4713127,-24.70042 5.837234,-1.80357 9.380906,-1.95574 45.544214,-1.95574 h 39.214488 l 0.27046,-63.4997 0.27046,-63.4997 3.2299,-6.55818 c 2.29409,-4.658061 6.31563,-9.800429 13.88011,-17.748617 41.97018,-44.099052 98.80953,-71.214525 170.63059,-81.4000971 18.00898,-2.5540102 76.6838,-2.5363174 94.52981,0.028485 47.52846,6.8307941 84.84403,19.5580121 119.95702,40.9137591 27.51567,16.73507 57.48146,44.189214 64.7601,59.33216 l 3.11097,6.47224 0.2842,62.97981 0.2842,62.97983 h 39.33702 c 37.1769,0 39.67716,0.1136 45.53046,2.06891 15.46952,5.16763 27.01465,17.42438 31.19373,33.11654 1.14802,4.31077 1.36725,55.66089 1.35675,317.80027 -0.008,207.25117 -0.35233,314.13177 -1.02008,317.02817 -3.55255,15.40614 -16.55893,29.29712 -31.9014,34.07105 -5.89919,1.83559 -14.54136,1.88378 -320.45191,1.78646 -185.33552,-0.0588 -315.976376,-0.47253 -318.255788,-1.00753 z",
        ); // code path

        if (type == "selectable") {
          // Ajouter la classe 'selectable' au div d'image
          image.classList.add("selectable");

          path.setAttribute("class", "path_selectable_black");
          path2.setAttribute("class", "path_selectable");
        }

        if (type == "selected") {
          // Ajouter la classe 'selected' au div d'image
          image.classList.add("selected");

          path.setAttribute("class", "path_selected_black");
          path2.setAttribute("class", "path_selected");
        }

        // Ajouter le path au SVG
        svg.appendChild(path);
        svg.appendChild(path2);

        // Ajouter le SVG en tant qu'élément enfant du div d'image
        image.appendChild(svg);
      }

      if (this.gamedatas.players[this.getActivePlayerId()].color == this.RED_COLOR) {
        // Créer un SVG avec le path pour le contour
        const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
        svg.setAttribute("viewBox", "0 0 709 945"); // Dimensions originales du PNG

        // Créer un path pour un contour
        const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
        path.setAttribute(
          "d",
          "m 33.926433,941.19236 c -3.122354,-0.99971 -7.602391,-2.94788 -9.955638,-4.32924 C 17.331631,932.96585 8.5383965,923.07395 4.9524655,915.46854 L 1.7720181,908.72316 1.5169738,591.72589 C 1.2822128,299.93877 1.3920358,274.33006 2.8979073,269.71738 6.5493291,258.53259 13.50774,249.48519 23.296168,243.19535 c 10.294055,-6.61474 12.630356,-6.9017 56.190614,-6.9017 h 39.208168 l 0.27679,-64.55124 0.27679,-64.55124 2.30925,-4.8329 c 3.06463,-6.413798 9.48372,-13.246091 15.14308,-16.117842 2.52447,-1.281 50.03084,-20.867913 105.56972,-43.526481 l 100.97978,-41.1973878 10.9512,0.064554 10.9512,0.064554 83.62736,34.1651588 c 45.99505,18.790836 91.8015,37.471582 101.79215,41.512769 9.99062,4.041183 20.21431,8.550369 22.71933,10.020414 4.88515,2.866822 11.22713,10.498706 14.04473,16.901241 2.10251,4.77769 2.24399,9.75538 2.28568,80.40616 l 0.0301,51.54005 41.06699,0.30034 41.06703,0.30034 7.77382,3.71704 c 13.01663,6.22386 21.81107,16.59141 25.96429,30.60877 1.46071,4.92986 1.60332,33.45687 1.59255,318.51923 -0.0113,258.24412 -0.24533,313.97781 -1.34349,318.08906 -4.22614,15.81904 -15.78474,28.15579 -31.21797,33.31962 l -6.19087,2.07142 -314.38066,-0.0535 C 52.583971,943.01253 39.368765,942.93491 33.926162,941.19251 Z",
        ); // code path

        // Créer un path pour un contour
        const path2 = document.createElementNS("http://www.w3.org/2000/svg", "path");
        path2.setAttribute(
          "d",
          "m 33.926433,941.19236 c -3.122354,-0.99971 -7.602391,-2.94788 -9.955638,-4.32924 C 17.331631,932.96585 8.5383965,923.07395 4.9524655,915.46854 L 1.7720181,908.72316 1.5169738,591.72589 C 1.2822128,299.93877 1.3920358,274.33006 2.8979073,269.71738 6.5493291,258.53259 13.50774,249.48519 23.296168,243.19535 c 10.294055,-6.61474 12.630356,-6.9017 56.190614,-6.9017 h 39.208168 l 0.27679,-64.55124 0.27679,-64.55124 2.30925,-4.8329 c 3.06463,-6.413798 9.48372,-13.246091 15.14308,-16.117842 2.52447,-1.281 50.03084,-20.867913 105.56972,-43.526481 l 100.97978,-41.1973878 10.9512,0.064554 10.9512,0.064554 83.62736,34.1651588 c 45.99505,18.790836 91.8015,37.471582 101.79215,41.512769 9.99062,4.041183 20.21431,8.550369 22.71933,10.020414 4.88515,2.866822 11.22713,10.498706 14.04473,16.901241 2.10251,4.77769 2.24399,9.75538 2.28568,80.40616 l 0.0301,51.54005 41.06699,0.30034 41.06703,0.30034 7.77382,3.71704 c 13.01663,6.22386 21.81107,16.59141 25.96429,30.60877 1.46071,4.92986 1.60332,33.45687 1.59255,318.51923 -0.0113,258.24412 -0.24533,313.97781 -1.34349,318.08906 -4.22614,15.81904 -15.78474,28.15579 -31.21797,33.31962 l -6.19087,2.07142 -314.38066,-0.0535 C 52.583971,943.01253 39.368765,942.93491 33.926162,941.19251 Z",
        ); // code path

        if (type == "selectable") {
          // Ajouter la classe 'selectable' au div d'image
          image.classList.add("selectable");

          path.setAttribute("class", "path_selectable_black");
          path2.setAttribute("class", "path_selectable");
        }

        if (type == "selected") {
          // Ajouter la classe 'selected' au div d'image
          image.classList.add("selected");

          path.setAttribute("class", "path_selected_black");
          path2.setAttribute("class", "path_selected");
        }

        // Ajouter le path au SVG
        svg.appendChild(path);
        svg.appendChild(path2);

        // Ajouter le SVG en tant qu'élément enfant du div d'image
        image.appendChild(svg);
      }
    },

    removeSVGs: function () {
      // Sélectionner tous les éléments <svg> dans le document
      const svgs = document.querySelectorAll("svg");

      // Parcourir chaque <svg>
      svgs.forEach((svg) => {
        // Vérifier si le <svg> contient un <path> avec la classe 'path_selectable' ou 'path_selected'
        const path1 = svg.querySelector("path.path_selectable");
        const path2 = svg.querySelector("path.path_selected");
        if (path1 || path2) {
          // Supprimer le <svg> du DOM
          svg.remove();
        }
      });
    },

    // TIMER sur bouton confirm

    startActionTimer: function (buttonId, time, pref, autoclick = false) {
      var button = $(buttonId);
      var isReadOnly = this.isReadOnly();
      if (button == null || isReadOnly || pref == 2) {
        //debug('Ignoring startActionTimer(' + buttonId + ')', 'readOnly=' + isReadOnly, 'prefValue=' + pref);
        return;
      }

      // If confirm disabled, click on button
      if (pref == 0) {
        if (autoclick) button.click();
        return;
      }

      this._actionTimerLabel = button.innerHTML;
      this._actionTimerSeconds = time;
      this._actionTimerFunction = () => {
        var button = $(buttonId);
        if (button == null) {
          this.stopActionTimer();
        } else if (this._actionTimerSeconds-- > 1) {
          button.innerHTML = this._actionTimerLabel + " (" + this._actionTimerSeconds + ")";
        } else {
          //debug('Timer ' + buttonId + ' execute');
          button.click();
          this.stopActionTimer();
        }
      };
      this._actionTimerFunction();
      this._actionTimerId = window.setInterval(this._actionTimerFunction.bind(this), 1000);
      //debug('Timer #' + this._actionTimerId + ' ' + buttonId + ' start');
    },

    stopActionTimer() {
      if (this._actionTimerId != null) {
        //debug('Timer #' + this._actionTimerId + ' stop');
        window.clearInterval(this._actionTimerId);
        delete this._actionTimerId;
      }
    },

    isReadOnly: function () {
      return this.isSpectator || typeof g_replayFrom != "undefined" || g_archive_mode;
    },

    /*************************************************
     *
     *  setup connections from this.args.selectable
     * on each beginning of new State (Player Turn)
     *
     ************************************************/

    setupConnections: function (selectables) {
      this.connections = [];

      selectables.forEach((elt_id) => {
        const element = document.getElementById(elt_id);

        const resourceClickHandler = (evt) => this.onSelect(evt);
        element.addEventListener("click", resourceClickHandler);
        this.connections.push({ element, event: "click", handler: resourceClickHandler });
      });

      /*    const zoomInputHandler = () => {
        window.localStorage.setItem("TB_zoom", $("zoom_value").value);
        this.onScreenWidthChange();
    };
    
    $("zoom_value").addEventListener("input", zoomInputHandler);
    this.connections.push({element: $("zoom_value"), event: "input", handler: zoomInputHandler });*/
    },

    /*************************************************
     *
     *  reset all connections
     *  on leaving a State
     *
     ************************************************/

    removeConnections: function () {
      this.connections.forEach((connection) => {
        const { element, event, handler } = connection;
        element.removeEventListener(event, handler);
      });
      this.connections = [];
    },

    setupPlayersBoard: function () {
      Object.values(this.players).forEach((player) => {
        const playerBoardElement = document.getElementById("player_board_" + player.id);
        playerBoardElement.insertAdjacentHTML(
          "beforeend",
          `
            <div class="a_board" id="a2_board_${player.id}"></div>
        `,
        );

        if (player.id == this.player_id || (this.isSpectator && player.id == this.spectator_id)) {
          const a2BoardElement = document.getElementById("a2_board_" + player.id);

          /* help mode Tisaac */
          a2BoardElement.insertAdjacentHTML(
            "beforeend",
            `
                        <div id="help-mode-switch">
                            <input type="checkbox" class="checkbox" id="help-mode-chk" />
                            <label class="label" for="help-mode-chk">
                                <div class="ball"></div>
                            </label>
                            <svg aria-hidden="true" focusable="false" data-prefix="fad" data-icon="question-circle" class="svg-inline--fa fa-question-circle fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                <g class="fa-group">
                                    <path class="fa-secondary" fill="currentColor" d="M256 8C119 8 8 119.08 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm0 422a46 46 0 1 1 46-46 46.05 46.05 0 0 1-46 46zm40-131.33V300a12 12 0 0 1-12 12h-56a12 12 0 0 1-12-12v-4c0-41.06 31.13-57.47 54.65-70.66 20.17-11.31 32.54-19 32.54-34 0-19.82-25.27-33-45.7-33-27.19 0-39.44 13.14-57.3 35.79a12 12 0 0 1-16.67 2.13L148.82 170a12 12 0 0 1-2.71-16.26C173.4 113 208.16 90 262.66 90c56.34 0 116.53 44 116.53 102 0 77-83.19 78.21-83.19 106.67z" opacity="0.4"></path>
                                    <path class="fa-primary" fill="currentColor" d="M256 338a46 46 0 1 0 46 46 46 46 0 0 0-46-46zm6.66-248c-54.5 0-89.26 23-116.55 63.76a12 12 0 0 0 2.71 16.24l34.7 26.31a12 12 0 0 0 16.67-2.13c17.86-22.65 30.11-35.79 57.3-35.79 20.43 0 45.7 13.14 45.7 33 0 15-12.37 22.66-32.54 34C247.13 238.53 216 254.94 216 296v4a12 12 0 0 0 12 12h56a12 12 0 0 0 12-12v-1.33c0-28.46 83.19-29.67 83.19-106.67 0-58-60.19-102-116.53-102z"></path>
                                </g>
                            </svg>
                        </div>
                    `,
          );
          const helpModeSwitchElement = document.getElementById("help-mode-switch");
          helpModeSwitchElement.style.display = "inline-block";
          const helpModeCheckbox = document.getElementById("help-mode-chk");
          helpModeCheckbox.addEventListener("change", () => {
            this.toggleHelpMode(helpModeCheckbox.checked);
          });
          this.addTooltip("help-mode-switch", "", _("Toggle Tooltips on Mobile mode."), TOOLTIP_DELAY);
          /* help mode Tisaac */
        }

        /* slider */
        /*        if(player.id == this.opponent_id) {
            const a2BoardElement = document.getElementById('a2_board_' + player.id);
            const initialValue = window.localStorage?.getItem("TB_zoom") ?? 100;
            a2BoardElement.insertAdjacentHTML('beforeend', `
                <div>
                    <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM136 184c-13.3 0-24 10.7-24 24s10.7 24 24 24H280c13.3 0 24-10.7 24-24s-10.7-24-24-24H136z"/></svg>
                    <input type="range" min="50" max="200" value="${initialValue}" class="slider" id="zoom_value">
                    <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM184 296c0 13.3 10.7 24 24 24s24-10.7 24-24V232h64c13.3 0 24-10.7 24-24s-10.7-24-24-24H232V120c0-13.3-10.7-24-24-24s-24 10.7-24 24v64H120c-13.3 0-24 10.7-24 24s10.7 24 24 24h64v64z"/></svg>
                </div>
            `);
            dojo.connect($("zoom_value"), "oninput", () => {
                // debug('zoom changed', $('zoom_value').value);
                window.localStorage.setItem("TB_zoom", $("zoom_value").value);
                this.onScreenWidthChange();
            });
        }*/
        /* slider */
      });
    },

    /**************************
     *
     * board is created from top to bottom for Blue player
     * and inverted for Red player
     *
     **************************/

    setupBoard: function () {
      console.log("Setting up the board");

      if (this.board_id == 9) {
        document.body.classList.add("snow-mode");
      }

      console.log("desktop preference " + this.getGameUserPreference("102"));

      // mobile and portrait mode
      console.log("mobile ? ", $("ebd-body").classList.contains("mobile_version"));
      console.log("mobile 2 ? ", /Mobi|Android|iPhone|iPad|iPod/i.test(navigator.userAgent));

      // Détecter l'orientation initiale
      this.orientation = window.innerWidth > window.innerHeight ? "landscape" : "portrait";

      if (this.checkHorizontalMode()) {
        this.setupHorizontalMode();
      } else {
        if (this.orientation === "landscape") {
          this.setupLandscapeMode();
        } else {
          this.setupPortraitMode();
        }
      }

      console.log(`Detected ${this.orientation} mode`);

      // Gérer les changements d'orientation avec rechargement forcé
      window.addEventListener("resize", () => {
        const newOrientation = window.innerWidth > window.innerHeight ? "landscape" : "portrait";
        if (this.orientation !== newOrientation) {
          console.log(`Orientation changed to ${newOrientation}. Reloading page...`);
          location.reload(); // Recharge la page
        }
      });
    },

    checkHorizontalMode: function () {
      //const isMobile =  window.matchMedia("(max-width: 768px)").matches;
      const isMobile = /Mobi|Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
      return isMobile == false && this.orientation === "landscape" && this.getGameUserPreference("102") == 2;
    },

    setupLandscapeMode: function () {
      const globalBigContainer = document.getElementById("global_big_id");
      const globalContainer = document.getElementById("global_id");

      globalContainer.style.flexDirection = "row";

      // Reinitialization
      globalContainer.className = "";
      globalContainer.innerHTML = "";

      /*  boardContainer definition 
        contains board and all troops
    */

      const sideleftContainer = this.createSideLeft();
      globalBigContainer.insertBefore(sideleftContainer, globalContainer);

      let goodieContainer;

      goodieContainer = this.createGoodie();
      if (this.isCurrentPlayerRed()) {
        goodieContainer.classList.add("board-inverted");
      }
      globalContainer.appendChild(goodieContainer);

      const boardContainer = this.createBoard();
      if (this.isCurrentPlayerRed()) {
        boardContainer.classList.add("board-inverted");
      }
      globalContainer.appendChild(boardContainer);

      this.createBases();
      this.createMedals();
      this.createTroopsOnBoard();

      /*  PlaymatContainer definition 
        contains red Rack red Discard, both Decks, blue Discard and blue Rack
    */

      const playmatContainer = this.createPlaymat();
      playmatContainer.style.flexDirection = "column";
      playmatContainer.style.height = "var(--board-height)";
      playmatContainer.style.width = "670px";
      if (this.isCurrentPlayerRed()) {
        playmatContainer.classList.add("board-inverted");
      }
      globalContainer.appendChild(playmatContainer);

      const redLineContainer = this.createLine("red");
      redLineContainer.style.justifyContent = "center";
      playmatContainer.appendChild(redLineContainer);

      if (this.isCurrentPlayerRed()) {
        /*  yourDeckContainer definition 
        contains Deck and number of Troops TODO
    */
        const redDeckElement = this.createDeck("red");
        redDeckElement.classList.add("board-inverted");
        redLineContainer.appendChild(redDeckElement);
      }

      /*  redRackContainer definition 
        contains Rack and all Troops in Hand
    */

      const redRackContainer = this.createRackWithTroops("red");
      redLineContainer.appendChild(redRackContainer);
      const redTroopsContainer = document.getElementById("red_troops_container");

      if (this.isCurrentPlayerRed()) {
        Object.values(this.my_hand)
          .reverse()
          .forEach((troop) => {
            const troopElement = this.createTroopElement(troop);
            if (troop.blocked > 0) {
              troopElement.insertAdjacentHTML("beforeend", `<div id="check_${troop.blocked}" class="checks check_blue"></div>`);
            }
            troopElement.classList.add("board-inverted");
            redTroopsContainer.appendChild(troopElement);
            this.addCustomTooltip(troopElement.id, this.getTooltipTroopContent(troop.type, troop.id));
          });
      } else {
        Object.values(this.your_hand).forEach((troop, index) => {
          const troopElement = this.createBackTroopElement(troop, index);
          if (this.troops_blocked[1].includes(`${index + 1}`)) {
            troopElement.insertAdjacentHTML("beforeend", `<div id="check_${index + 1}" class="checks check_blue"></div>`);
          }

          troopElement.classList.add("board-inverted");
          redTroopsContainer.appendChild(troopElement);
        });
      }

      if (this.isSpectator || this.isCurrentPlayerBlue()) {
        /*  yourDeckContainer definition 
        contains Deck and number of Troops TODO
    */
        const redDeckElement = this.createDeck("red");
        redDeckElement.classList.add("board-inverted");
        redLineContainer.appendChild(redDeckElement);
      }

      /* redDiscard */
      const redDiscardContainer = this.createDiscard("red");
      redDiscardContainer.style.flexDirection = "row";

      redDiscardContainer.style.width = "100%";
      redDiscardContainer.style.flexWrap = "wrap-reverse";
      redDiscardContainer.style.height = "25%";
      redDiscardContainer.style.alignItems = "flex-end";

      if (this.isCurrentPlayerRed()) {
        redDiscardContainer.style.justifyContent = "flex-end";
      }
      playmatContainer.appendChild(redDiscardContainer);

      if (this.isCurrentPlayerRed()) {
        const red_discard_list = this.my_discard;
        Object.values(red_discard_list)
          .reverse()
          .forEach((troop) => {
            console.log("land discard red", troop);
            const troopElement = this.createTroopElement(troop);
            troopElement.classList.add("board-inverted", "opa_70");
            redDiscardContainer.appendChild(troopElement);
            this.addCustomTooltip(troopElement.id, this.getTooltipTroopContent(troop.type, troop.id));
          });
      } else {
        const red_discard_list = this.your_discard;
        Object.values(red_discard_list)
          .reverse()
          .forEach((troop) => {
            console.log("land discard red", troop);
            const troopElement = this.createTroopElement(troop);
            troopElement.classList.add("board-inverted", "opa_70");
            redDiscardContainer.appendChild(troopElement);
            this.addCustomTooltip(troopElement.id, this.getTooltipTroopContent(troop.type, troop.id));
          });
      }

      /*  blueDiscardContainer definition 
        contains possible Troops in opacity 70 and all discarded ones TODO
    */
      const blueDiscardContainer = this.createDiscard("blue");
      blueDiscardContainer.style.flexDirection = "row";

      blueDiscardContainer.style.width = "100%";
      blueDiscardContainer.style.flexWrap = "wrap-reverse";
      blueDiscardContainer.style.height = "25%";

      if (this.isCurrentPlayerRed()) {
        blueDiscardContainer.style.justifyContent = "flex-end";
      }
      playmatContainer.appendChild(blueDiscardContainer);

      const blue_discard_list = this.isCurrentPlayerRed() ? this.your_discard : this.my_discard;
      Object.values(blue_discard_list).forEach((troop) => {
        console.log("land discard blue", troop);
        const troopElement = this.createTroopElement(troop);
        troopElement.classList.add("opa_70");
        blueDiscardContainer.appendChild(troopElement);
        this.addCustomTooltip(troopElement.id, this.getTooltipTroopContent(troop.type, troop.id));
      });

      /* blueLineContainer */
      const blueLineContainer = this.createLine("blue");
      blueLineContainer.style.justifyContent = "center";
      playmatContainer.appendChild(blueLineContainer);

      if (this.isCurrentPlayerRed()) {
        /* blueDeckElement */
        const blueDeckElement = this.createDeck("blue");
        blueLineContainer.appendChild(blueDeckElement);
      }

      /* blueRackContainer */

      const blueRackContainer = this.createRackWithTroops("blue");
      blueLineContainer.appendChild(blueRackContainer);
      const blueTroopsContainer = document.getElementById("blue_troops_container");

      if (this.isCurrentPlayerRed()) {
        Object.values(this.your_hand).forEach((troop, index) => {
          const backTroopElement = this.createBackTroopElement(troop, index);
          if (this.troops_blocked[0].includes(`${index + 1}`)) {
            backTroopElement.insertAdjacentHTML("beforeend", `<div id="check_${index + 1}" class="checks check_red"></div>`);
          }
          blueTroopsContainer.appendChild(backTroopElement);
        });
      } else if (this.isSpectator) {
        Object.values(this.my_hand).forEach((troop, index) => {
          const backTroopElement = this.createBackTroopElement(troop, index);
          if (this.troops_blocked[0].includes(`${index + 1}`)) {
            backTroopElement.insertAdjacentHTML("beforeend", `<div id="check_${index + 1}" class="checks check_red"></div>`);
          }

          blueTroopsContainer.appendChild(backTroopElement);
        });
      } else {
        Object.values(this.my_hand).forEach((troop) => {
          const troopElement = this.createTroopElement(troop);
          if (troop.blocked > 0) {
            troopElement.insertAdjacentHTML("beforeend", `<div id="check_${troop.blocked}" class="checks check_red"></div>`);
          }

          blueTroopsContainer.appendChild(troopElement);
          this.addCustomTooltip(troopElement.id, this.getTooltipTroopContent(troop.type, troop.id));
        });
      }

      if (this.isSpectator || this.isCurrentPlayerBlue()) {
        /* blueDeckElement */
        const blueDeckElement = this.createDeck("blue");
        blueLineContainer.appendChild(blueDeckElement);
      }

      const siderightContainer = this.createSideRight();
      globalBigContainer.appendChild(siderightContainer);
    },

    setupPortraitMode: function () {
      /*  creates global container with yourLine, playMat and myLine containers  */
      const globalContainer = document.getElementById("global_id");

      // Réinitialization
      globalContainer.className = "";
      globalContainer.innerHTML = "";

      globalContainer.style.flexDirection = "column";

      if (this.isCurrentPlayerRed()) {
        globalContainer.classList.add("board-inverted");
      }

      /*  redLineContainer definition 
        contains redDeckElement and redRackContainer
    */
      const redLineContainer = this.createLine("red");
      redLineContainer.style.justifyContent = "space-between";
      globalContainer.appendChild(redLineContainer);

      if (this.isCurrentPlayerRed()) {
        /*  yourDeckContainer definition 
            contains Deck and number of Troops TODO
        */
        const redDeckElement = this.createDeck("red");
        redDeckElement.classList.add("board-inverted");
        redLineContainer.appendChild(redDeckElement);
      }

      /*  redRackContainer definition 
        contains Rack and all Troops in Hand
    */

      const redRackContainer = this.createRackWithTroops("red");
      redLineContainer.appendChild(redRackContainer);
      const redTroopsContainer = document.getElementById("red_troops_container");

      if (this.isCurrentPlayerRed()) {
        Object.values(this.my_hand)
          .reverse()
          .forEach((troop) => {
            const troopElement = this.createTroopElement(troop);
            if (troop.blocked > 0) {
              troopElement.insertAdjacentHTML("beforeend", `<div id="check_${troop.blocked}" class="checks check_blue"></div>`);
            }
            troopElement.classList.add("board-inverted");
            redTroopsContainer.appendChild(troopElement);
            this.addCustomTooltip(troopElement.id, this.getTooltipTroopContent(troop.type, troop.id));
          });
      } else {
        Object.values(this.your_hand).forEach((troop, index) => {
          const troopElement = this.createBackTroopElement(troop, index);
          if (this.troops_blocked[1].includes(`${index + 1}`)) {
            troopElement.insertAdjacentHTML("beforeend", `<div id="check_${index + 1}" class="checks check_red"></div>`);
          }
          troopElement.classList.add("board-inverted");
          redTroopsContainer.appendChild(troopElement);
        });
      }

      if (this.isSpectator || this.isCurrentPlayerBlue()) {
        /*  yourDeckContainer definition 
            contains Deck and number of Troops TODO
        */
        const redDeckElement = this.createDeck("red");
        redDeckElement.classList.add("board-inverted");
        redLineContainer.appendChild(redDeckElement);
      }

      /*  PlaymatContainer definition 
        contains blueDiscard, Board and redDiscard
    */
      const playmatContainer = this.createPlaymat();
      playmatContainer.style.flexDirection = "row";
      globalContainer.appendChild(playmatContainer);

      /*  blueDiscardContainer definition 
        contains possible Troops in opacity 50 and all discarded ones TODO
    */
      let goodieContainer;

      goodieContainer = this.createGoodie();
      if (this.isCurrentPlayerBlue() || this.isSpectator) {
        playmatContainer.appendChild(goodieContainer);
      }

      const blueDiscardContainer = this.createDiscard("blue");
      blueDiscardContainer.style.flexDirection = "column";
      blueDiscardContainer.style.justifyContent = "flex-end";
      blueDiscardContainer.style.width = "70px";
      blueDiscardContainer.style.alignItems = "center";
      blueDiscardContainer.style.height = "var(--board-height)";
      playmatContainer.appendChild(blueDiscardContainer);

      const blue_discard_list = this.isCurrentPlayerRed() ? this.your_discard : this.my_discard;
      Object.values(blue_discard_list).forEach((troop) => {
        const troopElement = this.createTroopElement(troop);
        troopElement.classList.add("opa_70");
        blueDiscardContainer.appendChild(troopElement);
        this.addCustomTooltip(troopElement.id, this.getTooltipTroopContent(troop.type, troop.id));
      });

      /*  boardContainer definition 
        contains board and all troops
    */

      const boardContainer = this.createBoard();
      playmatContainer.appendChild(boardContainer);
      this.createBases();
      this.createTroopsOnBoard();
      this.createMedals();

      /* redDiscard */
      const redDiscardContainer = this.createDiscard("red");
      redDiscardContainer.style.flexDirection = "column";
      redDiscardContainer.style.justifyContent = "flex-start";
      redDiscardContainer.style.width = "70px";
      redDiscardContainer.style.alignItems = "center";
      redDiscardContainer.style.height = "var(--board-height)";
      playmatContainer.appendChild(redDiscardContainer);
      if (this.isCurrentPlayerRed()) {
        const red_discard_list = this.my_discard;
        Object.values(red_discard_list)
          .reverse()
          .forEach((troop) => {
            const troopElement = this.createTroopElement(troop);
            troopElement.classList.add("board-inverted", "opa_70");
            redDiscardContainer.appendChild(troopElement);
            this.addCustomTooltip(troopElement.id, this.getTooltipTroopContent(troop.type, troop.id));
          });
      } else {
        const red_discard_list = this.your_discard;
        Object.values(red_discard_list)
          .reverse()
          .forEach((troop) => {
            const troopElement = this.createTroopElement(troop);
            troopElement.classList.add("board-inverted", "opa_70");
            redDiscardContainer.appendChild(troopElement);
            this.addCustomTooltip(troopElement.id, this.getTooltipTroopContent(troop.type, troop.id));
          });
      }

      if (this.isCurrentPlayerRed()) {
        playmatContainer.appendChild(goodieContainer);
      }

      /* blueLineContainer */
      const blueLineContainer = this.createLine("blue");
      blueLineContainer.style.justifyContent = "space-between";
      globalContainer.appendChild(blueLineContainer);

      if (this.isCurrentPlayerRed()) {
        /*  yourDeckContainer definition 
            contains Deck and number of Troops TODO
        */
        const blueDeckElement = this.createDeck("blue");
        //blueDeckElement.classList.add('board-inverted');
        blueLineContainer.appendChild(blueDeckElement);
      }

      /* blueRackContainer */

      const blueRackContainer = this.createRackWithTroops("blue");
      blueLineContainer.appendChild(blueRackContainer);
      const blueTroopsContainer = document.getElementById("blue_troops_container");

      if (this.isCurrentPlayerRed()) {
        Object.values(this.your_hand).forEach((troop, index) => {
          const backTroopElement = this.createBackTroopElement(troop, index);
          if (this.troops_blocked[0].includes(`${index + 1}`)) {
            backTroopElement.insertAdjacentHTML("beforeend", `<div id="check_${index + 1}" class="checks check_red"></div>`);
          }
          blueTroopsContainer.appendChild(backTroopElement);
        });
      } else if (this.isSpectator) {
        Object.values(this.my_hand).forEach((troop, index) => {
          const backTroopElement = this.createBackTroopElement(troop, index);
          if (this.troops_blocked[0].includes(`${index + 1}`)) {
            backTroopElement.insertAdjacentHTML("beforeend", `<div id="check_${index + 1}" class="checks check_red"></div>`);
          }
          blueTroopsContainer.appendChild(backTroopElement);
        });
      } else {
        Object.values(this.my_hand).forEach((troop) => {
          const troopElement = this.createTroopElement(troop);
          if (troop.blocked > 0) {
            troopElement.insertAdjacentHTML("beforeend", `<div id="check_${troop.blocked}" class="checks check_red"></div>`);
          }
          blueTroopsContainer.appendChild(troopElement);
          this.addCustomTooltip(troopElement.id, this.getTooltipTroopContent(troop.type, troop.id));
        });
      }

      if (this.isSpectator || this.isCurrentPlayerBlue()) {
        /* blueDeckElement */
        const blueDeckElement = this.createDeck("blue");
        blueLineContainer.appendChild(blueDeckElement);
      }
    },

    setupHorizontalMode: function () {
      const globalBigContainer = document.getElementById("global_big_id");
      const globalContainer = document.getElementById("global_id");

      globalBigContainer.style.height = `724px`;
      //globalBigContainer.style.pointerEvents = 'none';
      globalBigContainer.style.justifyContent = "space-between";

      globalContainer.style.flexDirection = "column";

      // Appliquer la rotation
      if (this.isCurrentPlayerRed()) {
        globalContainer.style.transform = "translateX(-50%) rotate(270deg)";
      } else {
        globalContainer.style.transform = "translateX(-50%) rotate(90deg)";
      }

      globalContainer.style.left = `50%`;
      globalContainer.style.width = `auto`;
      globalContainer.style.height = `auto`;

      //globalContainer.style.pointerEvents = 'auto';

      /*  redLineContainer definition 
        contains redDeckElement and redRackContainer
    */
      const redLineContainer = this.createLine("red");
      redLineContainer.style.justifyContent = "space-between";
      globalContainer.appendChild(redLineContainer);

      if (this.isCurrentPlayerRed()) {
        /*  yourDeckContainer definition 
            contains Deck and number of Troops TODO
        */
        const redDeckElement = this.createDeck("red");
        redDeckElement.classList.add("board-inverted");
        redLineContainer.appendChild(redDeckElement);
      }

      /*  redRackContainer definition 
        contains Rack and all Troops in Hand
    */

      const redRackContainer = this.createRackWithTroops("red");
      redLineContainer.appendChild(redRackContainer);
      const redTroopsContainer = document.getElementById("red_troops_container");

      if (this.isCurrentPlayerRed()) {
        Object.values(this.my_hand)
          .reverse()
          .forEach((troop) => {
            const troopElement = this.createTroopElement(troop);
            if (troop.blocked > 0) {
              troopElement.insertAdjacentHTML("beforeend", `<div id="check_${troop.blocked}" class="checks check_blue"></div>`);
            }
            troopElement.classList.add("board-inverted");
            redTroopsContainer.appendChild(troopElement);
            this.addCustomTooltip(troopElement.id, this.getTooltipTroopContent(troop.type, troop.id));
          });
      } else {
        Object.values(this.your_hand).forEach((troop, index) => {
          const troopElement = this.createBackTroopElement(troop, index);
          if (this.troops_blocked[1].includes(`${index + 1}`)) {
            troopElement.insertAdjacentHTML("beforeend", `<div id="check_${index + 1}" class="checks check_red"></div>`);
          }
          troopElement.classList.add("board-inverted");
          redTroopsContainer.appendChild(troopElement);
        });
      }

      if (this.isSpectator || this.isCurrentPlayerBlue()) {
        /*  yourDeckContainer definition 
            contains Deck and number of Troops TODO
        */
        const redDeckElement = this.createDeck("red");
        redDeckElement.classList.add("board-inverted");
        redLineContainer.appendChild(redDeckElement);
      }

      /*  PlaymatContainer definition 
        contains blueDiscard, Board and redDiscard
    */
      const playmatContainer = this.createPlaymat();
      playmatContainer.style.flexDirection = "row";
      globalContainer.appendChild(playmatContainer);

      /*  blueDiscardContainer definition 
        contains possible Troops in opacity 50 and all discarded ones TODO
    */

      const blueDiscardContainer = this.createDiscard("blue");
      blueDiscardContainer.style.flexDirection = "column";

      if (this.isCurrentPlayerRed()) {
        blueDiscardContainer.style.flexWrap = "wrap-reverse";
        blueDiscardContainer.style.justifyContent = "flex-end";
      } else {
        blueDiscardContainer.style.flexWrap = "wrap";
        blueDiscardContainer.style.justifyContent = "flex-end";
      }

      blueDiscardContainer.style.width = "140px";
      blueDiscardContainer.style.maxWidth = "140px";
      //blueDiscardContainer.style.alignItems = 'center';
      blueDiscardContainer.style.height = "calc(var(--board-height) / 2)";

      /* redDiscard */
      const redDiscardContainer = this.createDiscard("red");
      redDiscardContainer.style.flexDirection = "column";

      if (this.isCurrentPlayerRed()) {
        redDiscardContainer.style.flexWrap = "wrap-reverse";
        redDiscardContainer.style.justifyContent = "flex-start";
      } else {
        redDiscardContainer.style.flexWrap = "wrap";
        redDiscardContainer.style.justifyContent = "flex-start";
      }
      redDiscardContainer.style.width = "140px";
      redDiscardContainer.style.maxWidth = "140px";
      //redDiscardContainer.style.alignItems = 'center';
      redDiscardContainer.style.height = "calc(var(--board-height) / 2)";

      if (this.isCurrentPlayerRed()) {
        playmatContainer.insertAdjacentHTML("beforeend", `<div id="discards_container" class="discards_horizontal"></div>`);

        const discardsContainer = document.getElementById("discards_container");
        discardsContainer.appendChild(redDiscardContainer);
        discardsContainer.appendChild(blueDiscardContainer);
      }

      let goodieContainer;

      goodieContainer = this.createGoodie();
      if (this.isCurrentPlayerBlue() || this.isSpectator) {
        playmatContainer.appendChild(goodieContainer);
      }

      /*  boardContainer definition 
        contains board and all troops
    */

      const boardContainer = this.createBoard();
      playmatContainer.appendChild(boardContainer);
      this.createBases();
      this.createTroopsOnBoard();
      this.createMedals();

      if (this.isCurrentPlayerBlue() || this.isSpectator) {
        playmatContainer.insertAdjacentHTML("beforeend", `<div id="discards_container" class="discards_horizontal"></div>`);
        const discardsContainer = document.getElementById("discards_container");

        discardsContainer.appendChild(redDiscardContainer);
        discardsContainer.appendChild(blueDiscardContainer);
      }

      if (this.isCurrentPlayerRed()) {
        playmatContainer.appendChild(goodieContainer);
      }

      const blue_discard_list = this.isCurrentPlayerRed() ? this.your_discard : this.my_discard;
      Object.values(blue_discard_list).forEach((troop) => {
        const troopElement = this.createTroopElement(troop);
        troopElement.classList.add("opa_70");
        blueDiscardContainer.appendChild(troopElement);
        this.addCustomTooltip(troopElement.id, this.getTooltipTroopContent(troop.type, troop.id));
      });
      if (this.isCurrentPlayerRed()) {
        const red_discard_list = this.my_discard;
        Object.values(red_discard_list).forEach((troop) => {
          const troopElement = this.createTroopElement(troop);
          troopElement.classList.add("board-inverted", "opa_70");
          redDiscardContainer.appendChild(troopElement);
          this.addCustomTooltip(troopElement.id, this.getTooltipTroopContent(troop.type, troop.id));
        });
      } else {
        const red_discard_list = this.your_discard;
        Object.values(red_discard_list)
          .reverse()
          .forEach((troop) => {
            const troopElement = this.createTroopElement(troop);
            troopElement.classList.add("board-inverted", "opa_70");
            redDiscardContainer.appendChild(troopElement);
            this.addCustomTooltip(troopElement.id, this.getTooltipTroopContent(troop.type, troop.id));
          });
      }

      /* blueLineContainer */
      const blueLineContainer = this.createLine("blue");
      blueLineContainer.style.justifyContent = "space-between";
      globalContainer.appendChild(blueLineContainer);

      if (this.isCurrentPlayerRed()) {
        /*  yourDeckContainer definition 
            contains Deck and number of Troops TODO
        */
        const blueDeckElement = this.createDeck("blue");
        //blueDeckElement.classList.add('board-inverted');
        blueLineContainer.appendChild(blueDeckElement);
      }

      /* blueRackContainer */

      const blueRackContainer = this.createRackWithTroops("blue");
      blueLineContainer.appendChild(blueRackContainer);
      const blueTroopsContainer = document.getElementById("blue_troops_container");

      if (this.isCurrentPlayerRed()) {
        Object.values(this.your_hand).forEach((troop, index) => {
          const backTroopElement = this.createBackTroopElement(troop, index);
          if (this.troops_blocked[0].includes(`${index + 1}`)) {
            backTroopElement.insertAdjacentHTML("beforeend", `<div id="check_${index + 1}" class="checks check_red"></div>`);
          }
          blueTroopsContainer.appendChild(backTroopElement);
        });
      } else if (this.isSpectator) {
        Object.values(this.my_hand).forEach((troop, index) => {
          const backTroopElement = this.createBackTroopElement(troop, index);
          if (this.troops_blocked[0].includes(`${index + 1}`)) {
            backTroopElement.insertAdjacentHTML("beforeend", `<div id="check_${index + 1}" class="checks check_red"></div>`);
          }
          blueTroopsContainer.appendChild(backTroopElement);
        });
      } else {
        Object.values(this.my_hand).forEach((troop) => {
          const troopElement = this.createTroopElement(troop);
          if (troop.blocked > 0) {
            troopElement.insertAdjacentHTML("beforeend", `<div id="check_${troop.blocked}" class="checks check_red"></div>`);
          }
          blueTroopsContainer.appendChild(troopElement);
          this.addCustomTooltip(troopElement.id, this.getTooltipTroopContent(troop.type, troop.id));
        });
      }

      if (this.isSpectator || this.isCurrentPlayerBlue()) {
        /* blueDeckElement */
        const blueDeckElement = this.createDeck("blue");
        blueLineContainer.appendChild(blueDeckElement);
      }
    },

    createLine: function (color) {
      const lineContainer = document.createElement("div");
      lineContainer.id = `${color}_line`;
      lineContainer.classList.add("line");

      return lineContainer;
    },

    createTroopElement: function (troop) {
      const troopElement = document.createElement("div");
      troopElement.id = `troop_${troop.id}`;
      if (this.board_id == 9) {
        troopElement.classList.add("troop_x");
      } else {
        troopElement.classList.add("troop");
      }

      const troop_type = troop.type % 10;
      const troop_color = Math.floor(troop.type / 10) - 1;
      troopElement.style.backgroundPosition = `-${troop_type}00% -${troop_color}00%`;

      return troopElement;
    },

    createBackTroopElement: function (troop, index) {
      const backTroopElement = document.createElement("div");
      const back_color = troop.type == 1 ? "blue" : "red";
      const sprite_line = troop.type == 1 ? "0" : "1";
      backTroopElement.id = `${back_color}_troop_${index + 1}`;
      if (this.board_id == 9) {
        backTroopElement.classList.add("troop_x");
      } else {
        backTroopElement.classList.add("troop");
      }
      backTroopElement.style.backgroundPosition = `-0% -${sprite_line}00%`;

      return backTroopElement;
    },

    createRack: function (color) {
      const rackContainer = document.createElement("div");
      rackContainer.id = `${color}_rack`;
      rackContainer.classList.add(`rack_${color}`);

      return rackContainer;
    },

    createRackWithTroops: function (color) {
      // Conteneur principal
      const rackWrapper = document.createElement("div");
      rackWrapper.id = `${color}_rack_wrapper`;
      rackWrapper.classList.add("rack-wrapper");

      // Rack visuel (fond)
      const rackContainer = this.createRack(color);
      rackWrapper.appendChild(rackContainer);

      // Conteneur pour les troupes
      const troopsContainer = document.createElement("div");
      troopsContainer.id = `${color}_troops_container`;
      troopsContainer.classList.add("troops-container");
      rackWrapper.appendChild(troopsContainer);

      return rackWrapper;
    },

    createDeck: function (color) {
      const deckWrapper = document.createElement("div");
      deckWrapper.id = `${color}_deck_wrapper`;
      deckWrapper.classList.add("deck-wrapper");
      const deckElement = document.createElement("div");
      deckElement.id = `${color}_deck`;
      deckElement.classList.add("deck");
      const sprite_line = color == "blue" ? "0" : "1";
      deckElement.style.backgroundPosition = `0% -${sprite_line}00%`;

      const deckCounterElement = this.createDeckCounter(color);
      deckElement.appendChild(deckCounterElement);
      deckWrapper.appendChild(deckElement);

      return deckWrapper;
    },

    createDeckCounter: function (color) {
      deckCounterElement = document.createElement("div");
      deckCounterElement.id = `${color}_deck_counter_id`;
      deckCounterElement.classList.add("deck_counter", `${color}_deck`);
      if (this.isCurrentPlayerRed()) {
        deckCounterElement.classList.add("board-inverted");
      }
      return deckCounterElement;
    },

    createPlaymat: function () {
      const playmatContainer = document.createElement("div");
      playmatContainer.id = `playmat_id`;
      playmatContainer.classList.add("playmat");

      return playmatContainer;
    },

    createDiscard: function (color) {
      const discardContainer = document.createElement("div");
      discardContainer.id = `${color}_discard`;
      discardContainer.classList.add("discard");

      return discardContainer;
    },

    createGoodie: function () {
      const goodieContainer = document.createElement("div");
      goodieContainer.id = `goodie_${this.board_id}`;
      goodieContainer.classList.add("goodie");

      const medals_needed = this.medals_to_win[this.board_id - 1];
      console.log("MEDALS TO WIN", this.medals_to_win);
      console.log("MEDALS NEEDED", this.medals_to_win[this.board_id - 1]);

      const background_x = medals_needed - 5;
      goodieContainer.style.backgroundPosition = `-${background_x}00% -00%`;

      Object.values(this.players).forEach((player) => {
        let player_indice = player.color == this.BLUE_COLOR ? 1 : 2;
        const medals_won = player.star;
        for (let i = 1; i <= medals_won; i++) {
          if (i == medals_needed) {
            player_indice = 3;
          }
          const goodie_id = `${player_indice}${i}`;

          const goodieElement = document.createElement("div");
          goodieElement.id = `goodie_${goodie_id}`;
          goodieElement.classList.add("medals", "board_medal");
          console.log("Gewdies", this.goodies);
          const goodie = this.goodies[medals_needed][goodie_id];
          console.log("Gewdie", this.goodies[medals_needed][goodie_id]);
          if (!goodie) {
            console.info("GOODIE ERROR", {
              timestamp: Date.now(),
              board_id: this.board_id,
              medals_needed,
              medals_to_win: this.medals_to_win,
              player_star: medals_won,
              player_indice,
              i,
              goodie_id,
              available_groups: Object.keys(this.goodies || {}),
              available_keys: Object.keys(goodieGroup || {}),
            });
          }

          goodieElement.style.cssText = `position: absolute; top: ${goodie.top}%; left: ${goodie.left}%; z-index: 10;`;
          goodieContainer.appendChild(goodieElement);
        }
      });

      return goodieContainer;
    },

    createSideLeft: function () {
      const sideleftContainer = document.createElement("div");
      sideleftContainer.id = `sideleft`;
      sideleftContainer.classList.add("sideleft");
      return sideleftContainer;
    },

    createSideRight: function () {
      const siderightContainer = document.createElement("div");
      siderightContainer.id = `sideright`;
      siderightContainer.classList.add("sideright");
      return siderightContainer;
    },

    createBoard: function () {
      const boardContainer = document.createElement("div");
      boardContainer.id = `board_${this.board_id}`;

      if (this.board_id == 9) {
        boardContainer.classList.add("board_x");
      } else if (this.board_id == 10) {
        boardContainer.classList.add("board_croisette");
      } else {
        boardContainer.classList.add("board");

        const background_x = (this.board_id - 1) % 4;
        const background_y = Math.floor((this.board_id - 1) / 4);
        boardContainer.style.backgroundPosition = `-${background_x}00% -${background_y}00%`;
      }

      return boardContainer;
    },

    createBases: function () {
      const TB_bases = this.bases;
      this.troops_on_bases = {};

      const boardContainer = document.getElementById(`board_${this.board_id}`);

      for (const baseId of Object.keys(TB_bases)) {
        this.troops_on_bases[baseId] = [];
        const baseData = TB_bases[baseId];

        // insert big base element
        boardContainer.insertAdjacentHTML(
          "beforeend",
          `<div id="base_${this.board_name}_${baseId}" class="base_all" style="top: ${baseData.top}%; left: ${baseData.left}%;"></div>`,
        );

        // insert blue base element
        boardContainer.insertAdjacentHTML(
          "beforeend",
          `<div id="blue_base_${this.board_name}_${baseId}" class="base" style="top: ${baseData.top}%; left: ${baseData.left}%;"></div>`,
        );

        // insert red base element
        boardContainer.insertAdjacentHTML(
          "beforeend",
          `<div id="red_base_${this.board_name}_${baseId}" class="base" style="top: ${baseData.top + 2.5}%; left: ${baseData.left}%;"></div>`,
        );
      }
    },

    createTroopsOnBoard: function () {
      const boardContainer = document.getElementById(`board_${this.board_id}`);
      const TB_bases = this.bases;
      Object.values(this.gamedatas.board_troops).forEach((troop) => {
        this.troops_on_bases[troop.location_arg].push(troop);

        const troopElement = this.createTroopElement(troop);
        const troop_color = Math.floor(troop.type / 10) - 1;
        // defines position on board from TB_bases array
        const baseData = TB_bases[troop.location_arg];
        troopElement.style.position = "absolute";
        troopElement.style.top = troop_color == this.BLUE ? `${baseData.top}%` : `${baseData.top + 2.5}%`; // red troops are 2.5% down
        troopElement.style.left = `${baseData.left}%`;
        troopElement.style.zIndex = 10 * troop.ordre;

        if (troop_color == this.RED) {
          troopElement.classList.add("board-inverted");
        }
        boardContainer.appendChild(troopElement);
      });
      this.createBasesTooltips();
    },

    createMedals: function () {
      const boardContainer = document.getElementById(`board_${this.board_id}`);
      const TB_medals = this.medals;
      console.log("TB MEdals", TB_medals);
      console.log("TB full regions", this.gamedatas.full_regions);
      Object.entries(TB_medals).forEach(([id, medal]) => {
        console.log("medal", medal);
        if (this.gamedatas.full_regions.includes(medal.region.toString())) {
          boardContainer.insertAdjacentHTML(
            "beforeend",
            `<div id="medal_${id}" class="medals board_medal" style="position: absolute; top: ${medal.top}%; left: ${medal.left}%; z-index: 10;"></div>`,
          );
        }
      });
    },

    createBasesTooltips: function () {
      Object.keys(this.troops_on_bases).forEach((base_id) => {
        this.createBaseTooltip(base_id);
      });
    },

    createBaseTooltip: function (base_id) {
      const TB_bases = this.bases;
      const base_power = TB_bases[base_id].power;
      const troops = this.troops_on_bases[base_id];

      if (troops.length > 0 || base_power > 0) {
        const base_css_id = `base_${this.board_name}_${base_id}`;
        this.addCustomTooltip(base_css_id, this.getTooltipBaseContent(this.board_id, base_power, troops, base_id));
      }
    },

    destroyBaseTooltip: function (base_id) {
      //TODO virer le tooltip

      const base_css = `base_${this.board_name}_${base_id}`;
      this.tooltips[base_css].destroy();
      delete this.tooltips[base_css];
      let elem = $(base_css); // Récupère l'élément DOM
      this.destroy(elem);

      const TB_bases = this.bases;
      const boardContainer = document.getElementById(`board_${this.board_id}`);
      const baseData = TB_bases[base_id];
      // big base element
      boardContainer.insertAdjacentHTML(
        "beforeend",
        `<div id="base_${this.board_name}_${base_id}" class="base_all" style="top: ${baseData.top}%; left: ${baseData.left}%;"></div>`,
      );
    },

    setupCounters: function () {
      this.blue_deck_counter = new ebg.counter();
      this.blue_deck_counter.create("blue_deck_counter_id");
      this.blue_deck_counter.toValue(this.nb_decks[0]);

      this.red_deck_counter = new ebg.counter();
      this.red_deck_counter.create("red_deck_counter_id");
      this.red_deck_counter.toValue(this.nb_decks[1]);
    },

    setupTooltips: function () {
      // Goodie

      html = "<div class='tooltip_content'><span class='tooltip_description'>" + _("Medals won") + "</span></div>";
      this.addCustomTooltip(`goodie_${this.board_id}`, html);

      // Red Deck
      html = "<div class='tooltip_content'><span class='tooltip_description'>" + _("Troops in Red deck") + "</span></div>";
      this.addCustomTooltip(`red_deck_counter_id`, html);
      // Blue Deck
      html = "<div class='tooltip_content'><span class='tooltip_description'>" + _("Troops in Blue deck") + "</span></div>";
      this.addCustomTooltip(`blue_deck_counter_id`, html);
    },

    addHelp: function () {
      document.body.insertAdjacentHTML("beforeend", `<div id="toybattle_help_button">?</div>`);
      const helpButton = document.getElementById("toybattle_help_button");

      // Ajouter un gestionnaire d'événement pour afficher une aide (modifiable selon besoin)
      helpButton.addEventListener("click", () => {
        this.showHelpModal();
      });
    },

    showHelpModal: function () {
      if (document.getElementById("helpModal")) return;

      let modalContent = `
    <div id="helpModal" class="modal" style="display: flex;">
      <div class="modal-content">
        <span class="close">&times;</span>
        <div class="troop-list">
  `;

      const troopOrder = [11, 22, 13, 24, 15, 26, 17, 28];

      troopOrder.forEach((type) => {
        modalContent += this.getTooltipTroopContent(type, `troop_${type}`);
      });

      modalContent += `</div></div></div>`; // fermeture .troop-list et .modal-content et .modal

      // insérer la modal dans le body
      document.body.insertAdjacentHTML("beforeend", modalContent);

      const modal = document.getElementById("helpModal");
      const closeButton = modal.querySelector(".close");

      // événements
      closeButton.addEventListener("click", () => modal.remove());
      window.addEventListener("click", (event) => {
        if (event.target === modal) modal.remove();
      });
    },

    isCurrentPlayerRed: function () {
      if (this.gamedatas.players[this.player_id]) {
        if (this.gamedatas.players[this.player_id].color == this.RED_COLOR) {
          return true;
        }
      }
      return false;
    },

    isCurrentPlayerBlue: function () {
      if (this.gamedatas.players[this.player_id]) {
        if (this.gamedatas.players[this.player_id].color == this.BLUE_COLOR) {
          return true;
        }
      }
      return false;
    },

    getBoundingClientRectIgnoreZoom: function (element) {
      let rect = element.getBoundingClientRect();
      const zoomCorr = this.interface_autoscale === true && !this.gameinterface_boundingRectIgnoresZoom ? this.gameinterface_zoomFactor || 1 : 1;
      rect.left /= zoomCorr;
      rect.top /= zoomCorr;
      rect.right /= zoomCorr;
      rect.bottom /= zoomCorr;
      rect.x /= zoomCorr;
      rect.y /= zoomCorr;
      rect.width /= zoomCorr;
      rect.height /= zoomCorr;
      return rect;
    },

    onScreenWidthChange: function () {
      const gamePlayArea = document.getElementById("game_play_area");
      const gamePlayAreaWidth = gamePlayArea.clientWidth;
      const gamePlayAreaHeight = window.innerHeight;

      console.log("gamePlayArea", gamePlayArea);
      console.log("gamePlayAreaWidth", gamePlayAreaWidth);
      console.log("gamePlayAreaHeight", gamePlayAreaHeight);

      const globalBigContainer = document.getElementById("global_big_id");
      const sideleft = document.getElementById("sideleft");
      const sideright = document.getElementById("sideright");

      const globalContainer = document.getElementById("global_id");

      const bigglobal = this.getBoundingClientRectIgnoreZoom(globalBigContainer);

      const global = this.getBoundingClientRectIgnoreZoom(globalContainer);

      if (globalBigContainer) {
        const board = document.getElementById(`board_${this.board_id}`); // Élément board
        const playmat = document.getElementById("playmat_id"); // Élément playmat

        //TODO hauteur proportionnelle à la largeur du board.

        if (this.checkHorizontalMode()) {
          if (gamePlayAreaWidth < global.width) {
            globalContainer.style.width = `${gamePlayArea.clientWidth}`;
            globalContainer.style.height = `${gamePlayArea.clientHeight}`;

            console.log("globalW", gamePlayArea.clientWidth);
            console.log("globalH", gamePlayArea.clientHeight);

            this.scale = gamePlayAreaWidth / global.width;

            const boardWidth = this.BOARD_WIDTH * this.scale;
            const boardHeight = this.BOARD_HEIGHT * this.scale;

            const troopWidth = this.TROOP_WIDTH * this.scale;
            const troopHeight = this.TROOP_HEIGHT * this.scale;

            const rackWidth = this.RACK_WIDTH * this.scale;
            const rackHeight = this.RACK_HEIGHT * this.scale;
            const lineWidth = this.LINE_WIDTH * this.scale;
            const lineHeight = this.LINE_HEIGHT * this.scale;

            //const goodieWidth = this.GOODIE_WIDTH  * this.scale;
            const goodieHeight = this.GOODIE_HEIGHT * this.scale;
            const medalWidth = this.MEDAL_WIDTH * this.scale;

            const victoryWidth = Math.min(gamePlayAreaWidth, this.VICTORY_WIDTH);
            const victoryHeight = (this.VICTORY_HEIGHT / this.VICTORY_WIDTH) * victoryWidth;

            const deckCounterSize = this.DECK_COUNTER_SIZE * this.scale;

            document.documentElement.style.setProperty("--board-height", `${boardHeight}px`);

            document.documentElement.style.setProperty("--board-width", `${boardWidth}px`);
            document.documentElement.style.setProperty("--board-height", `${boardHeight}px`);

            document.documentElement.style.setProperty("--troop-width", `${troopWidth}px`);
            document.documentElement.style.setProperty("--troop-height", `${troopHeight}px`);

            document.documentElement.style.setProperty("--rack-width", `${rackWidth}px`);
            document.documentElement.style.setProperty("--rack-height", `${rackHeight}px`);
            document.documentElement.style.setProperty("--line-width", `${lineWidth}px`);
            document.documentElement.style.setProperty("--line-height", `${lineHeight}px`);

            //document.documentElement.style.setProperty('--goodie-width', `${goodieWidth}px`);
            document.documentElement.style.setProperty("--goodie-height", `${goodieHeight}px`);

            document.documentElement.style.setProperty("--medal-width", `${medalWidth}px`);

            document.documentElement.style.setProperty("--victory-width", `${victoryWidth}px`);
            document.documentElement.style.setProperty("--victory-height", `${victoryHeight}px`);

            document.documentElement.style.setProperty("--deck-counter-size", `${deckCounterSize}px`);
          }

          //globalContainer.style.width = `${gamePlayAreaHeight}px`;
        } else {
          if (gamePlayAreaWidth < 1532) {
            if (sideleft) {
              sideleft.classList.add("hidden");
            }
            if (sideright) {
              sideright.classList.add("hidden");
            }
            globalBigContainer.style.justifyContent = "center"; // Recentre global_id
            document.documentElement.style.setProperty("--global-width", `100%`);
          } else {
            if (sideleft && sideleft.classList.contains("hidden")) {
              sideleft.classList.remove("hidden");
            }
            if (sideright && sideright.classList.contains("hidden")) {
              sideright.classList.remove("hidden");
            }
            globalBigContainer.style.justifyContent = "space-between"; // Répartit les éléments
            document.documentElement.style.setProperty("--global-width", `1250px`);
          }

          if (board && playmat) {
            // Récupérer les dimensions recalculées
            const boardRect = this.getBoundingClientRectIgnoreZoom(board);
            const playmatRect = this.getBoundingClientRectIgnoreZoom(playmat);

            // Choisir la base de référence (largeur ou hauteur)
            const baseWidth = boardRect.width;
            const baseHeight = (baseWidth / this.BOARD_WIDTH) * this.BOARD_HEIGHT;

            // Calculer la taille des troops proportionnellement
            const troopWidth = (this.TROOP_WIDTH / this.BOARD_WIDTH) * baseWidth; // 66px basé sur la largeur du board original
            const troopHeight = (this.TROOP_HEIGHT / this.TROOP_WIDTH) * troopWidth; // 88px basé sur la hauteur originale
            const deckCounterSize = (this.DECK_COUNTER_SIZE / this.BOARD_WIDTH) * baseWidth;

            const goodieWidth = (this.GOODIE_WIDTH / this.BOARD_WIDTH) * baseWidth; // 66px basé sur la largeur du board original
            const goodieHeight = (this.GOODIE_HEIGHT / this.GOODIE_WIDTH) * goodieWidth; // 88px basé sur la hauteur originale

            const medalWidth = (this.MEDAL_WIDTH / this.BOARD_WIDTH) * baseWidth;

            const rackWidth = (this.RACK_WIDTH / this.BOARD_WIDTH) * baseWidth; // 66px basé sur la largeur du board original
            const rackHeight = (this.RACK_HEIGHT / this.RACK_WIDTH) * rackWidth; // 88px basé sur la hauteur originale

            const lineWidth = (this.LINE_WIDTH / this.BOARD_WIDTH) * baseWidth; // 66px basé sur la largeur du board original
            const lineHeight = (this.LINE_HEIGHT / this.LINE_WIDTH) * lineWidth; // 88px basé sur la hauteur originale

            const victoryWidth = Math.min(gamePlayAreaWidth, this.VICTORY_WIDTH);
            const victoryHeight = (this.VICTORY_HEIGHT / this.VICTORY_WIDTH) * victoryWidth;

            console.log("baseWidth", baseWidth);

            // Mettre à jour les variables CSS

            document.documentElement.style.setProperty("--troop-width", `${troopWidth}px`);
            document.documentElement.style.setProperty("--troop-height", `${troopHeight}px`);
            document.documentElement.style.setProperty("--deck-counter-size", `${deckCounterSize}px`);
            document.documentElement.style.setProperty("--board-height", `${baseHeight}px`);
            //document.documentElement.style.setProperty('--goodie-width', `${goodieWidth}px`);
            document.documentElement.style.setProperty("--goodie-height", `${goodieHeight}px`);
            document.documentElement.style.setProperty("--medal-width", `${medalWidth}px`);
            document.documentElement.style.setProperty("--rack-width", `${rackWidth}px`);
            document.documentElement.style.setProperty("--rack-height", `${rackHeight}px`);
            document.documentElement.style.setProperty("--line-width", `${lineWidth}px`);
            document.documentElement.style.setProperty("--line-height", `${lineHeight}px`);
            document.documentElement.style.setProperty("--victory-width", `${victoryWidth}px`);
            document.documentElement.style.setProperty("--victory-height", `${victoryHeight}px`);
          }
        }
      }
      //const TB_zoom = window.localStorage?.getItem("TB_zoom") ?? 100;
      //this.scale = Math.min(horizontalScale, verticalScale)*BB_zoom/100;
      // TO DO
    },

    /*******************************
     ****** UTILS TISAAC *******
     ******************************/

    /*******************************
     ****** HELP MODE TISAAC *******
     ******************************/
    /**
     * Toggle help mode
     */
    toggleHelpMode(b) {
      if (b) this.activateHelpMode();
      else this.desactivateHelpMode();
    },

    activateHelpMode() {
      this._helpMode = true;
      dojo.addClass("ebd-body", "help-mode");
      this._displayedTooltip = null;
      document.body.addEventListener("click", this.closeCurrentTooltip.bind(this));
    },

    desactivateHelpMode() {
      this.closeCurrentTooltip();
      this._helpMode = false;
      dojo.removeClass("ebd-body", "help-mode");
      document.body.removeEventListener("click", this.closeCurrentTooltip.bind(this));
    },

    closeCurrentTooltip() {
      if (!this._helpMode) return;
      if (this._displayedTooltip == null) return;
      else {
        this._displayedTooltip.close();
        this._displayedTooltip = null;
      }
    },

    /*
     * Custom connect that keep track of all the connections
     *  and wrap clicks to make it work with help mode
     */
    connect(node, action, callback) {
      this._connections.push(dojo.connect($(node), action, callback));
    },

    onClick(node, callback, temporary = true) {
      let safeCallback = (evt) => {
        evt.stopPropagation();
        if (this.isInterfaceLocked()) return false;
        if (this._helpMode) return false;
        callback(evt);
      };

      if (temporary) {
        this.connect($(node), "click", safeCallback);
        dojo.removeClass(node, "unselectable");
        dojo.addClass(node, "selectable");
        this._selectableNodes.push(node);
      } else {
        dojo.connect($(node), "click", safeCallback);
      }
    },

    /**
     * Tooltip to work with help mode
     */
    registerCustomTooltip(html, id = null) {
      id = id || this.game_name + "-tooltipable-" + this._customTooltipIdCounter++;
      this._registeredCustomTooltips[id] = html;
      return id;
    },

    attachRegisteredTooltips() {
      Object.keys(this._registeredCustomTooltips).forEach((id) => {
        if ($(id)) {
          this.addCustomTooltip(id, this._registeredCustomTooltips[id], { forceRecreate: true });
        }
      });
      this._registeredCustomTooltips = {};
    },

    addCustomTooltip(id, html, config = {}) {
      config = Object.assign(
        {
          delay: 400,
          midSize: true,
          forceRecreate: false,
        },
        config,
      );

      let isMobile = window.matchMedia("(pointer: coarse)").matches;
      let longPressTimer = null;

      let getContent = () => {
        let content = typeof html === "function" ? html() : html;
        if (config.midSize) {
          content = '<div class="midSizeDialog">' + content + "</div>";
        }
        return content;
      };

      if (this.tooltips[id] && !config.forceRecreate) {
        this.tooltips[id].getContent = getContent;
        return;
      }

      let tooltip = new dijit.Tooltip({
        getContent,
        position: this.defaultTooltipPosition,
        showDelay: config.delay,
      });
      this.tooltips[id] = tooltip;
      dojo.addClass(id, "tooltipable");

      // Empêcher l'affichage au simple clic sur mobile
      dojo.connect($(id), "click", (evt) => {
        if (isMobile && !this._helpMode) {
          evt.stopPropagation();
          return; // Bloque l'affichage du tooltip sur mobile sauf en mode help
        }

        if (!this._helpMode) {
          tooltip.close();
        } else {
          evt.stopPropagation();

          if (tooltip.state === "SHOWING") {
            this.closeCurrentTooltip();
          } else {
            this.closeCurrentTooltip();
            tooltip.open($(id));
            this._displayedTooltip = tooltip;
          }
        }
      });

      tooltip.showTimeout = null;

      // Gestion du long press sur mobile
      dojo.connect($(id), "touchstart", (evt) => {
        if (isMobile) {
          longPressTimer = setTimeout(() => {
            tooltip.open($(id));
          }, 500); // 500ms = temps pour considérer un long press
        }
      });

      dojo.connect($(id), "touchend", (evt) => {
        if (isMobile) {
          clearTimeout(longPressTimer);
        }
      });

      dojo.connect($(id), "touchmove", (evt) => {
        if (isMobile) {
          clearTimeout(longPressTimer); // Annule le long press si l'utilisateur glisse son doigt
        }
      });

      // Gestion normale des tooltips sur PC
      dojo.connect($(id), "mouseenter", (evt) => {
        evt.stopPropagation();

        if (!this._helpMode && !this._dragndropMode) {
          if (isMobile) return; // Bloque l'affichage des tooltips sur mobile hors help mode

          if (tooltip.showTimeout != null) clearTimeout(tooltip.showTimeout);

          tooltip.showTimeout = setTimeout(() => {
            if ($(id)) tooltip.open($(id));
          }, config.delay);
        }
      });

      dojo.connect($(id), "mouseleave", (evt) => {
        evt.stopPropagation();
        if (!this._helpMode && !this._dragndropMode) {
          tooltip.close();
          if (tooltip.showTimeout != null) clearTimeout(tooltip.showTimeout);
        }
      });
    },

    destroy(elem) {
      if (this.tooltips[elem.id]) {
        this.tooltips[elem.id].destroy();
        delete this.tooltips[elem.id];
      }

      elem.remove();
    },

    /*******************************
     *
     *  TOOLTIPS
     *
     *
     * ***************************** */

    getTooltipTroopContent: function (type, id) {
      let html = '<div class="tooltip_content">';
      // Calcul de la position de l'image
      const x = "-" + (type % 10) + "00%";
      const y = "-" + Math.floor(type / 10) - 1 + "00%";

      if (this.board_id == 9) {
        // Ajout de la troop (image) à gauche Christmas
        html += `<div class="troop_container">
               <div id="tb_troop_toolt_${id}" class="troop_x" style="background-position:${x} ${y};"></div>
            </div>`;
      } else {
        // Ajout de la troop (image) à gauche
        html += `<div class="troop_container">
               <div id="tb_troop_toolt_${id}" class="troop" style="background-position:${x} ${y};"></div>
            </div>`;
      }

      // Ajout des informations à droite
      html += `<div class="info_container">`;

      const troop_infos = this.troop_types[type % 10];

      // Afficher les informations des Troops
      html += `<span class='tooltip_title'>${_(troop_infos.name)}</span>`;
      let effect_desc = troop_infos.desc1;
      html += `<br><span class='tooltip_desc'>${_(effect_desc)}</span>`;

      let effect_info = troop_infos.desc2;
      html += `<br><span class='tooltip_info'>${_(effect_info)}</span>`;

      html += "</div></div>"; // Fermeture des div
      return html;
    },

    getTooltipBaseContent: function (board_id, base_power, troops, base_id) {
      // troops est trié selon l'ordre décroissant
      troops.sort((a, b) => b.ordre - a.ordre);

      let html = '<div">';

      if (base_power > 0) {
        // Afficher les informations de la Base Spéciale

        const board_infos = this.board_type;

        html += '<div id="special_base_desc">';

        // Ajout du titre avec ou sans l'icône
        html += `<span class='tooltip_title'>${_(board_infos.name)}`;
        if ([1, 3, 4, 5, 8].includes(parseInt(board_id))) {
          const iconClass = `icon_power_bandeau icon_powerbase_${board_id}`;
          html += `<span class="${iconClass}" style="margin-left: 10px;"></span>`;
        } else if (parseInt(board_id) == 7) {
          const iconClass = `icon_triangle icon_powerbase_${board_id}`;
          html += `<span class="${iconClass}" style="margin-left: 10px;"></span>`;
        } else if (parseInt(board_id) == 2) {
          // board 2
          if ([12, 22].includes(parseInt(base_id))) {
            const iconClass = `icon_triangle icon_powerbase_212`;
            html += `<span class="${iconClass}" style="margin-left: 10px;"></span>`;
          } else if ([16, 18].includes(parseInt(base_id))) {
            const iconClass = `icon_triangle icon_powerbase_235`;
            html += `<span class="${iconClass}" style="margin-left: 10px;"></span>`;
          } else if ([2, 17, 41].includes(parseInt(base_id))) {
            const iconClass = `icon_triangle icon_powerbase_267`;
            html += `<span class="${iconClass}" style="margin-left: 10px;"></span>`;
          }
        } else if (parseInt(board_id) == 10) {
          // board 2
          if ([12, 22].includes(parseInt(base_id))) {
            const iconClass = `icon_power_bandeau icon_powerbase_101`;
            html += `<span class="${iconClass}" style="margin-left: 10px;"></span>`;
          } else if ([15, 18].includes(parseInt(base_id))) {
            const iconClass = `icon_power_bandeau icon_powerbase_102`;
            html += `<span class="${iconClass}" style="margin-left: 10px;"></span>`;
          } else if ([14, 20].includes(parseInt(base_id))) {
            const iconClass = `icon_triangle icon_powerbase_103`;
            html += `<span class="${iconClass}" style="margin-left: 10px;"></span>`;
          } else if ([17].includes(parseInt(base_id))) {
            const iconClass = `icon_triangle icon_powerbase_104`;
            html += `<span class="${iconClass}" style="margin-left: 10px;"></span>`;
          }
        }

        if (parseInt(board_id) != 10) {
          html += `</span>`;

          // Ajout de la description
          let effect_desc = board_infos.desc1;
          html += `<br><span class='tooltip_desc'>${_(effect_desc)}</span>`;

          // Ajout des informations supplémentaires
          let effect_info = board_infos.desc2;
          html += `<br><span class='tooltip_info'>${_(effect_info)}</span></div>`;

          if (troops.length > 0) {
            html += `<hr style="border: 1px solid #7a9f34; margin: 10px 0;">`;
          }
        }
        if (parseInt(board_id) == 10) {
          html += `</span>`;

          let effect_desc = "";
          let effect_info = "";
          console.log("base_id", base_id);
          if ([12, 22].includes(parseInt(base_id))) {
            effect_desc = this.board_types[3].desc1;
            effect_info = this.board_types[3].desc2;
          } else if ([15, 18].includes(parseInt(base_id))) {
            effect_desc = this.board_types[8].desc1;
            effect_info = this.board_types[8].desc2;
          } else if ([14, 20].includes(parseInt(base_id))) {
            effect_desc = board_infos.desc1;
            effect_info = board_infos.desc2;
          } else if ([17].includes(parseInt(base_id))) {
            effect_desc = this.board_types[7].desc1;
            effect_info = this.board_types[7].desc2;
          }

          // Ajout de la description
          html += `<br><span class='tooltip_desc'>${_(effect_desc)}</span>`;

          // Ajout des informations supplémentaires
          html += `<br><span class='tooltip_info'>${_(effect_info)}</span></div>`;

          if (troops.length > 0) {
            html += `<hr style="border: 1px solid #7a9f34; margin: 10px 0;">`;
          }
        }
      }
      if (troops.length > 0) {
        html += `<span class='tooltip_title'>${_("Order of Troops on Base")}</span>`;
        html += '<div class="tooltip_content">';

        Object.values(troops).forEach((troop) => {
          const x = "-" + (troop.type % 10) + "00%";
          const y = "-" + Math.floor(troop.type / 10) - 1 + "00%";

          if (this.board_id == 9) {
            // Ajout de la troop (image) à gauche Christmas
            html += `<div class="troop_container">
                    <div class="troop_x" style="background-position:${x} ${y};"></div></div>`;
          } else {
            // Ajout de la troop (image) à gauche
            html += `<div class="troop_container">
                    <div class="troop" style="background-position:${x} ${y};"></div></div>`;
          }
        });
        html += "</div>";
      }

      html += "</div>"; // Fermeture des div
      return html;
    },

    /////////////////////////////////////////////////////////////////////////////////
    //         _____  _                       _                  _   _
    //        |  __ \| |                     ( )                | | (_)
    //        | |__) | | __ _ _   _  ___ _ __|/ ___    __ _  ___| |_ _  ___  _ __
    //        |  ___/| |/ _` | | | |/ _ \ '__| / __|  / _` |/ __| __| |/ _ \| '_ \
    //        | |    | | (_| | |_| |  __/ |    \__ \ | (_| | (__| |_| | (_) | | | |
    //        |_|    |_|\__,_|\__, |\___|_|    |___/  \__,_|\___|\__|_|\___/|_| |_|
    //                         __/ |
    //                        |___/
    /////////////////////////////////////////////////////////////////////////////////

    stopEvent: function (evt) {
      if (evt) {
        evt.preventDefault();
        evt.stopPropagation();
      }
    },

    onSelect: function (evt) {
      // Preventing default browser reaction
      this.stopEvent(evt);

      if (this.isCurrentPlayerActive() && !this._helpMode && evt.currentTarget.classList.contains("selectable")) {
        this.bgaPerformAction("actSelect", { arg1: evt.currentTarget.id });
      }
    },

    onOpButton: function (evt) {
      // Preventing default browser reaction
      this.stopEvent(evt);

      this.bgaPerformAction("actButton", { arg1: evt.currentTarget.id });
    },

    /////////////////////////////////////////////////////////////////////////////////
    //       _   _       _   _  __ _           _   _
    //      | \ | |     | | (_)/ _(_)         | | (_)
    //      |  \| | ___ | |_ _| |_ _  ___ __ _| |_ _  ___  _ __  ___
    //      | . ` |/ _ \| __| |  _| |/ __/ _` | __| |/ _ \| '_ \/ __|
    //      | |\  | (_) | |_| | | | | (_| (_| | |_| | (_) | | | \__ \
    //      |_| \_|\___/ \__|_|_| |_|\___\__,_|\__|_|\___/|_| |_|___/
    //
    /////////////////////////////////////////////////////////////////////////////////

    setupNotifications() {
      console.log("notifications subscriptions setup");

      // automatically listen to the notifications, based on the `notif_xxx` function on this class.
      // Uncomment the logger param to see debug information in the console about notifications.
      this.bga.notifications.setupPromiseNotifications({
        // logger: console.log
      });
    },

    /*   setupNotifications: function () {
      console.log("notifications subscriptions setup");

      const notifs = [
        ["displayNotif", 1],
        ["moveTroop", this.DELAY_BATTLEFIELD],
        ["drawTroopPrivate", 1],
        ["drawTroopPublic", 1],
        ["discardTroopFromBoard", this.DELAY_JUNGLE],
        ["discardTroopFromHand", this.DELAY_BATTLEFIELD],
        ["recoverTroopFromBoard", 1],
        ["recoverTroopFromDiscard", 1],
        ["moveTroopBoardToBoard", 1],
        ["hideTroopOnRackPrivate", 1],
        ["hideTroopOnRackPublic", 1],
        ["unhideTroopOnRack", 1],
        ["gainMedal", 1],
        ["score", 1],
        ["victory", 3000],
        ["message_allplayers_without_player", 1],
      ];

      notifs.forEach((notif) => {
        dojo.subscribe(notif[0], this, `notif_${notif[0]}`);
        if (notif[1] != 1) {
          this.notifqueue.setSynchronous(notif[0], notif[1]);
        }
      });

      this.notifqueue.setIgnoreNotificationCheck("message_allplayers_without_player", (notif) => args.player_id == this.player_id);
    },*/

    async notif_displayNotif(args) {
      console.log("notif_displayNotif");
      console.log(args);
    },

    /*********************************
     *
     *  a troop is moved from Rack to Board
     *    Action PLACE
     *    Troop 4 Crochet
     *    Troop 2 Cap'taine
     *
     **********************************/

    async notif_moveTroop(args) {
      console.log("notif_moveTroop");
      console.log(args);

      const player_color = this.players[args.player_id].color;
      const player_color_name = player_color == this.RED_COLOR ? "red" : "blue";

      const boardContainer = document.getElementById(`board_${this.board_id}`);

      const TB_bases = this.bases;

      const troop = args.infos_troop;

      const base_css_id = args.base_id;
      const base_id = base_css_id.split("_")[2];

      // JS part
      if (this.player_id == args.player_id) {
        // troop is removed from hand JS array
        const index = this.my_hand.findIndex((t) => t.id === troop.id);
        if (index !== -1) {
          this.my_hand.splice(index, 1);

          //remove tooltip
          this.tooltips[`troop_${troop.id}`].destroy();
          delete this.tooltips[`troop_${troop.id}`];
        }
      } else {
        // remove troop from hand JS array

        if (this.isSpectator == false || player_color == this.RED_COLOR) {
          // Obtenir les indices ajustés pour le tableau
          const indicesNonBloques = args.numbers_no_blocked.map((index) => index - 1); // Convertir en indices de tableau

          const indexMax = Math.max(...indicesNonBloques); // Trouver l'indice maximum (ajusté)

          // Vérifier si l'indice est valide avant de retirer l'élément
          if (indexMax >= 0 && indexMax < this.your_hand.length) {
            this.your_hand.splice(indexMax, 1); // Supprimer l'élément correspondant
          }
        } else {
          // Même logique pour my_hand
          const indicesNonBloques = args.numbers_no_blocked.map((index) => index - 1); // Convertir en indices de tableau
          const indexMax = Math.max(...indicesNonBloques);

          if (indexMax >= 0 && indexMax < this.my_hand.length) {
            this.my_hand.splice(indexMax, 1);
          }
        }
      }

      this.troops_on_bases[base_id].push(troop);
      this.createBaseTooltip(base_id);

      // animation part
      if (this.player_id == args.player_id) {
        if (this.instantaneousMode) {
          // Déplacement immédiat pour le mode instantané

          const troopElement = document.getElementById(`troop_${troop.id}`);

          const troopColor = Math.floor(troop.type / 10) - 1;
          const baseData = TB_bases[troop.location_arg];

          // Positionnement immédiat sans animation
          troopElement.style.position = "absolute";
          troopElement.style.zIndex = troop.ordre * 10;
          troopElement.style.top = troopColor === this.BLUE ? `${baseData.top}%` : `${baseData.top + 2.5}%`; // red troops are 2.5% down
          troopElement.style.left = `${baseData.left}%`;

          boardContainer.appendChild(troopElement);
        } else {
          /* animation for the active player */
          const troopElement = document.getElementById(`troop_${troop.id}`);

          troopElement.style.zIndex = troop.ordre * 10;

          const destination_id = this.isCurrentPlayerRed() ? "red_" + args.base_id : "blue_" + args.base_id;
          const destinationContainer = document.getElementById(destination_id);

          const startRect = this.getBoundingClientRectIgnoreZoom(troopElement);
          const endRect = this.getBoundingClientRectIgnoreZoom(destinationContainer);

          let deltaX = endRect.left - startRect.left;
          let deltaY = endRect.top - startRect.top;

          console.log("deltaX", deltaX);
          console.log("deltaY", deltaY);

          if (this.checkHorizontalMode()) {
            const deltaZ = deltaX;
            deltaX = deltaY;
            deltaY = -deltaZ;
          }
          console.log("deltaX", deltaX);
          console.log("deltaY", deltaY);

          // transformation
          const translateTransform = `translate(${deltaX}px, ${deltaY}px)`;

          // gets rotation, if defined
          const existingTransform = window.getComputedStyle(troopElement).transform;

          const newTransform = existingTransform !== "none" ? `${existingTransform} ${translateTransform}` : translateTransform;

          troopElement.style.transform = newTransform;

          const onTransitionEnd = () => {
            troopElement.style.transform = existingTransform;
            // absolute position on board
            troopElement.style.position = "absolute";

            const baseData = TB_bases[troop.location_arg];
            const troopColor = Math.floor(troop.type / 10) - 1;
            troopElement.style.top = troopColor == this.BLUE ? `${baseData.top}%` : `${baseData.top + 2.5}%`; // red troops are 2.5% down
            troopElement.style.left = `${baseData.left}%`;

            boardContainer.appendChild(troopElement);
            troopElement.removeEventListener("transitionend", onTransitionEnd);
          };
          troopElement.addEventListener("transitionend", onTransitionEnd);
        }
      } else {
        //troop becomes visible
        const numbersNoBlocked = args.numbers_no_blocked; // Par exemple [1, 2]
        const maxValue = Math.max(...numbersNoBlocked);
        let moving_troop_id = `${player_color_name}_troop_${maxValue}`;

        const troopElement = document.getElementById(moving_troop_id);

        const x = troop.type.toString().slice(-1);
        troopElement.style.backgroundPositionX = `-${x}00%`;

        if (this.instantaneousMode) {
          // Déplacement immédiat pour le mode instantané
          const baseData = TB_bases[troop.location_arg];
          const troopColor = Math.floor(troop.type / 10) - 1;

          troopElement.style.position = "absolute";
          troopElement.style.zIndex = troop.ordre * 10;
          troopElement.style.top = troopColor === this.BLUE ? `${baseData.top}%` : `${baseData.top + 2.5}%`; // red troops are 2.5% down
          troopElement.style.left = `${baseData.left}%`;
          troopElement.id = `troop_${troop.id}`;

          boardContainer.appendChild(troopElement);
        } else {
          // Animation pour les déplacements normaux

          const destination_id = this.isCurrentPlayerRed() ? "blue_" + args.base_id : "red_" + args.base_id;
          const destinationContainer = document.getElementById(destination_id);

          const startRect = this.getBoundingClientRectIgnoreZoom(troopElement);
          const endRect = this.getBoundingClientRectIgnoreZoom(destinationContainer);

          let deltaX = endRect.left - startRect.left;
          let deltaY = endRect.top - startRect.top;
          if (this.isSpectator === false || player_color === this.RED_COLOR) {
            deltaX = -deltaX;
            deltaY = -deltaY;
          }

          if (this.checkHorizontalMode()) {
            const deltaZ = deltaX;
            deltaX = deltaY;
            deltaY = -deltaZ;
          }

          troopElement.style.zIndex = 1000;
          const translateTransform = `translate(${deltaX}px, ${deltaY}px)`;

          const existingTransform = window.getComputedStyle(troopElement).transform;
          const newTransform = existingTransform !== "none" ? `${existingTransform} ${translateTransform}` : translateTransform;
          troopElement.style.transform = newTransform;

          const onTransitionEnd = () => {
            troopElement.style.transform = existingTransform;
            troopElement.style.zIndex = troop.ordre * 10;
            troopElement.style.position = "absolute";

            const baseData = TB_bases[troop.location_arg];
            const troopColor = Math.floor(troop.type / 10) - 1;
            troopElement.style.top = troopColor === this.BLUE ? `${baseData.top}%` : `${baseData.top + 2.5}%`; // red troops are 2.5% down
            troopElement.style.left = `${baseData.left}%`;

            troopElement.id = `troop_${troop.id}`;

            boardContainer.appendChild(troopElement);
            troopElement.removeEventListener("transitionend", onTransitionEnd);
          };
          troopElement.addEventListener("transitionend", onTransitionEnd);
        }
      }

      await this.bga.gameui.wait(this.DELAY_BATTLEFIELD);
    },

    /*********************************
     *
     *  one or two troops are drawn.
     *    ACTION DRAW
     *    Troop 1 Skully
     *    Troop 6 Star
     *    Board 3 Clouds
     *
     *********************************/

    /*********************************
     *
     *  Animation for active player
     *
     *********************************/
    async notif_drawTroopPrivate(args) {
      console.log("notif_drawTroopPrivate");
      console.log(args);

      const player_color = this.players[args.player_id].color;
      const player_color_name = player_color == this.RED_COLOR ? "red" : "blue";

      const deckId = `${player_color_name}_deck`;
      const deckContainer = document.getElementById(deckId);

      const rackId = `${player_color_name}_troops_container`;
      const rackContainer = document.getElementById(rackId);

      const addTroopToRack = (troop, index) => {
        /* Création de la troupe */
        const troopElement = document.createElement("div");
        troopElement.id = `troop_${troop.id}`;
        if (this.board_id == 9) {
          troopElement.classList.add("troop_x");
        } else {
          troopElement.classList.add("troop");
        }

        const troop_type = troop.type % 10;
        const troop_color = Math.floor(troop.type / 10) - 1;
        troopElement.style.backgroundPosition = `-${troop_type}00% -${troop_color}00%`;
        troopElement.style.top = "0px";
        troopElement.style.left = "0px";
        deckContainer.appendChild(troopElement);

        this.addCustomTooltip(troopElement.id, this.getTooltipTroopContent(troop.type, troop.id));

        /* Calcul de l'index d'insertion */
        const newTroop = { id: troop.id, type: troop.type };
        let insertIndex = this.my_hand.findIndex((t) => t.type > newTroop.type);
        if (insertIndex === -1) {
          this.my_hand.push(newTroop); // fin du tableau
        } else {
          this.my_hand.splice(insertIndex, 0, newTroop);
        }

        /* Placeholder dans le rack */
        let placeholder = document.createElement("div");
        placeholder.classList.add("troop-placeholder");
        if (player_color == this.RED_COLOR) {
          if (insertIndex === -1) {
            insertIndex = 0; // Vérification
          } else {
            insertIndex = this.my_hand.length - insertIndex - 1;
          }
        }

        if (insertIndex === rackContainer.children.length) {
          rackContainer.appendChild(placeholder);
        } else {
          rackContainer.insertBefore(placeholder, rackContainer.children[insertIndex]);
        }

        if (this.instantaneousMode) {
          // Déplacement instantané
          if (player_color == this.RED_COLOR) {
            troopElement.classList.add("board-inverted");
          }

          // Remplacement du placeholder par le troopElement
          rackContainer.replaceChild(troopElement, placeholder);

          // Appel de la prochaine troupe
          if (index + 1 < args.new_troops.length) {
            addTroopToRack(args.new_troops[index + 1], index + 1);
          }
        } else {
          // Animation
          // Calcul des positions
          const startRect = this.getBoundingClientRectIgnoreZoom(deckContainer);
          const rackRect = this.getBoundingClientRectIgnoreZoom(rackContainer);
          const targetRect = this.getBoundingClientRectIgnoreZoom(placeholder);

          let deltaX = targetRect.left - startRect.left;
          let deltaY = rackRect.top - startRect.top;

          if (this.checkHorizontalMode()) {
            deltaX = rackRect.top - startRect.top;
            deltaY = targetRect.left - startRect.left;
          }

          troopElement.style.zIndex = 1000;
          troopElement.style.position = "absolute";
          troopElement.style.transform = `translate(${deltaX}px, ${deltaY}px)`;
          troopElement.style.zIndex = 100;

          const onTransitionEnd = () => {
            troopElement.style.transform = "";
            troopElement.style.position = "";
            if (player_color == this.RED_COLOR) {
              troopElement.classList.add("board-inverted");
            }

            // Remplacement du placeholder par le troopElement
            rackContainer.replaceChild(troopElement, placeholder);

            // Nettoyage : suppression du gestionnaire
            troopElement.removeEventListener("transitionend", onTransitionEnd);

            // Appel de la prochaine animation si nécessaire
            if (index + 1 < args.new_troops.length) {
              addTroopToRack(args.new_troops[index + 1], index + 1);
            }
          };

          troopElement.addEventListener("transitionend", onTransitionEnd);
        }
      };

      if (args.new_troops.length > 0) {
        addTroopToRack(args.new_troops[0], 0);
      }
    },

    /*********************************
     *
     *  one or two troops are drawn.
     *    ACTION DRAW
     *    Troop 1 Skully
     *    Troop 6 Star
     *    Board 3 Clouds
     *
     *********************************/

    /*********************************
     *
     *  Animation for other players. active player is not affected
     *
     *********************************/

    async notif_drawTroopPublic(args) {
      console.log("notif_drawTroopPublic");
      console.log(args);

      const player_color = this.players[args.player_id].color;
      const player_color_name = player_color == this.RED_COLOR ? "red" : "blue";
      const player_color_index = player_color == this.RED_COLOR ? "2" : "1";

      const deckId = `${player_color_name}_deck`;
      const deckContainer = document.getElementById(deckId);

      const rackId = `${player_color_name}_troops_container`;
      const rackContainer = document.getElementById(rackId);

      if (player_color == this.BLUE_COLOR) {
        this.blue_deck_counter.incValue(parseInt(-args.nb_troops));
      } else {
        this.red_deck_counter.incValue(parseInt(-args.nb_troops));
      }
      // TODO EMPTY DECK

      // no changes for active player
      if (this.player_id != args.player_id) {
        const addTroopToRack = (index) => {
          /* JS troop addition */
          const newTroop = { type: player_color_index };
          if (this.isSpectator === false || player_color == this.RED_COLOR) {
            this.your_hand.push(newTroop);
          } else {
            this.my_hand.push(newTroop);
          }

          /* Création de la troupe */
          let troopElement = document.createElement("div");
          if (this.board_id == 9) {
            troopElement.classList.add("troop_x");
          } else {
            troopElement.classList.add("troop");
          }

          if (this.isCurrentPlayerRed()) {
            troopElement.id = `blue_troop_${index + parseInt(args.nb_troops_hand) + 1}`;
            troopElement.style.backgroundPosition = `-0% -0%`;
          } else if (this.isCurrentPlayerBlue()) {
            troopElement.id = `red_troop_${index + parseInt(args.nb_troops_hand) + 1}`;
            troopElement.style.backgroundPosition = `-0% -100%`;
          } else if (this.isSpectator === true) {
            // Spectator
            if (player_color == this.RED_COLOR) {
              troopElement.id = `red_troop_${index + parseInt(args.nb_troops_hand) + 1}`;
              troopElement.style.backgroundPosition = `-0% -100%`;
            } else {
              troopElement.id = `blue_troop_${index + parseInt(args.nb_troops_hand) + 1}`;
              troopElement.style.backgroundPosition = `-0% -0%`;
            }
          }
          troopElement.style.top = "0px";
          troopElement.style.left = "0px";
          deckContainer.appendChild(troopElement);

          /* Réserver une place dans le rack */
          const placeholder = document.createElement("div");
          placeholder.classList.add("troop-placeholder");
          rackContainer.appendChild(placeholder);

          if (this.instantaneousMode) {
            // Mode instantané : remplace directement le placeholder
            if (player_color == this.RED_COLOR) {
              troopElement.classList.add("board-inverted");
            }
            rackContainer.replaceChild(troopElement, placeholder);

            // Appel de la troupe suivante
            if (index + 1 < args.nb_troops) {
              addTroopToRack(index + 1);
            }
          } else {
            // Animation
            const startRect = this.getBoundingClientRectIgnoreZoom(deckContainer);
            const rackRect = this.getBoundingClientRectIgnoreZoom(rackContainer);
            const targetRect = this.getBoundingClientRectIgnoreZoom(placeholder);

            let deltaX = targetRect.left - startRect.left;
            let deltaY = rackRect.top - startRect.top;

            if (this.isSpectator === false || player_color == this.RED_COLOR) {
              deltaX = -deltaX;
              deltaY = -deltaY;
            }

            if (this.checkHorizontalMode()) {
              const deltaZ = deltaX;
              deltaX = deltaY;
              deltaY = deltaZ;
            }

            troopElement.style.zIndex = 1000;
            troopElement.style.position = "absolute";
            troopElement.style.transform = `translate(${deltaX}px, ${deltaY}px)`;
            troopElement.style.zIndex = 100;

            // Gestionnaire de transition
            const onTransitionEnd = () => {
              troopElement.style.transform = "";
              troopElement.style.position = "";
              if (player_color == this.RED_COLOR) {
                troopElement.classList.add("board-inverted");
              }

              rackContainer.replaceChild(troopElement, placeholder);

              troopElement.removeEventListener("transitionend", onTransitionEnd);

              // Appel de la troupe suivante
              if (index + 1 < args.nb_troops) {
                addTroopToRack(index + 1);
              }
            };

            troopElement.addEventListener("transitionend", onTransitionEnd);
          }
        };

        if (args.nb_troops > 0) {
          addTroopToRack(0);
        }
      }
    },

    /*********************************
     *
     *  a chosen troop from the board is discarded
     *    Troop 3 Mastok
     *
     ************************************/

    async notif_discardTroopFromBoard(args) {
      console.log("notif_discardTroopFromBoard");
      console.log(args);

      const troop = args.infos_troop;

      const player_color = this.players[troop.type_arg].color;
      const player_color_name = player_color == this.RED_COLOR ? "red" : "blue";

      // array this.troops_on_bases and base tooltip are updated
      const base_id = troop.location_arg;

      let base_troops = this.troops_on_bases[base_id];
      const index = base_troops.findIndex((t) => t.id === troop.id);

      if (index !== -1) {
        this.troops_on_bases[base_id].splice(index, 1);
        if (base_troops.length > 0) {
          this.createBaseTooltip(base_id);
        } else {
          this.destroyBaseTooltip(base_id);
        }
      }
      this.addCustomTooltip(`troop_${troop.id}`, this.getTooltipTroopContent(troop.type, troop.id));

      /* check where to insert the troop */
      const newTroop = { id: troop.id, type: troop.type };
      let insertIndex;

      if ((player_color == this.BLUE_COLOR && (this.isSpectator == true || this.isCurrentPlayerBlue())) || (player_color == this.RED_COLOR && this.isCurrentPlayerRed())) {
        insertIndex = this.my_discard.findIndex((t) => t.type > newTroop.type);
        if (insertIndex === -1) {
          this.my_discard.push(newTroop); // end of array
        } else {
          this.my_discard.splice(insertIndex, 0, newTroop);
        }
      } else {
        insertIndex = this.your_discard.findIndex((t) => t.type > newTroop.type);
        if (insertIndex === -1) {
          this.your_discard.push(newTroop); // end of array
        } else {
          this.your_discard.splice(insertIndex, 0, newTroop);
        }
      }

      /* room is reserved in the flex */

      let placeholder = document.createElement("div");
      placeholder.classList.add("troop-placeholder");

      const troopElement = document.getElementById(`troop_${troop.id}`);

      const discardId = `${player_color_name}_discard`;
      const discardContainer = document.getElementById(discardId);

      if (player_color == this.RED_COLOR) {
        if (insertIndex === 0) {
          discardContainer.appendChild(placeholder);
        } else if (insertIndex === -1) {
          insertIndex = 0;
          discardContainer.insertBefore(placeholder, discardContainer.children[insertIndex]);
        } else {
          discardContainer.insertBefore(placeholder, discardContainer.children[insertIndex]);
        }
      } else {
        if (insertIndex === discardContainer.children.length) {
          discardContainer.appendChild(placeholder);
        } else {
          discardContainer.insertBefore(placeholder, discardContainer.children[insertIndex]);
        }
      }

      if (this.instantaneousMode) {
        // Déplacement direct sans animation
        troopElement.style.transform = "";
        troopElement.style.top = "";
        troopElement.style.left = "";
        troopElement.style.position = "";
        troopElement.style.zIndex = 10;
        troopElement.classList.add("opa_70");
        discardContainer.replaceChild(troopElement, placeholder);
      } else {
        // Animation de la troupe
        const startRect = this.getBoundingClientRectIgnoreZoom(troopElement);
        const targetRect = this.getBoundingClientRectIgnoreZoom(placeholder);

        let deltaX = targetRect.left - startRect.left;
        let deltaY = targetRect.top - startRect.top;

        if (!this.isCurrentPlayerRed() && player_color == this.RED_COLOR) {
          deltaX = -deltaX;
          deltaY = -deltaY;
        } else if (this.isCurrentPlayerRed() && player_color == this.BLUE_COLOR) {
          deltaX = -deltaX;
          deltaY = -deltaY;
        }

        if (this.checkHorizontalMode()) {
          const deltaZ = deltaX;
          deltaX = deltaY;
          deltaY = -deltaZ;
        }

        const translateTransform = `translate(${deltaX}px, ${deltaY}px)`;

        const existingTransform = window.getComputedStyle(troopElement).transform;
        const newTransform = existingTransform !== "none" ? `${existingTransform} ${translateTransform}` : translateTransform;

        troopElement.style.transform = newTransform;

        const onTransitionEnd = () => {
          troopElement.style.transform = "";
          troopElement.style.top = "";
          troopElement.style.left = "";
          troopElement.style.position = "";
          troopElement.style.zIndex = 10;
          troopElement.classList.add("opa_70");
          discardContainer.replaceChild(troopElement, placeholder);

          troopElement.removeEventListener("transitionend", onTransitionEnd);
        };
        troopElement.addEventListener("transitionend", onTransitionEnd);
      }

      await this.bga.gameui.wait(this.DELAY_JUNGLE);
    },

    /*********************************
     *
     *  a chosen troop from the opponent's rack is discarded
     *    Troop 5 XB-42
     *
     ************************************/

    async notif_discardTroopFromHand(args) {
      console.log("notif_discardTroopFromHand");
      console.log(args);

      const troop = args.infos_troop;

      const player_color = this.players[troop.type_arg].color;
      const player_color_name = player_color == this.RED_COLOR ? "red" : "blue";

      const discardId = `${player_color_name}_discard`;
      const discardContainer = document.getElementById(discardId);

      /* check where to remove to troop */

      if (troop.type_arg == this.player_id) {
        /* troop is removed from hand JS array */
        const index = this.my_hand.findIndex((t) => t.id === troop.id);
        if (index !== -1) {
          this.my_hand.splice(index, 1);

          //remove tooltip
          this.tooltips[`troop_${troop.id}`].destroy();
          delete this.tooltips[`troop_${troop.id}`];
        }
      } else {
        // remove troop from hand JS array
        if (this.isSpectator == false || player_color == this.RED_COLOR) {
          this.your_hand.pop();
        } else {
          // blue spectator
          this.my_hand.pop();
        }
      }

      /* check where to insert the troop */
      const newTroop = { id: troop.id, type: troop.type };
      let insertIndex;

      if ((player_color == this.BLUE_COLOR && (this.isSpectator == true || this.isCurrentPlayerBlue())) || (player_color == this.RED_COLOR && this.isCurrentPlayerRed())) {
        insertIndex = this.my_discard.findIndex((t) => t.type > newTroop.type);
        if (insertIndex === -1) {
          this.my_discard.push(newTroop); // end of array
        } else {
          this.my_discard.splice(insertIndex, 0, newTroop);
        }
      } else {
        insertIndex = this.your_discard.findIndex((t) => t.type > newTroop.type);
        if (insertIndex === -1) {
          this.your_discard.push(newTroop); // end of array
        } else {
          this.your_discard.splice(insertIndex, 0, newTroop);
        }
      }

      /* room is reserved in the flex */
      let placeholder = document.createElement("div");
      placeholder.classList.add("troop-placeholder");
      if (player_color == this.RED_COLOR) {
        if (insertIndex === -1) {
          insertIndex = 0; // TODO vérifier
        } else {
          insertIndex = this.your_discard.length - insertIndex - 1; //TODO vérifier le bon index
        }
      }

      /* troop in hand to remove is defined*/

      if (this.player_id == troop.type_arg) {
        const troopElement = document.getElementById(`troop_${troop.id}`);

        if (insertIndex === discardContainer.children.length) {
          discardContainer.appendChild(placeholder);
        } else {
          discardContainer.insertBefore(placeholder, discardContainer.children[insertIndex]);
        }

        if (this.instantaneousMode) {
          // Déplacement direct sans animation
          troopElement.style.transform = "";
          troopElement.style.top = "";
          troopElement.style.left = "";
          troopElement.style.position = "";
          troopElement.style.zIndex = 10;
          troopElement.classList.add("opa_70");
          discardContainer.replaceChild(troopElement, placeholder);
        } else {
          const startRect = this.getBoundingClientRectIgnoreZoom(troopElement);
          const endRect = this.getBoundingClientRectIgnoreZoom(placeholder);

          let deltaX = endRect.left - startRect.left;
          let deltaY = endRect.top - startRect.top;
          console.log("deltaX", deltaX);
          console.log("deltaY", deltaY);

          if (this.isSpectator == false) {
            deltaX = -deltaX;
          }

          if (this.checkHorizontalMode()) {
            const deltaZ = deltaX;
            deltaX = deltaY;
            deltaY = deltaZ;
          }

          const existingTransform = window.getComputedStyle(troopElement).transform;
          const translateTransform = `translate(${deltaX}px, ${deltaY}px)`;
          const newTransform = existingTransform !== "none" ? `${existingTransform} ${translateTransform}` : translateTransform;
          troopElement.style.transform = newTransform;

          const onTransitionEnd = () => {
            //troopElement.style.transform = existingTransform;
            troopElement.style.transform = "";
            troopElement.style.top = "";
            troopElement.style.left = "";
            troopElement.style.position = "";
            troopElement.style.zIndex = 10; // ATTENTION POUR LES PROCHAINES ACTIONS
            troopElement.classList.add("opa_70");
            discardContainer.replaceChild(troopElement, placeholder);

            troopElement.removeEventListener("transitionend", onTransitionEnd);
          };
          troopElement.addEventListener("transitionend", onTransitionEnd);
        }
      } else {
        // rename Troop id and unhide it
        // remove troop from hand JS array
        if (this.isSpectator == false || player_color == this.RED_COLOR) {
          this.your_hand.pop();
        } else {
          this.my_hand.pop();
        }

        let selected_troop = args.selected_troop;

        if (selected_troop == 0) {
          selected_troop = args.nb_cards_in_hand;
        }

        let moving_troop_id = `${player_color_name}_troop_${selected_troop}`;

        const troopElement = document.getElementById(moving_troop_id);

        troopElement.id = `troop_${troop.id}`;
        const x = troop.type.toString().slice(-1);
        troopElement.style.backgroundPositionX = `-${x}00%`;

        if (insertIndex === discardContainer.children.length) {
          discardContainer.appendChild(placeholder);
        } else {
          discardContainer.insertBefore(placeholder, discardContainer.children[insertIndex]);
        }

        if (this.instantaneousMode) {
          // Déplacement direct sans animation
          troopElement.style.transform = "";
          troopElement.style.top = "";
          troopElement.style.left = "";
          troopElement.style.position = "";
          troopElement.style.zIndex = 10;
          troopElement.classList.add("opa_70");
          discardContainer.replaceChild(troopElement, placeholder);

          for (let i = parseInt(selected_troop) + 1; i <= args.nb_cards_in_hand; i++) {
            const troop_id = `${player_color_name}_troop_${i}`;
            let troopElement = document.getElementById(troop_id);
            troopElement.id = `${player_color_name}_troop_${i - 1}`;
          }
        } else {
          const startRect = this.getBoundingClientRectIgnoreZoom(troopElement);
          const endRect = this.getBoundingClientRectIgnoreZoom(placeholder);

          let deltaX = endRect.left - startRect.left;
          let deltaY = endRect.top - startRect.top;

          if (this.isSpectator == false || player_color == this.RED_COLOR) {
            deltaX = -deltaX;
            deltaY = -deltaY;
          }

          if (this.checkHorizontalMode()) {
            const deltaZ = deltaX;
            deltaX = deltaY;
            deltaY = -deltaZ;
          }

          const existingTransform = window.getComputedStyle(troopElement).transform;
          const translateTransform = `translate(${deltaX}px, ${deltaY}px)`;
          const newTransform = existingTransform !== "none" ? `${existingTransform} ${translateTransform}` : translateTransform;
          troopElement.style.transform = newTransform;

          const onTransitionEnd = () => {
            //troopElement.style.transform = existingTransform;
            troopElement.style.transform = "";
            troopElement.style.top = "";
            troopElement.style.left = "";
            troopElement.style.position = "";
            troopElement.style.zIndex = 10; // ATTENTION POUR LES PROCHAINES ACTIONS
            troopElement.classList.add("opa_70");
            discardContainer.replaceChild(troopElement, placeholder);

            for (let i = parseInt(selected_troop) + 1; i <= args.nb_cards_in_hand; i++) {
              const troop_id = `${player_color_name}_troop_${i}`;
              let troopElement = document.getElementById(troop_id);
              troopElement.id = `${player_color_name}_troop_${i - 1}`;
            }

            troopElement.removeEventListener("transitionend", onTransitionEnd);
          };

          troopElement.addEventListener("transitionend", onTransitionEnd);
        }
      }
      this.addCustomTooltip(`troop_${troop.id}`, this.getTooltipTroopContent(troop.type, troop.id));

      await this.bga.gameui.wait(this.DELAY_BATTLEFIELD);
    },

    /*********************************
     *
     *  a chosen Troop goes from board to rack
     *    Castle Base
     *
     ************************************/

    async notif_recoverTroopFromBoard(args) {
      console.log("notif_recoverTroopFromBoard");
      console.log(args);

      const troop = args.infos_troop;

      const player_color = this.players[troop.type_arg].color;
      const player_color_name = player_color == this.RED_COLOR ? "red" : "blue";
      const player_color_index = player_color == this.RED_COLOR ? "2" : "1";

      const troopElement = document.getElementById(`troop_${troop.id}`);

      const rackId = `${player_color_name}_troops_container`;
      const rackContainer = document.getElementById(rackId);

      const base_id = troop.location_arg;
      let base_troops = this.troops_on_bases[base_id];
      const index = base_troops.findIndex((t) => t.id === troop.id);
      if (index !== -1) {
        this.troops_on_bases[base_id].splice(index, 1);
        if (base_troops.length > 0) {
          this.createBaseTooltip(base_id);
        } else {
          this.destroyBaseTooltip(base_id);
        }
      }

      if (troop.type_arg == this.player_id) {
        /* check where to insert the troop */
        const newTroop = { id: troop.id, type: troop.type };
        let insertIndex = this.my_hand.findIndex((t) => t.type > newTroop.type);
        if (insertIndex === -1) {
          this.my_hand.push(newTroop); // end of array
        } else {
          this.my_hand.splice(insertIndex, 0, newTroop);
        }

        /* room is reserved in the flex */
        let placeholder = document.createElement("div");
        placeholder.classList.add("troop-placeholder");
        if (player_color == this.RED_COLOR) {
          if (insertIndex === -1) {
            insertIndex = 0;
          } else {
            insertIndex = this.my_hand.length - insertIndex - 1;
          }
        }

        if (insertIndex === rackContainer.children.length) {
          rackContainer.appendChild(placeholder);
        } else {
          rackContainer.insertBefore(placeholder, rackContainer.children[insertIndex]);
        }

        if (this.instantaneousMode) {
          troopElement.style.transform = "";
          troopElement.style.top = "";
          troopElement.style.left = "";
          troopElement.style.position = "";
          troopElement.style.zIndex = 10;

          rackContainer.replaceChild(troopElement, placeholder);
        } else {
          const startRect = this.getBoundingClientRectIgnoreZoom(troopElement);
          const targetRect = this.getBoundingClientRectIgnoreZoom(placeholder);

          let deltaX = targetRect.left - startRect.left;
          let deltaY = targetRect.top - startRect.top;

          if (this.checkHorizontalMode()) {
            const deltaZ = deltaX;
            deltaX = deltaY;
            deltaY = -deltaZ;
          }

          const existingTransform = window.getComputedStyle(troopElement).transform;
          const translateTransform = `translate(${deltaX}px, ${deltaY}px)`;
          const newTransform = existingTransform !== "none" ? `${existingTransform} ${translateTransform}` : translateTransform;
          troopElement.style.transform = newTransform;

          const onTransitionEnd = () => {
            troopElement.style.transform = "";
            troopElement.style.top = "";
            troopElement.style.left = "";
            troopElement.style.position = "";
            troopElement.style.zIndex = 10;

            rackContainer.replaceChild(troopElement, placeholder);

            troopElement.removeEventListener("transitionend", onTransitionEnd);
          };

          troopElement.addEventListener("transitionend", onTransitionEnd);
        }

        this.addCustomTooltip(troopElement.id, this.getTooltipTroopContent(troop.type, troop.id));
      } else {
        const newTroop = { type: player_color_index };
        if (this.isSpectator == false || player_color == this.RED_COLOR) {
          this.your_hand.push(newTroop);
        } else {
          // blue spectator
          this.my_hand.push(newTroop);
        }

        const placeholder = document.createElement("div");
        placeholder.classList.add("troop-placeholder");
        rackContainer.appendChild(placeholder);

        if (this.instantaneousMode) {
          troopElement.style.transform = "";
          troopElement.style.top = "";
          troopElement.style.left = "";
          troopElement.style.position = "";
          troopElement.style.zIndex = 10;
          if (player_color == this.RED_COLOR) {
            troopElement.classList.add("board-inverted");
          }

          // troop in rack is renamed
          const troop_index = parseInt(args.nb_troops_hand) + 1;
          if (this.isCurrentPlayerRed()) {
            troopElement.id = `blue_troop_${troop_index}`;
            troopElement.style.backgroundPosition = `-0% -0%`;
          } else if (this.isCurrentPlayerBlue()) {
            troopElement.id = `red_troop_${troop_index}`;
            troopElement.style.backgroundPosition = `-0% -100%`;
          } else if (this.isSpectator == true) {
            // spectator
            if (player_color == this.RED_COLOR) {
              troopElement.id = `red_troop_${troop_index}`;
              troopElement.style.backgroundPosition = `-0% -100%`;
            } else {
              troopElement.id = `blue_troop_${troop_index}`;
              troopElement.style.backgroundPosition = `-0% -0%`;
            }
          }

          rackContainer.replaceChild(troopElement, placeholder);
        } else {
          const startRect = this.getBoundingClientRectIgnoreZoom(troopElement);
          const targetRect = this.getBoundingClientRectIgnoreZoom(placeholder);

          let deltaX = targetRect.left - startRect.left;
          let deltaY = targetRect.top - startRect.top;

          if (this.isSpectator == false || player_color == this.RED_COLOR) {
            deltaX = -deltaX;
            deltaY = -deltaY;
          }

          if (this.checkHorizontalMode()) {
            const deltaZ = deltaX;
            deltaX = deltaY;
            deltaY = -deltaZ;
          }

          troopElement.style.zIndex = 100;

          const existingTransform = window.getComputedStyle(troopElement).transform;
          const translateTransform = `translate(${deltaX}px, ${deltaY}px)`;
          const newTransform = existingTransform !== "none" ? `${existingTransform} ${translateTransform}` : translateTransform;
          troopElement.style.transform = newTransform;

          troopElement.style.zIndex = 10;

          const onTransitionEnd = () => {
            troopElement.style.transform = "";
            troopElement.style.top = "";
            troopElement.style.left = "";
            troopElement.style.position = "";

            if (player_color == this.RED_COLOR) {
              troopElement.classList.add("board-inverted");
            }

            // troop in rack is renamed
            const troop_index = parseInt(args.nb_troops_hand) + 1;
            if (this.isCurrentPlayerRed()) {
              troopElement.id = `blue_troop_${troop_index}`;
              troopElement.style.backgroundPosition = `-0% -0%`;
            } else if (this.isCurrentPlayerBlue()) {
              troopElement.id = `red_troop_${troop_index}`;
              troopElement.style.backgroundPosition = `-0% -100%`;
            } else if (this.isSpectator == true) {
              // spectator
              if (player_color == this.RED_COLOR) {
                troopElement.id = `red_troop_${troop_index}`;
                troopElement.style.backgroundPosition = `-0% -100%`;
              } else {
                troopElement.id = `blue_troop_${troop_index}`;
                troopElement.style.backgroundPosition = `-0% -0%`;
              }
            }

            rackContainer.replaceChild(troopElement, placeholder);
            troopElement.removeEventListener("transitionend", onTransitionEnd);
          };

          troopElement.addEventListener("transitionend", onTransitionEnd);
        }
      }
    },

    /*********************************
     *
     *  a chosen troop goes back from the cemetery to rack
     *    Cemetery base
     *
     ***********************************/

    async notif_recoverTroopFromDiscard(args) {
      console.log("notif_recoverTroopFromDiscard");
      console.log(args);

      const troop = args.infos_troop;

      const player_color = this.players[troop.type_arg].color;
      const player_color_name = player_color == this.RED_COLOR ? "red" : "blue";
      const player_color_index = player_color == this.RED_COLOR ? "2" : "1";

      const troopElement = document.getElementById(`troop_${troop.id}`);
      dojo.removeClass(`troop_${troop.id}`, "opa_70");

      const rackId = `${player_color_name}_troops_container`;
      const rackContainer = document.getElementById(rackId);

      if (troop.type_arg == this.player_id) {
        const newTroop = { id: troop.id, type: troop.type };

        let insertIndex = this.my_hand.findIndex((t) => t.type > newTroop.type);
        if (insertIndex === -1) {
          this.my_hand.push(newTroop); // end of array
        } else {
          this.my_hand.splice(insertIndex, 0, newTroop);
        }

        let placeholder = document.createElement("div");
        placeholder.classList.add("troop-placeholder");
        if (player_color == this.RED_COLOR) {
          if (insertIndex === -1) {
            insertIndex = 0; // TODO vérifier
          } else {
            insertIndex = this.my_hand.length - insertIndex - 1; //TODO vérifier le bon index
          }
        }

        if (insertIndex === rackContainer.children.length) {
          rackContainer.appendChild(placeholder);
        } else {
          rackContainer.insertBefore(placeholder, rackContainer.children[insertIndex]);
        }

        if (this.instantaneousMode) {
          troopElement.style.transform = "";
          troopElement.style.top = "";
          troopElement.style.left = "";
          troopElement.style.position = "";
          troopElement.style.zIndex = 10;

          rackContainer.replaceChild(troopElement, placeholder);
        } else {
          const startRect = this.getBoundingClientRectIgnoreZoom(troopElement);
          const targetRect = this.getBoundingClientRectIgnoreZoom(placeholder);

          let deltaX = targetRect.left - startRect.left;
          let deltaY = targetRect.top - startRect.top;

          if (this.checkHorizontalMode()) {
            const deltaZ = deltaX;
            deltaX = deltaY;
            deltaY = -deltaZ;
          }

          const existingTransform = window.getComputedStyle(troopElement).transform;
          const translateTransform = `translate(${deltaX}px, ${deltaY}px)`;
          const newTransform = existingTransform !== "none" ? `${existingTransform} ${translateTransform}` : translateTransform;

          troopElement.style.transform = newTransform;

          const onTransitionEnd = () => {
            troopElement.style.transform = "";
            troopElement.style.top = "";
            troopElement.style.left = "";
            troopElement.style.position = "";
            troopElement.style.zIndex = 10;

            rackContainer.replaceChild(troopElement, placeholder);

            troopElement.removeEventListener("transitionend", onTransitionEnd);
          };

          troopElement.addEventListener("transitionend", onTransitionEnd);
        }
      } else {
        const newTroop = { type: player_color_index };
        if (this.isSpectator == false || player_color == this.RED_COLOR) {
          this.your_hand.push(newTroop);
        } else {
          // blue spectator
          this.my_hand.push(newTroop);
        }

        const placeholder = document.createElement("div");
        placeholder.classList.add("troop-placeholder");
        rackContainer.appendChild(placeholder);

        if (this.instantaneousMode) {
          troopElement.style.transform = "";
          troopElement.style.top = "";
          troopElement.style.left = "";
          troopElement.style.position = "";
          troopElement.style.zIndex = 10;
          if (player_color == this.RED_COLOR) {
            troopElement.classList.add("board-inverted");
          }

          const troop_index = parseInt(args.nb_troops_hand) + 1;
          if (this.isCurrentPlayerRed()) {
            troopElement.id = `blue_troop_${troop_index}`;
            troopElement.style.backgroundPosition = `-0% -0%`;
          } else if (this.isCurrentPlayerBlue()) {
            troopElement.id = `red_troop_${troop_index}`;
            troopElement.style.backgroundPosition = `-0% -100%`;
          } else if (this.isSpectator == true) {
            // spectator
            if (player_color == this.RED_COLOR) {
              troopElement.id = `red_troop_${troop_index}`;
              troopElement.style.backgroundPosition = `-0% -100%`;
            } else {
              troopElement.id = `blue_troop_${troop_index}`;
              troopElement.style.backgroundPosition = `-0% -0%`;
            }
          }

          rackContainer.replaceChild(troopElement, placeholder);
        } else {
          const startRect = this.getBoundingClientRectIgnoreZoom(troopElement);
          const targetRect = this.getBoundingClientRectIgnoreZoom(placeholder);

          let deltaX = targetRect.left - startRect.left;
          let deltaY = targetRect.top - startRect.top;

          if (this.isSpectator == false || player_color == this.RED_COLOR) {
            deltaX = -deltaX;
            deltaY = -deltaY;
          }

          if (this.checkHorizontalMode()) {
            const deltaZ = deltaX;
            deltaX = deltaY;
            deltaY = -deltaZ;
          }

          const existingTransform = window.getComputedStyle(troopElement).transform;
          const translateTransform = `translate(${deltaX}px, ${deltaY}px)`;
          const newTransform = existingTransform !== "none" ? `${existingTransform} ${translateTransform}` : translateTransform;

          troopElement.style.transform = newTransform;
          troopElement.style.zIndex = 100;

          const onTransitionEnd = () => {
            troopElement.style.transform = "";
            troopElement.style.top = "";
            troopElement.style.left = "";
            troopElement.style.position = "";
            troopElement.style.zIndex = 10;
            if (player_color == this.RED_COLOR) {
              troopElement.classList.add("board-inverted");
            }

            const troop_index = parseInt(args.nb_troops_hand) + 1;
            if (this.isCurrentPlayerRed()) {
              troopElement.id = `blue_troop_${troop_index}`;
              troopElement.style.backgroundPosition = `-0% -0%`;
            } else if (this.isCurrentPlayerBlue()) {
              troopElement.id = `red_troop_${troop_index}`;
              troopElement.style.backgroundPosition = `-0% -100%`;
            } else if (this.isSpectator == true) {
              // spectator
              if (player_color == this.RED_COLOR) {
                troopElement.id = `red_troop_${troop_index}`;
                troopElement.style.backgroundPosition = `-0% -100%`;
              } else {
                troopElement.id = `blue_troop_${troop_index}`;
                troopElement.style.backgroundPosition = `-0% -0%`;
              }
            }

            rackContainer.replaceChild(troopElement, placeholder);

            troopElement.removeEventListener("transitionend", onTransitionEnd);
          };

          troopElement.addEventListener("transitionend", onTransitionEnd);
        }
      }
    },

    /*********************************
     *
     *  a chosen troop moves from board to board
     *    Volcano Base
     *
     ***********************************/

    async notif_moveTroopBoardToBoard(args) {
      console.log("notif_moveTroopBoardToBoard");
      console.log(args);

      const troop = args.infos_troop;
      const troop_after = args.infos_troop_after;

      const player_color = this.players[troop.type_arg].color;
      const player_color_name = player_color == this.RED_COLOR ? "red" : "blue";
      const player_color_index = player_color == this.RED_COLOR ? "2" : "1";

      const troopElement = document.getElementById(`troop_${troop.id}`);
      troopElement.style.zIndex = args.ordre * 10;

      // modify troop_before
      const base_id = troop.location_arg;
      let base_troops = this.troops_on_bases[base_id];
      const index = base_troops.findIndex((t) => t.id === troop.id);
      if (index !== -1) {
        this.troops_on_bases[base_id].splice(index, 1);
        if (base_troops.length > 0) {
          this.createBaseTooltip(base_id);
        } else {
          this.destroyBaseTooltip(base_id);
        }
      }

      // modify trrop_after
      this.troops_on_bases[args.base_id].push(troop_after);
      this.createBaseTooltip(args.base_id);

      const destination_id = `${player_color_name}_base_${this.board_name}_${args.base_id}`;
      const destinationContainer = document.getElementById(destination_id);

      if (this.instantaneousMode) {
        troopElement.style.top = destinationContainer.style.top;
        troopElement.style.left = destinationContainer.style.left;
        troopElement.style.transition = "none";
        troopElement.style.transform = existingTransform;
      } else {
        const startRect = this.getBoundingClientRectIgnoreZoom(troopElement);
        const endRect = this.getBoundingClientRectIgnoreZoom(destinationContainer);

        let deltaX = endRect.left - startRect.left;
        let deltaY = endRect.top - startRect.top;

        if (this.isCurrentPlayerRed() && player_color == this.BLUE_COLOR) {
          deltaX = -deltaX;
          deltaY = -deltaY;
        } else if (this.isCurrentPlayerBlue() && player_color == this.RED_COLOR) {
          deltaX = -deltaX;
          deltaY = -deltaY;
        } else if (this.isSpectator && player_color == this.RED_COLOR) {
          deltaX = -deltaX;
          deltaY = -deltaY;
        }

        if (this.checkHorizontalMode()) {
          const deltaZ = deltaX;
          deltaX = deltaY;
          deltaY = -deltaZ;
        }

        const existingTransform = window.getComputedStyle(troopElement).transform;
        const translateTransform = `translate(${deltaX}px, ${deltaY}px)`;
        const newTransform = existingTransform !== "none" ? `${existingTransform} ${translateTransform}` : translateTransform;

        troopElement.style.transform = newTransform;

        const onTransitionEnd = () => {
          troopElement.style.top = destinationContainer.style.top;
          troopElement.style.left = destinationContainer.style.left;
          troopElement.style.transition = "none";
          troopElement.style.transform = existingTransform;

          troopElement.removeEventListener("transitionend", onTransitionEnd);
        };

        troopElement.addEventListener("transitionend", onTransitionEnd);
      }
    },

    /*********************************
     *
     *  a chosen troop is hidden for next round
     *    Battlefield Base
     *
     ***********************************/

    async notif_hideTroopOnRackPrivate(args) {
      console.log("notif_hideTroopOnRackPrivate");
      console.log(args);

      const player_color = this.players[args.player_id].color;
      const other_player_color_name = player_color == this.RED_COLOR ? "blue" : "red";
      const check_name = player_color == this.RED_COLOR ? "check_red" : "check_blue";

      const troop = args.infos_troop_before;

      const troopElement = document.getElementById(`troop_${troop.id}`);

      const checkElement = document.createElement("div");
      checkElement.id = `check_${troop.id}`;
      checkElement.classList.add("checks", check_name);

      troopElement.appendChild(checkElement);
    },

    async notif_hideTroopOnRackPublic(args) {
      console.log("notif_hideTroopOnRackPublic");
      console.log(args);

      if (args.player_id != this.opponent_id || this.isSpectator) {
        const player_color = this.players[args.player_id].color;
        const other_player_color_name = player_color == this.RED_COLOR ? "blue" : "red";
        const check_name = player_color == this.RED_COLOR ? "check_red" : "check_blue";

        const troopElement = document.getElementById(`${other_player_color_name}_troop_${args.card_blocked}`);

        const checkElement = document.createElement("div");
        checkElement.id = `check_${args.card_blocked}`;
        checkElement.classList.add("checks", check_name);
        troopElement.appendChild(checkElement);
      }
    },

    async notif_unhideTroopOnRack(args) {
      console.log("notif_unhideTroopOnRack");
      console.log(args);

      const player_color = this.players[args.player_id].color;
      const check_name = player_color == this.RED_COLOR ? "check_blue" : "check_red";

      const elements = document.querySelectorAll(`.${check_name}`);
      elements.forEach((element) => {
        element.remove();
      });

      const nb_troops_hand = args.nb_troops_hand;
      console.log("nb_troops_hand", nb_troops_hand);
      // TODO utiliser nb_cards_in_hands au lieu du front.

      const rack_name = player_color == this.RED_COLOR ? "red_rack" : "blue_rack";

      if (rack_name == "red_rack" && this.isCurrentPlayerRed() == false) {
        const rackElement = document.getElementById("red_troops_container");
        let count = 1;
        const children = Array.from(rackElement.children);
        children.forEach((child) => {
          if (count <= nb_troops_hand) {
            child.id = `red_troop_${count}`;
            count++;
          }
        });
      }

      if (rack_name == "blue_rack" && this.isCurrentPlayerBlue() == false) {
        const rackElement = document.getElementById("blue_troops_container");
        let count = 1;
        const children = Array.from(rackElement.children);
        children.forEach((child) => {
          if (count <= nb_troops_hand) {
            child.id = `blue_troop_${count}`;
            count++;
          }
        });
      }
    },

    /*********************************
     *
     *  Regions are occupied and Medals are won
     *
     ************************************/

    async notif_gainMedal(args) {
      console.log("notif_gainMedal");
      console.log(args);

      let medals_already_won = parseInt(args.medals_already_won);
      let index = 1;
      const TB_medals = this.medals;

      const medals_needed = parseInt(this.medals_to_win[this.board_id - 1]);

      const goodieContainer = document.getElementById("goodie_" + this.board_id);

      const player_color = this.players[args.player_id].color;

      let player_indice = player_color == this.BLUE_COLOR ? 1 : 2;

      if (this.instantaneousMode) {
        let html = "";
        const goodiesForLevel = this.goodies[medals_needed];

        Object.entries(TB_medals).forEach(([id, medal]) => {
          if (args.emptied_regions.includes(medal.region)) {
            const medalElement = document.getElementById(`medal_${id}`);

            const indice = parseInt(medals_already_won) + index;

            if (indice === medals_needed) {
              player_indice = 3;
            }

            if (indice <= medals_needed) {
              const goodie_id = `${player_indice}${indice}`;
              const g = goodiesForLevel[goodie_id];

              html += `<div id="goodie_${goodie_id}" class="medals board_medal" 
                style="position:absolute;top:${g.top}%;left:${g.left}%;z-index:10;"></div>`;
            }

            medalElement?.remove();
            index++;
          }
        });

        if (html) {
          goodieContainer.insertAdjacentHTML("beforeend", html);
        }
      } else {
        const timeoutDelay = 200;

        Object.entries(TB_medals).forEach(async ([id, medal]) => {
          if (args.emptied_regions.includes(medal.region)) {
            const medalElement = document.getElementById(`medal_${id}`);
            const indice = parseInt(medals_already_won) + index;

            if (indice === medals_needed) player_indice = 3;

            const animationDelay = index * 500;
            index++;

            await this.bga.gameui.wait(animationDelay);

            // Étape 1 : agrandir la médaille source
            medalElement.style.transform = "scale(4)";
            await this.bga.gameui.wait(timeoutDelay);

            // Étape 2 : réduire pour disparition
            medalElement.style.transform = "scale(0)";
            await this.bga.gameui.wait(timeoutDelay);

            if (indice <= medals_needed) {
              const goodie_id = `${player_indice}${indice}`;
              const goodie = this.goodies[medals_needed][goodie_id];

              // Étape 3 : créer et insérer le goodie
              goodieContainer.insertAdjacentHTML(
                "beforeend",
                `<div id="goodie_${goodie_id}" class="medals board_medal"
                  style="position:absolute;top:${goodie.top}%;left:${goodie.left}%;z-index:10;"></div>`,
              );

              const goodieElement = document.getElementById(`goodie_${goodie_id}`);

              // Étape 4 : agrandir puis revenir à la taille normale
              goodieElement.style.transform = "scale(3)";
              await this.bga.gameui.wait(timeoutDelay);
              goodieElement.style.transform = "scale(1)";
            }
          }
        });
      }
    },

    async notif_score(args) {
      console.log("notif_score");
      console.log(args);

      this.scoreCtrl[args.playerid].toValue(args.score);
    },

    async notif_victory(args) {
      console.log("notif_victory");
      console.log(args);

      this.animateVictory(args.typevictory, args.colorvictory, args.troopvictory);
    },

    animateVictory: function (type, color, troop0) {
      const container = document.querySelector("#global_big_id");
      if (!container) return;

      const victoryElement = document.createElement("div");
      victoryElement.classList.add("victory");
      //victoryElement.style.position = 'relative'; // Permet le positionnement interne

      let x = 0;
      let y = 0;

      if (type == 1) {
        // medals
        y = 4;
        x = color == "blue" ? 0 : 1;
      } else {
        const troop = parseInt(troop0) - 1;
        x = troop % 4;
        y = color == "blue" ? 0 : 1;
        y += 2 * Math.floor(troop / 4);
      }
      victoryElement.style.backgroundPosition = `-${x}00% -${y}00%`;

      const victoryTitle = document.createElement("div");
      victoryTitle.classList.add("tooltip_content");
      victoryTitle.innerHTML = `<span class='victory_title'>${_("Victory!")}</span>`;

      const victoryDescription = document.createElement("div");
      victoryDescription.classList.add("tooltip_content");
      if (type == 2) {
        victoryDescription.innerHTML = `<span class='victory_description'>${_("H.Q. captured")}</span>`;
      } else {
        victoryDescription.innerHTML = `<span class='victory_description'>${_("Terrain conquered")}</span>`;
      }

      victoryElement.appendChild(victoryTitle);
      victoryElement.appendChild(victoryDescription);
      container.appendChild(victoryElement);

      // Force a reflow before animation
      victoryElement.offsetHeight;

      setTimeout(() => {
        victoryElement.style.transform = "translate(-50%, -50%) scale(1)";
      }, 100);

      setTimeout(() => {
        victoryElement.style.opacity = "0"; // Disparition progressive
      }, 4000);

      setTimeout(() => {
        victoryElement.remove(); // Suppression de l'élément du DOM
      }, 4500); // Attendre que la transition d'opacité soit terminée
    },

    async notif_message_allplayers_without_player(args) {
      console.log("notif_message_allplayers_without_player");

      // juste un message envoyé en php
    },
  });
});
