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


define([
    "dojo","dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter",
],
function (dojo, declare, counter) {
  return declare("bgagame.toybattle",  ebg.core.gamegui, {

    constructor: function(){ console.log('toybattle constructor'); },
       
       
/////////////////////////////////////////////////////////////////////////////////           
//    _____                      _____        _            
//   / ____|                    |  __ \      | |           
//  | |  __  __ _ _ __ ___   ___| |  | | __ _| |_ __ _ ___ 
//  | | |_ |/ _` | '_ ` _ \ / _ \ |  | |/ _` | __/ _` / __|
//  | |__| | (_| | | | | | |  __/ |__| | (_| | || (_| \__ \
//   \_____|\__,_|_| |_| |_|\___|_____/ \__,_|\__\__,_|___/
//                                                        
/////////////////////////////////////////////////////////////////////////////////
        
setup: function( gamedatas )
{
    console.log( "Starting game setup" );

    console.log('gamedatas');
    console.log(gamedatas);

    this.boards = ["castle", "pool", "clouds", "jungle", "cemetery", "carribean","station", "battlefield"];
    this.medals_to_win = [7, 6, 8, 7, 7, 5, 7, 8];

    this.BLUE_COLOR = "4f66a2";
    this.RED_COLOR = "d1553e";
    this.BLUE = 0;
    this.RED = 1;
    
    this.TROOP_WIDTH = 66;  
    this.TROOP_HEIGHT = 88;        
    this.BOARD_WIDTH = 500;  
    this.BOARD_HEIGHT = 833.5;

    this.players = gamedatas.players; // A RAJOUTER/NE PAS SUPPRIMER POUR MOTEUR (UTILITY METHODS)

    this.connections = [];

this.bases = gamedatas.bases;
this.regions = gamedatas.regions;
this.medals = gamedatas.medals;
this.troop_types = gamedatas.troop_types;
this.board_types = gamedatas.board_types;
this.board_name = gamedatas.board_name;
this.board_id = gamedatas.board_id;



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

this.troops_blocked = [gamedatas.blue_blocked, gamedatas.red_blocked ];
    
this.setupPlayersBoard();
this.setupBoard();
this.setupCounters();



    // Setup game notifications to handle (see "setupNotifications" method below)
    this.setupNotifications();

    console.log( "Ending game setup" );
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
onEnteringState: function( stateName, args )
{
    
    if( stateName != 'pending') {
        console.log('Entering state: '+stateName, args);
    }
    
    switch( stateName ) {
        case 'playerTurn':
            this.args = args.args;
            if(this.isCurrentPlayerActive()) {
                this.args.selectable.forEach(sid => {
                    dojo.addClass(sid,"selectable");
                });
                this.args.selected.forEach(sid => {
                    dojo.addClass(sid,"selected");    
                });

                this.setupConnections(this.args.selectable);

                if(args.args.titleyou != null) {
                    $('pagemaintitletext').innerHTML = this.format_string_recursive((args.args.titleyou).replace('${you}', this.divYou()).replace(/#opponent#/g,args.args.opponent).replace('#nb#',args.args.nb).replace('#nb2#',args.args.nb2).replace('#icon#',args.args.icon).replace('#icon2#',args.args.icon2), args.args);
                }   
            }   
            else
            {
                if(args.args.title != null) {
                    $('pagemaintitletext').innerHTML = this.format_string_recursive(_(args.args.title).replace('${actplayer}', this.divActPlayer()).replace('#nb#',args.args.nb).replace('#nb2#',args.args.nb2).replace('#icon#',args.args.icon).replace('#icon2#',args.args.icon2), args.args);  
                }
            }
           
            break;
        case 'dummmy':
            break;
    }
},

// onLeavingState: this method is called each time we are leaving a game state.
//                 You can use this method to perform some user interface changes at this moment.
//

onLeavingState: function( stateName )
{
    if( stateName != 'pending') {
        console.log('Leaving state: '+stateName);
    }
    
    dojo.query(".selectable").removeClass("selectable");
    dojo.query(".selected").removeClass("selected");
    
    switch( stateName )
    {
        case 'playerTurn':
            this.removeConnections();            
            break;
        case 'dummy':
            break;
    }               
}, 

// onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
//                        action status bar (ie: the HTML links in the status bar).
//      

onUpdateActionButtons: function( stateName, args )
{
    console.log( 'onUpdateActionButtons: '+stateName, args );
            
    if( this.isCurrentPlayerActive() )  {            
        switch( stateName )
        {
            case "playerTurn":
                for (let button of args.buttons) { 
                    switch (button) {
                        case "btn_cancel":
                            this.addActionButton('btn_cancel', _("Cancel"), 'onOpButton', null, null, 'red');
                            break;
                        case "btn_pass":
                            this.addActionButton('btn_pass', _("Pass"), 'onOpButton', null, null, 'red');
                            break;
                        case "btn_continue":
                            this.addActionButton('btn_continue', _("Continue"), 'onOpButton', null, null, 'blue');
                            break;
                        case "btn_draw_2":
                            this.addActionButton('btn_draw_2', _("Draw 2 Troops"), 'onOpButton', null, null, 'blue');
                            dojo.removeClass('btn_draw_2', 'bgabutton_blue');
                            dojo.addClass('btn_draw_2', 'bgabutton_orange');
                            break;
                        case "btn_draw_1":
                            this.addActionButton('btn_draw_1', _("Draw 1 Troop"), 'onOpButton', null, null, 'blue');
                            dojo.removeClass('btn_draw_1', 'bgabutton_blue');
                            dojo.addClass('btn_draw_1', 'bgabutton_orange');
                            break;
                        case "btn_place_troop":
                            this.addActionButton('btn_place_troop', _("Place 1 Troop"), 'onOpButton', null, null, 'blue');
                            dojo.removeClass('btn_place_troop', 'bgabutton_blue');
                            dojo.addClass('btn_place_troop', 'bgabutton_khakhi');
                            break;
                        case "btn_yes":
                            this.addActionButton('btn_yes', _("Yes"), 'onOpButton', null, null, 'blue');
                            break;
                        case "btn_no":
                            this.addActionButton('btn_no', _("No"), 'onOpButton', null, null, 'red');
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

divYou : function() {
    const color = this.players[this.player_id].color;
    const color_bg = "";
    const you = "<span style=\"font-weight:bold;color:#" + color + ";" + color_bg + "\">" + _("You") + "</span>";
    return you;
},

divActPlayer : function() {        	
    const color = this.players[this.getActivePlayerId()].color;
    const name = this.players[this.getActivePlayerId()].name;
    const color_bg = "";
    const you = "<span style=\"font-weight:bold;color:#" + color + ";" + color_bg + "\">" + name + "</span>";
    return you;
},

format_string_recursive : function(log, args) {
    try {
        if (log && args && !args.processed) {
            args.processed = true;

            
        }
    } catch (e) {
        console.error(log,args,"Exception thrown", e.stack);
    }
    return this.inherited(arguments);
},


/*************************************************
 * 
 *  setup connections from this.args.selectable
 * on each beginning of new State (Player Turn)
 * 
 ************************************************/

setupConnections: function(selectables) {
    this.connections = [];

    selectables.forEach(elt_id => {
        const element = document.getElementById(elt_id);

        const resourceClickHandler = (evt) => this.onSelect(evt);
        element.addEventListener('click', resourceClickHandler);
        this.connections.push({ element, event: 'click', handler: resourceClickHandler });
    });

    const zoomInputHandler = () => {
        window.localStorage.setItem("TB_zoom", $("zoom_value").value);
        this.onScreenWidthChange();
    };
    
    $("zoom_value").addEventListener("input", zoomInputHandler);
    this.connections.push({element: $("zoom_value"), event: "input", handler: zoomInputHandler });
},


/*************************************************
 * 
 *  reset all connections 
 *  on leaving a State
 * 
 ************************************************/

removeConnections: function() {
    this.connections.forEach(connection => {
        const { element, event, handler } = connection;
        element.removeEventListener(event, handler);
    });
    this.connections = [];
},




setupPlayersBoard: function() {
    Object.values(this.players).forEach(player => {
        const playerBoardElement = document.getElementById('player_board_' + player.id);
        playerBoardElement.insertAdjacentHTML('beforeend', `
            <div class="a_board" id="a1_board_${player.id}"></div>
            <div class="a_board" id="a2_board_${player.id}"></div>
        `);

        const a1BoardElement = document.getElementById('a1_board_' + player.id);

        const medals_needed = this.medals_to_win[this.board_id-1];
        const medals_won = player.star;
        for (let i = 1; i <= medals_needed; i++) {
            const medalContainer = document.createElement('div');
            medalContainer.id = `medal_${player.id}_${i}`;
            
            if (i <= medals_won) {
                medalContainer.classList.add('medals', 'full_medal'); // Médailles gagnées
            } else {
                medalContainer.classList.add('medals', 'null_medal'); // Médailles manquantes
            }
            a1BoardElement.appendChild(medalContainer);
        }



        if( player.id == this.player_id || (this.isSpectator && player.id == this.spectator_id) )
        {
            const a2BoardElement = document.getElementById('a2_board_' + player.id);
                    a2BoardElement.insertAdjacentHTML('beforeend', `
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
                    `);
                    const helpModeSwitchElement = document.getElementById('help-mode-switch');
                    helpModeSwitchElement.style.display = 'inline-block';
                    const helpModeCheckbox = document.getElementById('help-mode-chk');
                    helpModeCheckbox.addEventListener('change', () => {
                        this.toggleHelpMode(helpModeCheckbox.checked);
                    });
                    this.addTooltip("help-mode-switch", "", _("Toggle Tooltips on Mobile mode."));
                /* help mode Tisaac */

        }

        /* slider */
        if(player.id == this.opponent_id) {
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
        }
        /* slider */

    });

    

},


/**************************
 * 
 * board is created from top to bottom for Blue player
 * and inverted for Red player
 * 
 **************************/

setupBoard: function() {
    console.log('setup Board');

    //const mediaQuery = window.matchMedia('(max-width: 1024px) and (orientation: landscape)');
    const mediaQuery = window.matchMedia('(orientation: landscape)');

    const handleTabletChange = (e) => {
        if (e.matches) {
            console.log('Media Query Matched! Landscape Mode');
            this.orientation = "landscape";
            this.setupLandscapeMode();
        } else {
            console.log('Portrait Mode');
            this.orientation = "portrait";
            this.setupPortraitMode();
        }
    };

    mediaQuery.addEventListener('change', handleTabletChange);

    handleTabletChange(mediaQuery);

    //this.setupLandscapeMode();

},

setupLandscapeMode: function() {
    const globalContainer = document.getElementById('global_id');

    // Reinitialization
    globalContainer.className = '';
    globalContainer.innerHTML = '';

    /*  boardContainer definition 
        contains board and all troops
    */
    
    const boardContainer = this.createBoard();
    if( this.isCurrentPlayerRed() ) {
        boardContainer.classList.add('board-inverted');   
    }
    globalContainer.appendChild(boardContainer);

    this.createBases();
    this.createMedals();
    this.createTroopsOnBoard();

    /*  PlaymatContainer definition 
        contains red Rack red Discard, both Decks, blue Discard and blue Rack
    */

    const playmatContainer = this.createPlaymat();
    if( this.isCurrentPlayerRed() ) {
        playmatContainer.classList.add('board-inverted');
    }
    globalContainer.appendChild(playmatContainer);


    /*  redRackContainer definition 
    contains Rack and all Troops in Hand
*/
    const redRackContainer = this.createRack('red');
    playmatContainer.appendChild(redRackContainer);
    
    if( this.isCurrentPlayerRed() ) {
        Object.values(this.my_hand).reverse().forEach(troop => {
            const troopElement = this.createTroopElement(troop);
            if( troop.blocked > 0) {
                const checkElement = document.createElement('div');
                checkElement.id = `check_${troop.blocked}`;
                checkElement.classList.add('checks', 'check_blue');
                troopElement.appendChild(checkElement);
            }
            troopElement.classList.add('board-inverted');
            redRackContainer.appendChild(troopElement);

            this.addCustomTooltip(troopElement.id, this.getTooltipTroopContent(troop.type, troop.id), 0); 
            
        });
    }
    else {
        Object.values(this.your_hand).forEach((troop, index) => {
            const troopElement = this.createBackTroopElement(troop, index);
            if( this.troops_blocked[1].includes(`${index + 1}`)) {
                const checkElement = document.createElement('div');
                checkElement.id = `check_${index + 1}`;
                checkElement.classList.add('checks', 'check_blue');
                troopElement.appendChild(checkElement);                
            }
            troopElement.classList.add('board-inverted');
            redRackContainer.appendChild(troopElement);

        });
    }


    /* redDiscard */
    const redDiscardContainer = this.createDiscard( 'red' );
    redDiscardContainer.style.flexDirection = "row";
    playmatContainer.appendChild(redDiscardContainer);


    const red_discard_list = this.isCurrentPlayerRed() ? this.my_discard : this.your_discard;
    Object.values(red_discard_list).forEach(troop => {
        const troopElement = this.createTroopElement(troop);
        troopElement.classList.add('board-inverted', 'opa_70');
        redDiscardContainer.appendChild(troopElement);
        this.addCustomTooltip(troopElement.id, this.getTooltipTroopContent(troop.type, troop.id), 0);  
    });
   

    /* whiteLineContainer */
    const whiteLineContainer = this.createLine( 'white');
    playmatContainer.appendChild(whiteLineContainer);

    /* blueDeckElement */
    const blueDeckElement = this.createDeck( 'blue' );
    whiteLineContainer.appendChild(blueDeckElement);

    const blueDeckCounterElement = this.createDeckCounter( 'blue');
    blueDeckElement.appendChild(blueDeckCounterElement);

    /* redDeckElement */
    const redDeckElement = this.createDeck( 'red' );
    redDeckElement.classList.add('board-inverted');
    whiteLineContainer.appendChild(redDeckElement);

    const redDeckCounterElement = this.createDeckCounter( 'red');
    redDeckElement.appendChild(redDeckCounterElement);

    /*  blueDiscardContainer definition 
        contains possible Troops in opacity 70 and all discarded ones TODO
    */
    const blueDiscardContainer = this.createDiscard( 'blue' );
    blueDiscardContainer.style.flexDirection = "row";
    playmatContainer.appendChild(blueDiscardContainer);

    const blue_discard_list = this.isCurrentPlayerRed() ? this.your_discard : this.my_discard;
    Object.values(blue_discard_list).forEach(troop => {
        const troopElement = this.createTroopElement(troop);
        troopElement.classList.add('opa_70');
        blueDiscardContainer.appendChild(troopElement);
        this.addCustomTooltip(troopElement.id, this.getTooltipTroopContent(troop.type, troop.id), 0);  
    });


    /* blueRackContainer */
    const blueRackContainer = this.createRack('blue');
    playmatContainer.appendChild(blueRackContainer);

    if( this.isCurrentPlayerRed() ) {
        Object.values(this.your_hand).forEach((troop, index) => {
            const troopElement = this.createBackTroopElement(troop, index);

            if( this.troops_blocked[0].includes(`${index + 1}`)) {
                const checkElement = document.createElement('div');
                checkElement.id = `check_${index + 1}`;
                checkElement.classList.add('checks', 'check_red');
                troopElement.appendChild(checkElement);                
            }
            blueRackContainer.appendChild(troopElement);

        });
    }
    else if( this.isSpectator) {
        Object.values(this.my_hand).forEach((troop, index) => {
            const troopElement = this.createBackTroopElement(troop, index);
            if( this.troops_blocked[0].includes(`${index + 1}`)) {
                const checkElement = document.createElement('div');
                checkElement.id = `check_${index + 1}`;
                checkElement.classList.add('checks', 'check_red');
                troopElement.appendChild(checkElement);                
            }
            blueRackContainer.appendChild(troopElement);
        });
    }
    else {
        Object.values(this.my_hand).forEach(troop => {
            const troopElement = this.createTroopElement(troop);
            if( troop.blocked > 0) {
                const checkElement = document.createElement('div');
                checkElement.id = `check_${troop.blocked}`;
                checkElement.classList.add('checks', 'check_red');
                troopElement.appendChild(checkElement);
            }
            blueRackContainer.appendChild(troopElement);
            this.addCustomTooltip(troopElement.id, this.getTooltipTroopContent(troop.type, troop.id), 0);  
        });
    }

},

setupPortraitMode: function() {
    /*  creates global container with yourLine, playMat and myLine containers  */
    const globalContainer = document.getElementById('global_id');

    // Réinitialization
    globalContainer.className = '';
    globalContainer.innerHTML = '';

    if( this.isCurrentPlayerRed() ) {
        globalContainer.classList.add('board-inverted');
    }

    /*  redLineContainer definition 
        contains redDeckElement and redRackContainer
    */
    const redLineContainer = this.createLine('red');
    globalContainer.appendChild(redLineContainer);


    /*  redRackContainer definition 
        contains Rack and all Troops in Hand
    */
    const redRackContainer = this.createRack('red');
    redLineContainer.appendChild(redRackContainer);

    if( this.isCurrentPlayerRed() ) {
        Object.values(this.my_hand).reverse().forEach(troop => {
            const troopElement = this.createTroopElement(troop);
            troopElement.classList.add('board-inverted');
            redRackContainer.appendChild(troopElement);
            this.addCustomTooltip(troopElement.id, this.getTooltipTroopContent(troop.type, troop.id), 0);  
        });
    }
    else {
        Object.values(this.your_hand).forEach((troop, index) => {
            const troopElement = this.createBackTroopElement(troop, index);
            troopElement.classList.add('board-inverted');
            redRackContainer.appendChild(troopElement);
        });
    }
    
    
    /*  yourDeckContainer definition 
        contains Deck and number of Troops TODO
    */
        const redDeckElement = this.createDeck( 'red' );
        redDeckElement.classList.add('board-inverted');
        redLineContainer.appendChild(redDeckElement);

        const redDeckCounterElement = this.createDeckCounter( 'red');
        redDeckElement.appendChild(redDeckCounterElement);

    /*  PlaymatContainer definition 
        contains blueDiscard, Board and redDiscard
    */
    const playmatContainer = this.createPlaymat();
    globalContainer.appendChild(playmatContainer);

    /*  blueDiscardContainer definition 
        contains possible Troops in opacity 50 and all discarded ones TODO
    */
    const blueDiscardContainer = this.createDiscard( 'blue' );
    blueDiscardContainer.style.flexDirection = "column";
    playmatContainer.appendChild(blueDiscardContainer);
    

    const blue_discard_list = this.isCurrentPlayerRed() ? this.your_discard : this.my_discard;
    Object.values(blue_discard_list).forEach(troop => {
        const troopElement = this.createTroopElement(troop);
        troopElement.classList.add('opa_70');
        blueDiscardContainer.appendChild(troopElement);
        this.addCustomTooltip(troopElement.id, this.getTooltipTroopContent(troop.type, troop.id), 0);  
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
    const redDiscardContainer = this.createDiscard( 'red' );
    redDiscardContainer.style.flexDirection = "column";
    playmatContainer.appendChild(redDiscardContainer);
    
    const red_discard_list = this.isCurrentPlayerRed() ? this.my_discard : this.your_discard;
    Object.values(red_discard_list).forEach(troop => {
        const troopElement = this.createTroopElement(troop);
        troopElement.classList.add('board-inverted', 'opa_70');
        redDiscardContainer.appendChild(troopElement);
        this.addCustomTooltip(troopElement.id, this.getTooltipTroopContent(troop.type, troop.id), 0);  
    });
    
    
    /* blueLineContainer */
    const blueLineContainer = this.createLine( 'blue');
    globalContainer.appendChild(blueLineContainer);

    /* blueDeckElement */
    const blueDeckElement = this.createDeck( 'blue' );
    blueLineContainer.appendChild(blueDeckElement);

    /* blueDeckCounterElement */
    const blueDeckCounterElement = this.createDeckCounter( 'blue');
    blueDeckElement.appendChild(blueDeckCounterElement);

    /* blueRackContainer */
    const blueRackContainer = this.createRack('blue');
    blueLineContainer.appendChild(blueRackContainer);

    if( this.isCurrentPlayerRed() ) {
        Object.values(this.your_hand).forEach((troop, index) => {
            const backTroopElement = this.createBackTroopElement(troop, index);
            blueRackContainer.appendChild(backTroopElement);
        });
    }
    else if( this.isSpectator) {
        Object.values(this.my_hand).forEach((troop, index) => {
            const backTroopElement = this.createBackTroopElement(troop, index);
            blueRackContainer.appendChild(backTroopElement);
        });
    }
    else {
        Object.values(this.my_hand).forEach(troop => {
            const troopElement = this.createTroopElement(troop);
            blueRackContainer.appendChild(troopElement);
            this.addCustomTooltip(troopElement.id, this.getTooltipTroopContent(troop.type, troop.id), 0);  
        });
    }
},


createLine: function( color ) {
    const lineContainer = document.createElement('div');
    lineContainer.id = `${color}_line`;
    lineContainer.classList.add('line');

    return lineContainer;
},

createTroopElement: function( troop ) {
    const troopElement = document.createElement('div');
    troopElement.id = `troop_${troop.id}`;
    troopElement.classList.add('troop');
    const troop_type =  troop.type % 10;
    const troop_color = Math.floor(troop.type / 10)-1;
    troopElement.style.backgroundPosition = `-${troop_type}00% -${troop_color}00%`;
     
    return troopElement;
},

createBackTroopElement: function( troop, index ) {
    const backTroopElement = document.createElement('div');
    const back_color = troop.type == 1 ? 'blue' : 'red';
    const sprite_line = troop.type == 1 ? '0' : '1';
    backTroopElement.id = `${back_color}_troop_${index + 1}`;
    backTroopElement.classList.add('troop');
    backTroopElement.style.backgroundPosition = `-0% -${sprite_line}00%`;
    return backTroopElement;
},

createRack: function( color ) {
    const rackContainer = document.createElement('div');
    rackContainer.id = `${color}_rack`;
    rackContainer.classList.add('rack', `rack_${color}`);

    return rackContainer;
},

createDeck: function( color ) {
    const deckElement = document.createElement('div');
    deckElement.id = `${color}_deck`;
    deckElement.classList.add('deck');
    const sprite_line = color == 'blue' ? '0' : '1';
    deckElement.style.backgroundPosition = `0% -${sprite_line}00%`;
    return deckElement;
},

createDeckCounter : function( color) {
    deckCounterElement = document.createElement('div');
    deckCounterElement.id = `${color}_deck_counter_id`;
    deckCounterElement.classList.add('deck_counter', `${color}_deck`);
    if( this.isCurrentPlayerRed() ) {
        deckCounterElement.classList.add('board-inverted');
    }
    return deckCounterElement;
},

createPlaymat: function() {

    const playmatContainer = document.createElement('div');
    playmatContainer.id = `playmat_id`;
    playmatContainer.classList.add('playmat');

    return playmatContainer;
},


createDiscard: function( color ) {
    const discardContainer = document.createElement('div');
    discardContainer.id = `${color}_discard`;
    discardContainer.classList.add('discard', `linear_${color}`);

    discardContainer.style.justifyContent = color === 'blue' ? 'flex-end' : 'flex-start';

    return discardContainer;
},


createBoard: function() {
    const boardContainer = document.createElement('div');
    boardContainer.id = `board_${this.board_id}`;
    boardContainer.classList.add('board');

    const background_x = (this.board_id - 1) % 4;  
    const background_y = Math.floor( (this.board_id - 1) / 4);
    boardContainer.style.backgroundPosition = `-${background_x}00% -${background_y}00%`;
    return boardContainer;
},

createBases: function() {
    const TB_bases = this.bases[this.board_name];
    this.troops_on_bases = {};

    const boardContainer = document.getElementById(`board_${this.board_id}`);
    // defines bases position from TB_bases array */
    for (const baseId of Object.keys(TB_bases)) {
        this.troops_on_bases[baseId] = [];
        const baseData = TB_bases[baseId];
        // big base element
        const baseElement = document.createElement('div');
        baseElement.id = `base_${this.board_name}_${baseId}`;
        baseElement.classList.add('base_all');
        //baseElement.classList.add('selected');
        baseElement.style.cssText = `top: ${baseData.top}%; left: ${baseData.left}%;`;
        boardContainer.appendChild(baseElement);

        // blue base element
        const baseBlueElement = document.createElement('div');
        baseBlueElement.id = `blue_base_${this.board_name}_${baseId}`;
        baseBlueElement.classList.add('base');
        baseBlueElement.style.cssText = `top: ${baseData.top}%; left: ${baseData.left}%;`;
        boardContainer.appendChild(baseBlueElement);

        // red base element
        const baseRedElement = document.createElement('div');
        baseRedElement.id = `red_base_${this.board_name}_${baseId}`;
        baseRedElement.classList.add('base');
        baseRedElement.style.cssText = `top: ${baseData.top + 2.5}%; left: ${baseData.left}%;`;
        boardContainer.appendChild(baseRedElement);
    }

    console.log( 'TROOPS ON BASES', this.troops_on_bases);
},

createTroopsOnBoard:function() {
    const boardContainer = document.getElementById(`board_${this.board_id}`);
    const TB_bases = this.bases[this.board_name];
    Object.values(this.gamedatas.board_troops).forEach(troop => {

        this.troops_on_bases[troop.location_arg].push(troop);

        const troopElement = this.createTroopElement( troop );
        const troop_color = Math.floor(troop.type / 10)-1;
        // defines position on board from TB_bases array
        const baseData = TB_bases[troop.location_arg];
        troopElement.style.cssText = `
            position: absolute;
            top: ${troop_color === this.BLUE ? `${baseData.top}%` : `${baseData.top + 2.5}%`};
            left: ${baseData.left}%;
            z-index: ${10 * troop.ordre};
        `;
        if( troop_color == this.RED ) {
            troopElement.classList.add('board-inverted');
        }
        boardContainer.appendChild(troopElement);
    });
    this.createBasesTooltips();
},

createMedals:function() {
    const boardContainer = document.getElementById(`board_${this.board_id}`);
    const TB_medals = this.medals[this.board_name];
    Object.entries(TB_medals).forEach(([id, medal]) => {
        if( this.gamedatas.full_regions.includes(medal.region.toString()) ) {
            const medalElement = document.createElement('div');
            medalElement.id = `medal_${id}`;
            medalElement.classList.add('medals', 'board_medal');
            medalElement.style.cssText = `position: absolute; top: ${medal.top}%; left: ${medal.left}%; z-index: 10;`;
            boardContainer.appendChild(medalElement);
        }
    });
},

createBasesTooltips: function() {
    Object.keys(this.troops_on_bases).forEach(base_id => {
        this.createBaseTooltip(base_id);
    });
},

createBaseTooltip: function(base_id) {
    const TB_bases = this.bases[this.board_name];
    const base_power = TB_bases[base_id].power;
    const troops = this.troops_on_bases[base_id];
    
    if (troops.length > 0 || base_power > 0) {
        const base_css_id = `base_${this.board_name}_${base_id}`;
        this.addCustomTooltip(base_css_id, this.getTooltipBaseContent(this.board_id, base_power, troops), 0);
    }
},


setupCounters: function() {
    this.blue_deck_counter = new ebg.counter();
    this.blue_deck_counter.create('blue_deck_counter_id');
    this.blue_deck_counter.toValue(this.nb_decks[0]);

    this.red_deck_counter = new ebg.counter();
    this.red_deck_counter.create('red_deck_counter_id');
    this.red_deck_counter.toValue(this.nb_decks[1]);
},


/*211224removeTroopFromBoardArray: function( troop_id ) {
    const index = this.board_troops.findIndex(t => t.id === troop_id);
    if (index !== -1) {
        this.board_troops.splice(index, 1);
    }
},*/

removeTroopFromBaseArray: function( troop ) {
    const base_id = troop.location_arg;
    let base_troops = this.troops_on_bases[base_id];
    const index = base_troops.findIndex(t => t.id === troop.id);
    if (index !== -1) {
        this.troops_on_bases[base_id].splice(index, 1);
        this.createBaseTooltip(base_id);
        
        //const base_css_id = `base_${this.board_name}_${base_id}`;
        //this.addCustomTooltip(base_css_id, this.getTooltipBaseContent(this.board_id, base_power, troops), 0);  
    }

},

removeTroopFromMyHandArray: function( troop_id) {
    const index = this.my_hand.findIndex(t => t.id === troop_id);
    if (index !== -1) {
        this.my_hand.splice(index, 1);

        //remove tooltip
        this.tooltips[`troop_${troop_id}`].destroy();
        delete this.tooltips[`troop_${troop_id}`];
    }
},


showArrays: function() {

    
    console.log('my_hand',this.my_hand);
    console.log('your_hand',this.your_hand);
    
    //console.log('nb_decks',this.nb_decks);

    console.log('my_discard',this.my_discard);
    console.log('your_discard',this.your_discard);

    //console.log('troops_on_bases',this.troops_on_bases);
},



isCurrentPlayerRed: function()
{
    if( this.gamedatas.players[ this.player_id ] )
    {
        if( this.gamedatas.players[ this.player_id ].color == this.RED_COLOR )
        {   return true;   }
    }
    return false;
},

isCurrentPlayerBlue: function()
{
    if( this.gamedatas.players[ this.player_id ] )
    {
        if( this.gamedatas.players[ this.player_id ].color == this.BLUE_COLOR )
        {   return true;   }
    }
    return false;
},


getBoundingClientRectIgnoreZoom: function (element) {
    let rect = element.getBoundingClientRect();
    const zoomCorr = this.interface_autoscale === true && !this.gameinterface_boundingRectIgnoresZoom ? (this.gameinterface_zoomFactor || 1) : 1;
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

onScreenWidthChange: function() {
    const board = document.getElementById(`board_${this.board_id}`);       // Élément board
    const playmat = document.getElementById('playmat_id');   // Élément playmat

    //TODO hauteur proportionnelle à la largeur du board.

    if (board && playmat) {

        // Récupérer les dimensions recalculées
        const boardRect = this.getBoundingClientRectIgnoreZoom(board);
        const playmatRect = this.getBoundingClientRectIgnoreZoom(playmat);

        // Choisir la base de référence (largeur ou hauteur)

        const baseWidth = boardRect.width;
        const baseHeight = baseWidth / this.BOARD_WIDTH * this.BOARD_HEIGHT;

        // Calculer la taille des troops proportionnellement
        const troopWidth = (this.TROOP_WIDTH / this.BOARD_WIDTH) * baseWidth; // 66px basé sur la largeur du board original
        const troopHeight = (this.TROOP_HEIGHT / this.TROOP_WIDTH) * troopWidth; // 88px basé sur la hauteur originale

        // Mettre à jour les variables CSS
        document.documentElement.style.setProperty('--troop-width', `${troopWidth}px`);
        document.documentElement.style.setProperty('--troop-height', `${troopHeight}px`);
        document.documentElement.style.setProperty('--board-height', `${baseHeight}px`);
    }

    const TB_zoom = window.localStorage?.getItem("TB_zoom") ?? 100;
    //this.scale = Math.min(horizontalScale, verticalScale)*BB_zoom/100;
    // TO DO 

},


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
    dojo.addClass('ebd-body', 'help-mode');
    this._displayedTooltip = null;
    document.body.addEventListener('click', this.closeCurrentTooltip.bind(this));
},

desactivateHelpMode() {
    this.closeCurrentTooltip();
    this._helpMode = false;
    dojo.removeClass('ebd-body', 'help-mode');
    document.body.removeEventListener('click', this.closeCurrentTooltip.bind(this));
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
        this.connect($(node), 'click', safeCallback);
        dojo.removeClass(node, 'unselectable');
        dojo.addClass(node, 'selectable');
        this._selectableNodes.push(node);
    } else {
        dojo.connect($(node), 'click', safeCallback);
    }
},

    /**
     * Tooltip to work with help mode
     */
registerCustomTooltip(html, id = null) {
    id = id || this.game_name + '-tooltipable-' + this._customTooltipIdCounter++;
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

    // Handle dynamic content out of the box
    let getContent = () => {
        let content = typeof html === 'function' ? html() : html;
        if (config.midSize) {
        content = '<div class="midSizeDialog">' + content + '</div>';
        }
        return content;
    };

    if (this.tooltips[id] && !config.forceRecreate) {
        this.tooltips[id].getContent = getContent;
        return;
    }

    let tooltip = new dijit.Tooltip({
        //        connectId: [id],
        getContent,
        position: this.defaultTooltipPosition,
        showDelay: config.delay,
    });
    this.tooltips[id] = tooltip;
    dojo.addClass(id, 'tooltipable');

    dojo.connect($(id), 'click', (evt) => {
        if (!this._helpMode) {
        tooltip.close();
        } else {
        evt.stopPropagation();

        if (tooltip.state == 'SHOWING') {
            this.closeCurrentTooltip();
        } else {
            this.closeCurrentTooltip();
            tooltip.open($(id));
            this._displayedTooltip = tooltip;
        }
        }
    });

    tooltip.showTimeout = null;
    dojo.connect($(id), 'mouseenter', (evt) => {
        evt.stopPropagation();
        if (!this._helpMode && !this._dragndropMode) {
        if (tooltip.showTimeout != null) clearTimeout(tooltip.showTimeout);

        tooltip.showTimeout = setTimeout(() => {
            if ($(id)) tooltip.open($(id));
        }, config.delay);
        }
    });

    dojo.connect($(id), 'mouseleave', (evt) => {
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


getTooltipTroopContent : function(type, id) {

    let html = '<div class="tooltip_content">';
    // Calcul de la position de l'image
    const x = '-' + type % 10 + '00%';
    const y = '-' + Math.floor(type / 10)-1 + '00%';
    
    // Ajout de la troop (image) à gauche
    html += `<div class="troop_container">
               <div id="tb_troop_toolt_${id}" class="troop" style="background-position:${x} ${y};"></div>
            </div>`;
    
    // Ajout des informations à droite
    html += `<div class="info_container">`;

    const troop_infos = this.troop_types[type % 10];
   
    // Afficher les informations des Troops    
        html += `<span class='tooltip_title'>${troop_infos.name}</span>`;
    let effect_desc = troop_infos.desc1;
    html += `<br><span class='tooltip_desc'>${effect_desc}</span>`;

    let effect_info = troop_infos.desc2;
    html += `<br><span class='tooltip_info'>${effect_info}</span>`;
     
    html += '</div></div>'; // Fermeture des div      
    return html;          
},

getTooltipBaseContent: function(board_id, base_power, troops) {

    // troops est trié selon l'ordre décroissant
    troops.sort((a, b) => b.ordre - a.ordre);

    let html = '<div">';

    if( base_power > 0) {
        // Afficher les informations de la Base Spéciale  
        
        const board_infos = this.board_types[board_id];
        html += '<div id="special_base_desc">';
        html += `<br><span class='tooltip_title'>${board_infos.name}</span>`;
        let effect_desc = board_infos.desc1;
        html += `<br><span class='tooltip_desc'>${effect_desc}</span>`;

        let effect_info = board_infos.desc2;
        // Remplacer les ressources par les icônes dans le texte d'informations

        html += `<br><span class='tooltip_info'>${effect_info}</span></div>`;

        if( troops.length > 0) {
            html += `<hr style="border: 1px solid #7a9f34; margin: 10px 0;">`
        }

    }
    if( troops.length > 0) {
        html += `<span class='tooltip_title'>${_('Order of Troops on Base')}</span>`;
        html+= '<div class="tooltip_content">';

        Object.values(troops).forEach(troop => {
            
            const x = '-' + troop.type % 10 + '00%';
            const y = '-' + Math.floor(troop.type / 10)-1 + '00%';
            html += `<div class="troop_container">
                <div class="troop" style="background-position:${x} ${y};"></div></div>`;
        });
        html += '</div>';

    }

    html += '</div>'; // Fermeture des div      
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

stopEvent:function (evt) {
    if (evt) {
        evt.preventDefault();
        evt.stopPropagation();
    }
},


        
onSelect: function(evt)
{        	 
    // Preventing default browser reaction
    this.stopEvent( evt );
        
    if( !this.isCurrentPlayerActive() || !(evt.currentTarget.classList.contains('selectable')) )
    {   
        return; 
    }
    
    if(this.isCurrentPlayerActive() && evt.currentTarget.classList.contains('selectable'))
    {
            this.bgaPerformAction('actSelect', { arg1: evt.currentTarget.id });
    }
},

onOpButton: function(evt)
{
    
    // Preventing default browser reaction
    this.stopEvent( evt );
    console.log('evt.currentTarget.id',evt.currentTarget.id);
    this.bgaPerformAction('actButton', { arg1: evt.currentTarget.id });
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

setupNotifications: function()
{
    console.log( 'notifications subscriptions setup' );
    
    const notifs = [
        ['displayNotif', 1],
        ['moveTroop', 1],
        ['drawTroopPrivate', 1],
        ['drawTroopPublic', 1],
        ['discardTroopFromBoard', 1],
        ['discardTroopFromHand', 1],
        ['recoverTroopFromBoard', 1],
        ['recoverTroopFromDiscard', 1],
        ['moveTroopBoardToBoard', 1],
        ['hideTroopOnRackPrivate', 1],
        ['hideTroopOnRackPublic', 1],
        ['unhideTroopOnRack', 1],
        ['gainMedal', 1],
        ['score', 1],
        ['message_allplayers_without_player', 1],

    ];

    notifs.forEach((notif) => {
        dojo.subscribe(notif[0], this, `notif_${notif[0]}`);
        this.notifqueue.setSynchronous(notif[0], notif[1]);
    });

    this.notifqueue.setIgnoreNotificationCheck( 'message_allplayers_without_player', (notif) => (notif.args.player_id == this.player_id) );

},

notif_displayNotif: function(notif)
{
    console.log('notif_displayNotif');
    console.log(notif);
},



/*********************************
 * 
 *  a troop is moved from Rack to Board
 *    Action PLACE
 *    Troop 4 Crochet
 *    Troop 2 Cap'taine
 * 
 **********************************/

notif_moveTroop: function(notif)
{
    console.log('notif_moveTroop');
    console.log(notif);

    this.showArrays();

    const player_color = this.players[notif.args.player_id].color;
    const player_color_name = player_color == this.RED_COLOR ? 'red' : 'blue';
 
    const boardContainer = document.getElementById(`board_${this.board_id}`);

    const TB_bases = this.bases[this.board_name];

    const troop = notif.args.infos_troop;

    const base_css_id = notif.args.base_id;
    const base_id = base_css_id.split("_")[2];

    /* troop is added to board JS array */
    //211224this.board_troops.push(troop);
    
    if( this.player_id == notif.args.player_id) {
    
       /* troop is removed from hand JS array */
       this.removeTroopFromMyHandArray( troop.id);


    /* animation for the active player */
        const troopElement = document.getElementById(`troop_${troop.id}`);
        troopElement.style.zIndex = troop.ordre * 10;
        
        const destination_id = this.isCurrentPlayerRed() ? 'red_'+notif.args.base_id : 'blue_'+notif.args.base_id;
        const destinationContainer = document.getElementById(destination_id);

        const startRect = this.getBoundingClientRectIgnoreZoom(troopElement);
        const endRect = this.getBoundingClientRectIgnoreZoom(destinationContainer);

        let deltaX = endRect.left - startRect.left;
        let deltaY = endRect.top - startRect.top;

            // gets rotation, if defined
        const existingTransform = window.getComputedStyle(troopElement).transform;
        // new transformation
        const translateTransform = `translate(${deltaX}px, ${deltaY}px)`;
        const newTransform = existingTransform !== "none"
            ? `${existingTransform} ${translateTransform}`
            : translateTransform;
        troopElement.style.transform = newTransform;

        const onTransitionEnd = () => {

            troopElement.style.transform = existingTransform;
            troopElement.style.position = 'absolute';

            const baseData = TB_bases[troop.location_arg];
            const troopColor =  Math.floor(troop.type / 10)-1;
            troopElement.style.top = troopColor == this.BLUE ? `${baseData.top}%` : `${baseData.top+2.5}%`; // red troops are 2.5% down
            troopElement.style.left = `${baseData.left}%`;

            boardContainer.appendChild(troopElement);
            troopElement.removeEventListener("transitionend", onTransitionEnd);
        };
        troopElement.addEventListener("transitionend", onTransitionEnd);
    
    }
    else {
       
        // remove troop from hand JS array
        if( this.isSpectator == false || player_color == this.RED_COLOR ) {
            this.your_hand.pop();
        }
        else {
            this.my_hand.pop();
        }        
        
        
        // rename Troop id and unhide it
        let moving_troop_id = `${player_color_name}_troop_${notif.args.nb_troops_hand}`;

        const troopElement = document.getElementById(moving_troop_id);
        
        
        
        troopElement.id = `troop_${troop.id}`;
        const x = troop.type.toString().slice(-1);
        troopElement.style.backgroundPositionX = `-${x}00%`;
        troopElement.style.zIndex = troop.ordre * 10;
        
        const destination_id = this.isCurrentPlayerRed() ? 'blue_'+notif.args.base_id : 'red_'+notif.args.base_id;
        const destinationContainer = document.getElementById(destination_id);
    
        const startRect = this.getBoundingClientRectIgnoreZoom(troopElement);
        const endRect = this.getBoundingClientRectIgnoreZoom(destinationContainer);

        let deltaX = endRect.left - startRect.left;
        let deltaY = endRect.top - startRect.top;
        if( this.isSpectator == false || player_color == this.RED_COLOR ) {
            deltaX = -deltaX;
            deltaY = -deltaY;
        }

                // gets rotation, if defined
        const existingTransform = window.getComputedStyle(troopElement).transform;
        // new transformation
        const translateTransform = `translate(${deltaX}px, ${deltaY}px)`;
        const newTransform = existingTransform !== "none"
            ? `${existingTransform} ${translateTransform}`
            : translateTransform;
        troopElement.style.transform = newTransform;

        const onTransitionEnd = () => {

            troopElement.style.transform = existingTransform;
            troopElement.style.position = 'absolute';

            const baseData = TB_bases[troop.location_arg];
            const troopColor =  Math.floor(troop.type / 10)-1;
            troopElement.style.top = troopColor == this.BLUE ? `${baseData.top}%` : `${baseData.top+2.5}%`; // red troops are 2.5% down
            troopElement.style.left = `${baseData.left}%`;

            boardContainer.appendChild(troopElement);
            troopElement.removeEventListener("transitionend", onTransitionEnd);
        };
        troopElement.addEventListener("transitionend", onTransitionEnd);
        }
        console.log(' base_id ',base_id );
        console.log(' TOB ',this.troops_on_bases[base_id] );
        this.troops_on_bases[base_id].push(troop);
        this.createBaseTooltip(base_id);

    this.showArrays();

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
notif_drawTroopPrivate: function (notif) {
    console.log('notif_drawTroopPrivate');
    console.log(notif);

    const player_color = this.players[notif.args.player_id].color;
    const player_color_name = player_color == this.RED_COLOR ? 'red' : 'blue';
    
    const deckId = `${player_color_name}_deck`;
    const deckContainer = document.getElementById(deckId);
    
    const rackId = `${player_color_name}_rack`;
    const rackContainer = document.getElementById(rackId);
   

    const animateTroop = (troop, index) => {
        /* troop is created and added to the deck */
        const troopElement = document.createElement('div');
        troopElement.id = `troop_${troop.id}`;
        troopElement.classList.add('troop');
        const troop_type = troop.type % 10;
        const troop_color = Math.floor(troop.type / 10) - 1;
        troopElement.style.backgroundPosition = `-${troop_type}00% -${troop_color}00%`;
        deckContainer.appendChild(troopElement);

        this.addCustomTooltip(troopElement.id, this.getTooltipTroopContent(troop.type, troop.id), 0);  

        /* check where to insert the troop */
        const newTroop = { id: troop.id, type: troop.type };

        let insertIndex = this.my_hand.findIndex(t => t.type > newTroop.type);
        if (insertIndex === -1) {
            this.my_hand.push(newTroop); // end of array
        } else {
            this.my_hand.splice(insertIndex, 0, newTroop);
        }

        /* room is reserved in the flex */
        let placeholder = document.createElement('div');
        placeholder.classList.add('troop-placeholder');
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

        const startRect = this.getBoundingClientRectIgnoreZoom(deckContainer);
        const targetRect = this.getBoundingClientRectIgnoreZoom(placeholder);

        const deltaX = targetRect.left - startRect.left;
        const deltaY = targetRect.top - startRect.top;

        troopElement.style.transform = `translate(${deltaX}px, ${deltaY}px)`;
        troopElement.style.zIndex = 100;

        // Ajout de l'écouteur sans 'once: true'
        const onTransitionEnd = () => {
            troopElement.style.transform = '';
            if (player_color == this.RED_COLOR) {
                troopElement.classList.add('board-inverted');
            }

            // Remplacement du placeholder par le troopElement
            rackContainer.replaceChild(troopElement, placeholder);

            // Nettoyage : suppression du gestionnaire
            troopElement.removeEventListener('transitionend', onTransitionEnd);

            // Appel de la prochaine animation si nécessaire
            if (index + 1 < notif.args.new_troops.length) {
                animateTroop(notif.args.new_troops[index + 1], index + 1);
            }
        };

        troopElement.addEventListener('transitionend', onTransitionEnd);
    };

    if (notif.args.new_troops.length > 0) {
        animateTroop(notif.args.new_troops[0], 0);
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

notif_drawTroopPublic: function (notif) {
    console.log('notif_drawTroopPublic');
    console.log(notif);

    this.showArrays();

    const player_color = this.players[notif.args.player_id].color;
    const player_color_name = player_color == this.RED_COLOR ? 'red' : 'blue';
    const player_color_index = player_color == this.RED_COLOR ? '2' : '1';

    const deckId = `${player_color_name}_deck`;
    const deckContainer = document.getElementById(deckId);

    const rackId = `${player_color_name}_rack`;
    const rackContainer = document.getElementById(rackId);

    if( player_color == this.BLUE_COLOR) {
        this.blue_deck_counter.incValue(parseInt(-notif.args.nb_troops));
    }
    else {
        this.red_deck_counter.incValue(parseInt(-notif.args.nb_troops));
    }
    // TODO EMPTY DECK


    if (this.player_id != notif.args.player_id) {

        const animateTroop = (index) => {
            /* troop is created and added to the deck */
            let troopElement = document.createElement('div');
            troopElement.classList.add('troop');

            if (this.isCurrentPlayerRed()) {
                troopElement.id = `blue_troop_${index + parseInt(notif.args.nb_troops_hand) + 1}`;
                troopElement.style.backgroundPosition = `-0% -0%`;

            } else if (this.isCurrentPlayerBlue()) {
                troopElement.id = `red_troop_${index + parseInt(notif.args.nb_troops_hand) + 1}`;
                troopElement.style.backgroundPosition = `-0% -100%`;
            } else if( this.isSpectator == true ){ // spectator
                if (player_color == this.RED_COLOR) {
                    troopElement.id = `red_troop_${index + parseInt(notif.args.nb_troops_hand) + 1}`;
                    troopElement.style.backgroundPosition = `-0% -100%`;
                } else {
                    troopElement.id = `blue_troop_${index + parseInt(notif.args.nb_troops_hand) + 1}`;
                    troopElement.style.backgroundPosition = `-0% -0%`;
                }
            }
            deckContainer.appendChild(troopElement);

            /* add to hand JS array */
            const newTroop = { type: player_color_index };
            if( this.isSpectator == false || player_color == this.RED_COLOR ) {
                this.your_hand.push(newTroop);
            }
            else {
                this.my_hand.push(newTroop);
            }

            /* room is reserved in the flex */
            const placeholder = document.createElement('div');
            placeholder.classList.add('troop-placeholder');
            rackContainer.appendChild(placeholder);

            const startRect = this.getBoundingClientRectIgnoreZoom(deckContainer);
            const targetRect = this.getBoundingClientRectIgnoreZoom(placeholder);

            let deltaX = targetRect.left - startRect.left;
            let deltaY = targetRect.top - startRect.top;

            if (this.isSpectator == false || player_color == this.RED_COLOR) {
                deltaX = -deltaX;
                deltaY = -deltaY;
            }

            troopElement.style.transform = `translate(${deltaX}px, ${deltaY}px)`;
            troopElement.style.zIndex = 100;

            // Gestionnaire de transition
            const onTransitionEnd = () => {
                troopElement.style.transform = '';
                if (player_color == this.RED_COLOR) {
                    troopElement.classList.add('board-inverted');
                }

                // Remplacement du placeholder par le troopElement
                rackContainer.replaceChild(troopElement, placeholder);

                // Nettoyage : suppression du gestionnaire
                troopElement.removeEventListener('transitionend', onTransitionEnd);

                // Appel de la prochaine animation si nécessaire
                if (index + 1 < notif.args.nb_troops) {
                    animateTroop(index + 1);
                }
            };

            troopElement.addEventListener('transitionend', onTransitionEnd);
        };

        if (notif.args.nb_troops > 0) {
            animateTroop(0);
        }
    }

    this.showArrays();
},



/*********************************
 * 
 *  a chosen troop from the board is discarded
 *    Troop 3 Mastok
 * 
 ************************************/

notif_discardTroopFromBoard: function (notif) {
    console.log('notif_discardTroopFromBoard');
    console.log(notif);

    //TODO MAJ this.troopsOnBase et les deux Tooltips

    this.showArrays();

    const troop = notif.args.infos_troop;
    this.removeTroopFromBaseArray(troop);
    //211224this.removeTroopFromBoardArray(troop.id);
    this.addCustomTooltip(`troop_${troop.id}`, this.getTooltipTroopContent(troop.type, troop.id), 0); 
    
    const player_color = this.players[troop.type_arg].color;
    const player_color_name = player_color == this.RED_COLOR ? 'red' : 'blue';

    const troopElement = document.getElementById(`troop_${troop.id}`);
    const discardId = `${player_color_name}_discard`;
    const discardContainer = document.getElementById(discardId);

    /* check where to insert the troop */
    const newTroop = { id: troop.id, type: troop.type };
    let insertIndex;   
    if( this.isSpectator == false || player_color == this.RED_COLOR ) {
       
        insertIndex = this.your_discard.findIndex(t => t.type > newTroop.type);
        if (insertIndex === -1) {
            this.your_discard.push(newTroop); // end of array
        } else {
            this.your_discard.splice(insertIndex, 0, newTroop);
        }
    }
    else {
        insertIndex = this.my_discard.findIndex(t => t.type > newTroop.type);
        if (insertIndex === -1) {
            this.my_discard.push(newTroop); // end of array
        } else {
            this.my_discard.splice(insertIndex, 0, newTroop);
        }

    }
    

    /* room is reserved in the flex */
    let placeholder = document.createElement('div');
    placeholder.classList.add('troop-placeholder');
    if (player_color == this.RED_COLOR) {
        if (insertIndex === -1) {
            insertIndex = 0; // TODO vérifier
        } else {
            insertIndex = this.your_discard.length - insertIndex - 1; //TODO vérifier le bon index
        }
    }

    if (insertIndex === discardContainer.children.length) {
        discardContainer.appendChild(placeholder);
    } else {
        discardContainer.insertBefore(placeholder, discardContainer.children[insertIndex]);
    }

    const startRect = this.getBoundingClientRectIgnoreZoom(troopElement);
    const targetRect = this.getBoundingClientRectIgnoreZoom(placeholder);

    let deltaX = targetRect.left - startRect.left;
    let deltaY = targetRect.top - startRect.top;

    if (!this.isCurrentPlayerRed() && player_color == this.RED_COLOR) {
        deltaX = -deltaX;
        deltaY = -deltaY;
    }
    else if (this.isCurrentPlayerRed() && player_color == this.BLUE_COLOR) {
        deltaX = -deltaX;
        deltaY = -deltaY;
    }



    const existingTransform = window.getComputedStyle(troopElement).transform;
    const translateTransform = `translate(${deltaX}px, ${deltaY}px)`;
    const newTransform = existingTransform !== "none"
        ? `${existingTransform} ${translateTransform}`
        : translateTransform;

    troopElement.style.transform = newTransform;

    const onTransitionEnd = () => {

        //troopElement.style.transform = existingTransform;
        troopElement.style.transform = '';
        troopElement.style.top = '';
        troopElement.style.left = '';
        troopElement.style.position = '';
        troopElement.style.zIndex = 10; // ATTENTION POUR LES PROCHAINES ACTIONS
        discardContainer.replaceChild(troopElement, placeholder);

        troopElement.removeEventListener('transitionend', onTransitionEnd);
    };
    troopElement.addEventListener('transitionend', onTransitionEnd);
    
    this.showArrays();
},



/*********************************
 * 
 *  a chosen troop from the opponent's rack is discarded 
 *    Troop 5 XB-42
 * 
 ************************************/

notif_discardTroopFromHand: function (notif) {
    console.log('notif_discardTroopFromHand');
    console.log(notif);

    this.showArrays();

    const troop = notif.args.infos_troop;
    const player_color = this.players[troop.type_arg].color;
    const player_color_name = player_color == this.RED_COLOR ? 'red' : 'blue';

    const discardId = `${player_color_name}_discard`;
    const discardContainer = document.getElementById(discardId);
    

    /* check where to remove to troop */
    
    
    if( troop.type_arg == this.player_id) {
        this.removeTroopFromMyHandArray( troop.id);
    }
    else {
        // remove troop from hand JS array
        if( this.isSpectator == false || player_color == this.RED_COLOR ) {
            this.your_hand.pop();
        }
        else { // blue spectator
            this.my_hand.pop();
        }        
    }

    /* check where to insert the troop */
    const newTroop = { id: troop.id, type: troop.type }; 
    let insertIndex;  
    if( this.isSpectator == false || player_color == this.RED_COLOR ) {
        insertIndex = this.your_discard.findIndex(t => t.type > newTroop.type);
        if (insertIndex === -1) {
            this.your_discard.push(newTroop); // end of array
        } else {
            this.your_discard.splice(insertIndex, 0, newTroop);
        }
    }
    else { // blue spectator
        insertIndex = this.my_discard.findIndex(t => t.type > newTroop.type);
        if (insertIndex === -1) {
            this.my_discard.push(newTroop); // end of array
        } else {
            this.my_discard.splice(insertIndex, 0, newTroop);
        }
    }

     /* room is reserved in the flex */
    let placeholder = document.createElement('div');
    placeholder.classList.add('troop-placeholder');
    if (player_color == this.RED_COLOR) {
        if (insertIndex === -1) {
            insertIndex = 0; // TODO vérifier
        } else {
            insertIndex = this.your_discard.length - insertIndex - 1; //TODO vérifier le bon index
        }
    }


   /* troop in hand to remove is defined*/

    if( this.player_id == troop.type_arg) {

        const troopElement = document.getElementById(`troop_${troop.id}`);

        if (insertIndex === discardContainer.children.length) {
            discardContainer.appendChild(placeholder);
        } else {
            discardContainer.insertBefore(placeholder, discardContainer.children[insertIndex]);
        }

        const startRect = this.getBoundingClientRectIgnoreZoom(troopElement);
        const endRect = this.getBoundingClientRectIgnoreZoom(placeholder);

        let deltaX = endRect.left - startRect.left;
        let deltaY = endRect.top - startRect.top;

        const existingTransform = window.getComputedStyle(troopElement).transform;
        const translateTransform = `translate(${deltaX}px, ${deltaY}px)`;
        const newTransform = existingTransform !== "none" 
            ? `${existingTransform} ${translateTransform}` 
            : translateTransform;
        troopElement.style.transform = newTransform;

        const onTransitionEnd = () => {
            //troopElement.style.transform = existingTransform;
            troopElement.style.transform = '';
            troopElement.style.top = '';
            troopElement.style.left = '';
            troopElement.style.position = '';
            troopElement.style.zIndex = 10; // ATTENTION POUR LES PROCHAINES ACTIONS
            troopElement.classList.add('opa_70');
            discardContainer.replaceChild(troopElement, placeholder);

            troopElement.removeEventListener("transitionend", onTransitionEnd);
        };
        troopElement.addEventListener("transitionend", onTransitionEnd);
    }
    else {
        // rename Troop id and unhide it

            // A CONTROLER
                // remove troop from hand JS array
                if( this.isSpectator == false || player_color == this.RED_COLOR ) {
                    this.your_hand.pop();
                }
                else {
                    this.my_hand.pop();
                }
        

        let selected_troop = notif.args.selected_troop;
        if( selected_troop == 0) {
            selected_troop = notif.args.nb_cards_in_hand;
        } 

        let moving_troop_id = `${player_color_name}_troop_${selected_troop}`;

        const troopElement = document.getElementById(moving_troop_id);
        troopElement.id = `troop_${troop.id}`;
        const x = troop.type.toString().slice(-1);
        troopElement.style.backgroundPositionX = `-${x}00%`;

        this.addCustomTooltip(`troop_${troop.id}`, this.getTooltipTroopContent(troop.type, troop.id), 0); 



       //TODO renommer les suivants -1

       if (insertIndex === discardContainer.children.length) {
            discardContainer.appendChild(placeholder);
        } else {
            discardContainer.insertBefore(placeholder, discardContainer.children[insertIndex]);
        }


        const startRect = this.getBoundingClientRectIgnoreZoom(troopElement);
        const endRect = this.getBoundingClientRectIgnoreZoom(placeholder);


        let deltaX = endRect.left - startRect.left;
        let deltaY = endRect.top - startRect.top;
        if( this.isSpectator == false || player_color == this.RED_COLOR ) {
            deltaX = -deltaX;
            deltaY = -deltaY;
        }

        const existingTransform = window.getComputedStyle(troopElement).transform;
        const translateTransform = `translate(${deltaX}px, ${deltaY}px)`;
        const newTransform = existingTransform !== "none" 
            ? `${existingTransform} ${translateTransform}` 
            : translateTransform;
        troopElement.style.transform = newTransform;

        const onTransitionEnd = () => {
            //troopElement.style.transform = existingTransform;
            troopElement.style.transform = '';
            troopElement.style.top = '';
            troopElement.style.left = '';
            troopElement.style.position = '';
            troopElement.style.zIndex = 10; // ATTENTION POUR LES PROCHAINES ACTIONS
            troopElement.classList.add('opa_70');
            discardContainer.replaceChild(troopElement, placeholder);

            for( let i=selected_troop+1; i<=notif.args.nb_cards_in_hand;i++) {
                const troop_id = `${player_color_name}_troop_${i}`;
                let troopElement = document.getElementById(troop_id);
                troopElement.id = `${player_color_name}_troop_${i-1}`;
            }

            troopElement.removeEventListener("transitionend", onTransitionEnd);
        };
        
        troopElement.addEventListener("transitionend", onTransitionEnd);
    }

    this.showArrays();
},



/*********************************
 * 
 *  a chosen Troop goes from board to rack 
 *    Castle Base
 * 
 ************************************/

notif_recoverTroopFromBoard: function (notif) {
    console.log('notif_recoverTroopFromBoard');
    console.log(notif);

    this.showArrays();

    const troop = notif.args.infos_troop;
    
    const player_color = this.players[troop.type_arg].color;
    const player_color_name = player_color == this.RED_COLOR ? 'red' : 'blue';
    const player_color_index = player_color == this.RED_COLOR ? '2' : '1';

    const troopElement = document.getElementById(`troop_${troop.id}`);

    const rackId = `${player_color_name}_rack`;
    const rackContainer = document.getElementById(rackId);
    
    this.removeTroopFromBaseArray(troop);
    //211224this.removeTroopFromBoardArray(troop.id);


    if( troop.type_arg == this.player_id) {

        /* check where to insert the troop */
        const newTroop = { id: troop.id, type: troop.type };
        let insertIndex = this.my_hand.findIndex(t => t.type > newTroop.type);
        if (insertIndex === -1) {
            this.my_hand.push(newTroop); // end of array
        } else {
            this.my_hand.splice(insertIndex, 0, newTroop);
        }

        /* room is reserved in the flex */
        let placeholder = document.createElement('div');
        placeholder.classList.add('troop-placeholder');
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

        const startRect = this.getBoundingClientRectIgnoreZoom(troopElement);
        const targetRect = this.getBoundingClientRectIgnoreZoom(placeholder);

        let deltaX = targetRect.left - startRect.left;
        let deltaY = targetRect.top - startRect.top;

        const existingTransform = window.getComputedStyle(troopElement).transform;
        const translateTransform = `translate(${deltaX}px, ${deltaY}px)`;
        const newTransform = existingTransform !== "none"
            ? `${existingTransform} ${translateTransform}`
            : translateTransform;
        troopElement.style.transform = newTransform;

        const onTransitionEnd = () => {
            troopElement.style.transform = '';
            troopElement.style.top = '';
            troopElement.style.left = '';
            troopElement.style.position = '';
            troopElement.style.zIndex = 10;

            rackContainer.replaceChild(troopElement, placeholder);

            troopElement.removeEventListener('transitionend', onTransitionEnd);         
        };
        this.addCustomTooltip(`troop_${troop.id}`, this.getTooltipTroopContent(troop.type, troop.id), 0); 

        troopElement.addEventListener('transitionend', onTransitionEnd);
    }

    else {
        const newTroop = { type: player_color_index };
        if( this.isSpectator == false || player_color == this.RED_COLOR ) {
            this.your_hand.push(newTroop);
        }
        else { // blue spectator
            this.my_hand.push(newTroop);
        }


        const placeholder = document.createElement('div');
        placeholder.classList.add('troop-placeholder');
        rackContainer.appendChild(placeholder);

        const startRect = this.getBoundingClientRectIgnoreZoom(troopElement);
        const targetRect = this.getBoundingClientRectIgnoreZoom(placeholder);

        let deltaX = targetRect.left - startRect.left;
        let deltaY = targetRect.top - startRect.top;

        if ( this.isSpectator == false || player_color == this.RED_COLOR ) {
            deltaX = -deltaX;
            deltaY = -deltaY;
        }


        const existingTransform = window.getComputedStyle(troopElement).transform;
        const translateTransform = `translate(${deltaX}px, ${deltaY}px)`;
        const newTransform = existingTransform !== "none"
            ? `${existingTransform} ${translateTransform}`
            : translateTransform;
        troopElement.style.transform = newTransform;

        troopElement.style.zIndex = 100;

        const onTransitionEnd = () => {
            troopElement.style.transform = '';
            troopElement.style.top = '';
            troopElement.style.left = '';
            troopElement.style.position = '';
            troopElement.style.zIndex = 10;
            if (player_color == this.RED_COLOR) {
                troopElement.classList.add('board-inverted');
            }


            // troop in rack is renamed
            const troop_index = parseInt(notif.args.nb_troops_hand) + 1;
            if (this.isCurrentPlayerRed()) {
                troopElement.id = `blue_troop_${troop_index}`;
                troopElement.style.backgroundPosition = `-0% -0%`;

            } else if (this.isCurrentPlayerBlue()) {
                troopElement.id = `red_troop_${troop_index}`;
                troopElement.style.backgroundPosition = `-0% -100%`;
            } else if( this.isSpectator == true ){ // spectator
                if (player_color == this.RED_COLOR) {
                    troopElement.id = `red_troop_${troop_index}`;
                    troopElement.style.backgroundPosition = `-0% -100%`;
                } else {
                    troopElement.id = `blue_troop_${troop_index}`;
                    troopElement.style.backgroundPosition = `-0% -0%`;
                }
            }

            rackContainer.replaceChild(troopElement, placeholder);

            troopElement.removeEventListener('transitionend', onTransitionEnd);
        };

        troopElement.addEventListener('transitionend', onTransitionEnd);
    }
    this.showArrays();
},





/*********************************
 * 
 *  a chosen troop goes back from the cemetery to rack 
 *    Cemetery base
 * 
 ***********************************/

notif_recoverTroopFromDiscard: function (notif) {
    console.log('notif_recoverTroopFromDiscard');
    console.log(notif);

    this.showArrays();


    const troop = notif.args.infos_troop;
    
    const player_color = this.players[troop.type_arg].color;
    const player_color_name = player_color == this.RED_COLOR ? 'red' : 'blue';
    const player_color_index = player_color == this.RED_COLOR ? '2' : '1';

    const troopElement = document.getElementById(`troop_${troop.id}`);
    dojo.removeClass(`troop_${troop.id}`, 'opa_70');

    const rackId = `${player_color_name}_rack`;
    const rackContainer = document.getElementById(rackId);


    if( troop.type_arg == this.player_id) {

        const newTroop = { id: troop.id, type: troop.type };

        let insertIndex = this.my_hand.findIndex(t => t.type > newTroop.type);
        if (insertIndex === -1) {
            this.my_hand.push(newTroop); // end of array
        } else {
            this.my_hand.splice(insertIndex, 0, newTroop);
        }

        let placeholder = document.createElement('div');
        placeholder.classList.add('troop-placeholder');
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

        const startRect = this.getBoundingClientRectIgnoreZoom(troopElement);
        const targetRect = this.getBoundingClientRectIgnoreZoom(placeholder);

        let deltaX = targetRect.left - startRect.left;
        let deltaY = targetRect.top - startRect.top;


        const existingTransform = window.getComputedStyle(troopElement).transform;
        const translateTransform = `translate(${deltaX}px, ${deltaY}px)`;
        const newTransform = existingTransform !== "none"
            ? `${existingTransform} ${translateTransform}`
            : translateTransform;

        troopElement.style.transform = newTransform;

        const onTransitionEnd = () => {
            troopElement.style.transform = '';
            troopElement.style.top = '';
            troopElement.style.left = '';
            troopElement.style.position = '';
            troopElement.style.zIndex = 10;

            rackContainer.replaceChild(troopElement, placeholder);

            troopElement.removeEventListener('transitionend', onTransitionEnd);
        };

        troopElement.addEventListener('transitionend', onTransitionEnd);
    }

    else {
        const newTroop = { type: player_color_index };
        if( this.isSpectator == false || player_color == this.RED_COLOR ) {
            this.your_hand.push(newTroop);
        }
        else { // blue spectator
            this.my_hand.push(newTroop);
        }

        const placeholder = document.createElement('div');
        placeholder.classList.add('troop-placeholder');
        rackContainer.appendChild(placeholder);

        const startRect = this.getBoundingClientRectIgnoreZoom(troopElement);
        const targetRect = this.getBoundingClientRectIgnoreZoom(placeholder);

        let deltaX = targetRect.left - startRect.left;
        let deltaY = targetRect.top - startRect.top;

        if ( this.isSpectator == false || player_color == this.RED_COLOR ) {
            deltaX = -deltaX;
            deltaY = -deltaY;
        }


        const existingTransform = window.getComputedStyle(troopElement).transform;
        const translateTransform = `translate(${deltaX}px, ${deltaY}px)`;
        const newTransform = existingTransform !== "none"
            ? `${existingTransform} ${translateTransform}`
            : translateTransform;

        troopElement.style.transform = newTransform;
        troopElement.style.zIndex = 100;


        const onTransitionEnd = () => {
            troopElement.style.transform = '';
            troopElement.style.top = '';
            troopElement.style.left = '';
            troopElement.style.position = '';
            troopElement.style.zIndex = 10;
            if (player_color == this.RED_COLOR) {
                troopElement.classList.add('board-inverted');
            }

            const troop_index = parseInt(notif.args.nb_troops_hand) + 1;
            if (this.isCurrentPlayerRed()) {
                troopElement.id = `blue_troop_${troop_index}`;
                troopElement.style.backgroundPosition = `-0% -0%`;

            } else if (this.isCurrentPlayerBlue()) {
                troopElement.id = `red_troop_${troop_index}`;
                troopElement.style.backgroundPosition = `-0% -100%`;
            } else if( this.isSpectator == true ){ // spectator
                if (player_color == this.RED_COLOR) {
                    troopElement.id = `red_troop_${troop_index}`;
                    troopElement.style.backgroundPosition = `-0% -100%`;
                } else {
                    troopElement.id = `blue_troop_${troop_index}`;
                    troopElement.style.backgroundPosition = `-0% -0%`;
                }
            }

            rackContainer.replaceChild(troopElement, placeholder);

            troopElement.removeEventListener('transitionend', onTransitionEnd);
        };

        troopElement.addEventListener('transitionend', onTransitionEnd);
    }
    this.showArrays();
},

/*********************************
 * 
 *  a chosen troop moves from board to board 
 *    Volcano Base
 * 
 ***********************************/

notif_moveTroopBoardToBoard: function (notif) {
    console.log('notif_moveTroopBoardToBoard');
    console.log(notif);

    this.showArrays();

    const TB_bases = this.bases[this.board_name];

    const troop = notif.args.infos_troop;
    
    const player_color = this.players[troop.type_arg].color;
    const player_color_name = player_color == this.RED_COLOR ? 'red' : 'blue';
    const player_color_index = player_color == this.RED_COLOR ? '2' : '1';

    const troopElement = document.getElementById(`troop_${troop.id}`);
    troopElement.style.zIndex = notif.args.ordre * 10;

    this.removeTroopFromBaseArray(troop);
    //211224this.removeTroopFromBoardArray(troop.id);

    this.troops_on_bases[notif.args.base_id].push(troop);
    this.createBaseTooltip(notif.args.base_id);

    const destination_id = `${player_color_name}_base_${this.board_name}_${notif.args.base_id}`;
    const destinationContainer = document.getElementById(destination_id);

    const startRect = this.getBoundingClientRectIgnoreZoom(troopElement);
    const endRect = this.getBoundingClientRectIgnoreZoom(destinationContainer);

    let deltaX = endRect.left - startRect.left;
    let deltaY = endRect.top - startRect.top;

    if (this.isCurrentPlayerRed() && player_color == this.BLUE_COLOR) {
        deltaX = -deltaX;
        deltaY = -deltaY;
    }
    else if (this.isCurrentPlayerBlue() && player_color == this.RED_COLOR) {
        deltaX = -deltaX;
        deltaY = -deltaY;
    }
    else if( this.isSpectator && player_color == this.RED_COLOR) {
        deltaX = -deltaX;
        deltaY = -deltaY;
    }

    const existingTransform = window.getComputedStyle(troopElement).transform;
    const translateTransform = `translate(${deltaX}px, ${deltaY}px)`;
    const newTransform = existingTransform !== "none"
        ? `${existingTransform} ${translateTransform}`
        : translateTransform;

    troopElement.style.transform = newTransform;

    const onTransitionEnd = () => {

        troopElement.style.top = destinationContainer.style.top;
        troopElement.style.left = destinationContainer.style.left;
        troopElement.style.transition = 'none';
        troopElement.style.transform = existingTransform;      
    
        troopElement.removeEventListener("transitionend", onTransitionEnd);
    };

    troopElement.addEventListener("transitionend", onTransitionEnd);

    this.showArrays();
},



/*********************************
 * 
 *  a chosen troop is hidden for next round 
 *    Battlefield Base
 * 
 ***********************************/

notif_hideTroopOnRackPrivate: function (notif) {
    console.log('notif_hideTroopOnRackPrivate');
    console.log(notif);

//    this.showArrays();


    const player_color = this.players[notif.args.player_id].color;
    const other_player_color_name = player_color == this.RED_COLOR ? 'blue' : 'red';
    const check_name = player_color == this.RED_COLOR ? 'check_red' : 'check_blue';

    const troop = notif.args.infos_troop_before;
    
    const troopElement = document.getElementById(`troop_${troop.id}`);

    const checkElement = document.createElement('div');
    checkElement.id = `check_${troop.id}`;
    checkElement.classList.add('checks', check_name);


    troopElement.appendChild(checkElement);  



//    this.showArrays();

},

notif_hideTroopOnRackPublic: function (notif) {
    console.log('notif_hideTroopOnRackPublic');
    console.log(notif);

    //this.showArrays();

    

    if( notif.args.player_id != this.opponent_id || this.isSpectator) {
        const player_color = this.players[notif.args.player_id].color;
        const other_player_color_name = player_color == this.RED_COLOR ? 'blue' : 'red';
        const check_name = player_color == this.RED_COLOR ? 'check_red' : 'check_blue';
    
        const troopElement = document.getElementById(`${other_player_color_name}_troop_${notif.args.card_blocked}`);

        const checkElement = document.createElement('div');
        checkElement.id = `check_${notif.args.card_blocked}`;
        checkElement.classList.add('checks', check_name);
        troopElement.appendChild(checkElement);  
    }

            

    //this.showArrays();

},

notif_unhideTroopOnRack: function( notif )
{
    console.log('notif_unhideTroopOnRack');
    console.log(notif);

    const player_color = this.players[notif.args.player_id].color;
    const check_name = player_color == this.RED_COLOR ? 'check_blue' : 'check_red';

    const elements = document.querySelectorAll(`.${check_name}`);
    elements.forEach(element => {
        element.remove();
    });

    const rack_name = player_color == this.RED_COLOR ? 'red_rack' : 'blue_rack';

    if( rack_name == 'red_rack' && this.isCurrentPlayerRed == 'false') {
        const rackElement = document.getElementById('red_rack');
        const children = Array.from(rackElement.children); // Récupérer tous les enfants
        children.forEach((child, index) => {
            child.id = `red_troop_${index + 1}`; // Renommer chaque enfant
        });
    }

    if( rack_name == 'blue_rack' && this.isCurrentPlayerBlue == 'false') {
        const rackElement = document.getElementById('blue_rack');
        const children = Array.from(rackElement.children); // Récupérer tous les enfants
        children.forEach((child, index) => {
            child.id = `blue_troop_${index + 1}`; // Renommer chaque enfant
        });
    }


},

/*********************************
 * 
 *  Regions are occupied and Medals are won 
 * 
 ************************************/

notif_gainMedal: function (notif) {
    console.log('notif_gainMedal');
    console.log(notif);

    //this.showArrays();

    let medals_already_won = this.players[notif.args.player_id].star; 
    let index = 1;
    const TB_medals = this.medals[this.board_name];

    this.players[notif.args.player_id].star += notif.args.nb_medal;

    const timeoutDelay = 200;
    Object.entries(TB_medals).forEach(([id, medal]) => {
        if (notif.args.emptied_regions.includes(medal.region)) {    
            const medalId = `medal_${id}`;
            const medalElement = document.getElementById(medalId);
            const medalDestination = document.getElementById(`medal_${notif.args.player_id}_${parseInt(medals_already_won) + index}`);
            
            
            const animationDelay = index * 500; // 500ms per medal
            index++;
            setTimeout(() => {
                medalElement.style.transform = 'scale(5)';
                setTimeout(() => {
                    // Réduire l'échelle pour la faire disparaître
                    medalElement.style.transform = 'scale(0)';
                    
                    if (medalDestination) 
                    {
                        // Après disparition, traiter la médaille destination
                        setTimeout(() => {
                            // Étape 2 : Modifier la médaille destination
                            medalDestination.classList.remove('null_medal');
                            medalDestination.classList.add('full_medal');
        
                            // Augmenter la taille de la médaille destination
                            medalDestination.style.transform = 'scale(2)';
        
                            setTimeout(() => {
                                // Réduire l'échelle de la médaille destination à sa taille normale
                                medalDestination.style.transform = 'scale(1)';
                                // Réinitialiser l'index pour la prochaine médaille
                                
                            }, timeoutDelay); // Durée pour redescendre à `scale(1)`
                        }, timeoutDelay); // Durée après disparition de la médaille source
                    }
                }, timeoutDelay); // Durée pour agrandir et réduire la médaille source
            }, animationDelay); // Décalage pour chaque médaille
        }
    });

    

    //this.showArrays();
},


notif_score: function( notif ){
    console.log('notif_scorel');
    console.log(notif);
    this.scoreCtrl[ notif.args.playerid ].toValue( notif.args.score );
},

notif_message_allplayers_without_player: function( notif )
{
    console.log('notif_message_allplayers_without_player');
    
    // juste un message envoyé en php
},









});             
});
