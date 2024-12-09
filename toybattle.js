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

define([
    "dojo","dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter",
],
function (dojo, declare) {
    return declare("bgagame.toybattle",  ebg.core.gamegui, {

        constructor: function(){
            console.log('toybattle constructor');
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
        
setup: function( gamedatas )
{
    console.log( "Starting game setup" );

    console.log('gamedatas');
    console.log(gamedatas);
               
    // TODO: Set up your game interface here, according to "gamedatas"

    this.players = gamedatas.players; // A RAJOUTER/NE PAS SUPPRIMER POUR MOTEUR (UTILITY METHODS)

    this.connections = [];

    this.bases = gamedatas.bases;
    this.zones = gamedatas.zones;
    this.board_name = gamedatas.board_name;
    this.board_id = gamedatas.board_id;

    this.boards = ["castle", "pool", "clouds", "jungle", "cemetery", "carribean","station", "battlefield"];
    this.medals_to_win = [7, 6, 8, 7, 7, 5, 7, 8];

    this.BLUE_COLOR = "4f66a2";
    this.RED_COLOR = "d1553e";
    this.BLUE = 0;
    this.RED = 1;

     //TODO check if spectator is always BLUE
    this.spectator_id =  gamedatas.spectator_id;
    this.other_player_id =  gamedatas.other_player_id;

    this.board_troops = gamedatas.board_troops;

    this.my_hand = gamedatas.my_hand;
    this.your_hand = gamedatas.your_hand;

    this.setupPlayersBoard();
    this.setupBoard();


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
    console.log( 'Entering state: '+stateName, args );
    
    switch( stateName )    {
    
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
                    $('pagemaintitletext').innerHTML = 	this.format_string_recursive(_(args.args.titleyou).replace('${you}', this.divYou()).replace('#nb#',args.args.nb).replace('#nb2#',args.args.nb2).replace('#icon#',args.args.icon).replace('#icon2#',args.args.icon2), args.args);   
                }   
            }   
            else
            {
                if(args.args.title != null) {
                    $('pagemaintitletext').innerHTML = this.format_string_recursive(_(args.args.title).replace('${actplayer}', this.divActPlayer()).replace('#nb#',args.args.nb).replace('#nb2#',args.args.nb2).replace('#icon#',args.args.icon).replace('#icon2#',args.args.icon2), args.args);  
                }
            }

            if (this.prefs[101].value == 2)
            {
                //SELECTIONNER TOUTES LES CLASSES QUI PEUVENT CLIGNOTER ET LEUR AJOUTER UNE CLASSE SANS CLIGNOTEMENT

            }

            if (this.prefs[102].value == 2)
            {
                //SELECTIONNER TOUTES LES CLASSES QUI PEUVENT AVOIR UNE ANIMATION GENANT EN BOUCLE ET LEUR AJOUTER UNE CLASSE AVEC UNE ANIMATION MODEREE

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
    console.log( 'Leaving state: '+stateName );
    
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
                        case "cancel":
                            this.addActionButton('cancel', _("Cancel"), 'onOpButton', null, null, 'red');
                            break;
                        case "pass":
                            this.addActionButton('pass', _("Pass"), 'onOpButton', null, null, 'red');
                            break;
                        case "draw_2":
                            this.addActionButton('draw_2', _("Draw 2 Troops"), 'onOpButton', null, null, 'blue');
                            break;
                        case "draw_1":
                            this.addActionButton('draw_1', _("Draw 1 Troop"), 'onOpButton', null, null, 'blue');
                            break;
                        case "place_troop":
                            this.addActionButton('place_troop', _("Place 1 Troop"), 'onOpButton', null, null, 'blue');
                            break;
                        case "yes":
                            this.addActionButton('yes', _("Yes"), 'onOpButton', null, null, 'blue');
                            break;
                        case "no":
                            this.addActionButton('no', _("No"), 'onOpButton', null, null, 'red');
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

attachToNewParentNoDestroy: function (mobile_in, new_parent_in, relation, place_position) 
{
    const mobile = $(mobile_in);
    const new_parent = $(new_parent_in);

    const src = dojo.position(mobile);
    if (place_position)
        mobile.style.position = place_position;
    dojo.place(mobile, new_parent, relation);
    mobile.offsetTop;//force re-flow
    const tgt = dojo.position(mobile);
    const box = dojo.marginBox(mobile);
    const cbox = dojo.contentBox(mobile);
    const left = box.l + src.x - tgt.x;
    const top = box.t + src.y - tgt.y;

    mobile.style.position = "absolute";
    mobile.style.left = left + "px";
    mobile.style.top = top + "px";
    box.l += box.w - cbox.w;
    box.t += box.h - cbox.h;
    mobile.offsetTop;//force re-flow
    return box;
},


/*************************************************
 * 
 *  setup connections from this.args.selectable
 * on each beginning of new State (Player Turn)
 * 
 ************************************************/

setupConnections: function(selectables) {
    this.connections = [];
    this.resources_list = [];
    console.log( 'conneCtions');
    selectables.forEach(elt_id => {
        console.log( 'elt_id', elt_id);
        const element = document.getElementById(elt_id);

        const resourceClickHandler = (evt) => this.onSelect(evt);
        element.addEventListener('click', resourceClickHandler);
        this.connections.push({ element, event: 'click', handler: resourceClickHandler });
    });
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
        element.removeEventListener(event, handler);  // Supprime l'événement
    });
    this.connections = [];  // Vide le tableau des connexions
},




setupPlayersBoard: function() {
    Object.values(this.gamedatas.players).forEach(player => {
        const playerBoardElement = document.getElementById('player_board_' + player.id);
        playerBoardElement.insertAdjacentHTML('beforeend', `
            <div class="a_board" id="a1_board_${player.id}"></div>
            <div class="a_board" id="a2_board_${player.id}"></div>
        `);

    });

},


/**************************
 * 
 * board is created from top to bottom for Blue player
 * and inverted for Red player
 * 
 */

setupBoard: function() {

console.log( 'setup Board');

console.log( 'Red Player ? ',this.isCurrentPlayerRed());

    /*  creates global container with yourLine, playMat and myLine containers  */
    const globalContainer = document.getElementById('global_id');
    if( this.isCurrentPlayerRed() ) {
        console.log( 'golbal_id inverted');
        globalContainer.classList.add('board-inverted');
    }

    /*  redLineContainer definition 
        contains redDeckElement and redRackContainer
    */
    const redLineContainer = document.createElement('div');
    redLineContainer.id = `red_line`;
    redLineContainer.classList.add('line');
    globalContainer.appendChild(redLineContainer);


    /*  redRackContainer definition 
        contains Rack and all Troops in Hand
    */
    const redRackContainer = document.createElement('div');
    redRackContainer.id = `red_rack`;
    redRackContainer.classList.add('rack');
    redRackContainer.classList.add('linear_red');
    redLineContainer.appendChild(redRackContainer);

    if( this.isCurrentPlayerRed() ) {
        Object.values(this.my_hand).reverse().forEach(troop => {
            const troopElement = document.createElement('div');
            troopElement.id = `troop_${troop.id}`;
            troopElement.classList.add('troop');

            // troop.type < 10 -> backside
            const troop_type =  troop.type < 10 ? 0 : troop.type % 10;
            const troop_color = troop.type < 10 ? troop.type - 1 : Math.floor(troop.type / 10) - 1;
            troopElement.style.backgroundPosition = `-${troop_type}00% -${troop_color}00%`;

            troopElement.classList.add('board-inverted');
            redRackContainer.appendChild(troopElement);
        });
    }
    else {
        Object.values(this.gamedatas.your_hand).forEach((troop, index) => {
            const troopElement = document.createElement('div');
            troopElement.id = `red_troop_${index + 1}`;
            troopElement.classList.add('troop');
            troopElement.style.backgroundPosition = `0% -100%`;

            troopElement.classList.add('board-inverted');
            redRackContainer.appendChild(troopElement);
        });
    }



    /*  yourDeckContainer definition 
        contains Deck and number of Troops TODO
    */
        const redDeckElement = document.createElement('div');
        redDeckElement.id = `red_deck`;
        redDeckElement.classList.add('troop');
        redDeckElement.style.backgroundPosition = `0% -100%`;

        redDeckElement.classList.add('board-inverted');
        redLineContainer.appendChild(redDeckElement);

   
    /*  PlaymatContainer definition 
        contains blueDiscard, Board and redDiscard
    */
    const playmatContainer = document.createElement('div');
    playmatContainer.id = `playmat_id`;
    playmatContainer.classList.add('playmat');
    globalContainer.appendChild(playmatContainer);

    /*  blueDiscardContainer definition 
        contains possible Troops in opacity 50 and all discarded ones TODO
    */
    const blueDiscardContainer = document.createElement('div');
    blueDiscardContainer.id = `discard_blue`;
    blueDiscardContainer.classList.add('discard', 'linear_blue');
    
    // 8 troops in opa 50
    for( let i=1; i<9; i++ ) {
        const blueDiscardTroop = document.createElement('div');
        blueDiscardTroop.id = `discard_blue_${i}`;
        blueDiscardTroop.classList.add('troop','opa_50');
        blueDiscardTroop.style.backgroundPosition = `-${i}00% -000%`;
        blueDiscardContainer.appendChild(blueDiscardTroop);
    }
    playmatContainer.appendChild(blueDiscardContainer);

    /*  boardContainer definition 
        contains board and all troops
    */   
    const boardContainer = document.createElement('div');
    boardContainer.id = `board_${this.board_id}`;
    boardContainer.classList.add('board');

    const background_x = (this.board_id - 1) % 4;  
    const background_y = Math.floor( (this.board_id - 1) / 4);
    boardContainer.style.backgroundPosition = `-${background_x}00% -${background_y}00%`;


    const TB_bases = this.bases[this.board_name];

    // defines bases position from TB_bases array */
    for (const baseId of Object.keys(TB_bases)) {
        const baseData = TB_bases[baseId];
        // big base element
        const baseElement = document.createElement('div');
        baseElement.id = `base_${this.board_name}_${baseId}`;
        baseElement.classList.add('base_all');
        
        baseElement.style.top = `${baseData.top}%`;
        baseElement.style.left = `${baseData.left}%`;
        boardContainer.appendChild(baseElement);

        // blue base element
        const baseBlueElement = document.createElement('div');
        baseBlueElement.id = `blue_base_${this.board_name}_${baseId}`;
        baseBlueElement.classList.add('base');
        baseBlueElement.style.top = `${baseData.top}%`;
        baseBlueElement.style.left = `${baseData.left}%`;
        boardContainer.appendChild(baseBlueElement);

        // red base element
        const baseRedElement = document.createElement('div');
        baseRedElement.id = `red_base_${this.board_name}_${baseId}`;
        baseRedElement.classList.add('base');
        baseRedElement.style.top = `${baseData.top+2.5}%`;
        baseRedElement.style.left = `${baseData.left}%`;
        boardContainer.appendChild(baseRedElement);
    }


        
    
    Object.values(this.board_troops).forEach(troop => {
        const troopElement = document.createElement('div');
        troopElement.id = `troop_${troop.id}`;
        troopElement.classList.add('troop');
        // defines troop type and color
        const troop_type =  troop.type < 10 ? 0 : troop.type % 10;
        const troop_color = troop.type < 10 ? troop.type - 1 : Math.floor(troop.type / 10)-1;
        troopElement.style.backgroundPosition = `-${troop_type}00% -${troop_color}00%`;

        boardContainer.appendChild(troopElement);

      
        // defines position on board from TB_bases array
        const baseData = TB_bases[troop.location_arg];
        troopElement.style.position = 'absolute';
        troopElement.style.top = troop_color == this.BLUE ? `${baseData.top}%` : `${baseData.top+2.5}%`; // red troops are 2.5% down
        troopElement.style.left = `${baseData.left}%`;
        troopElement.style.zIndex = 10 * troop.ordre;
        if( troop_color == this.RED ) {
            troopElement.classList.add('board-inverted');
        }
    });




    playmatContainer.appendChild(boardContainer);

    /* redDiscard */
    const redDiscardContainer = document.createElement('div');
    redDiscardContainer.id = `discard_red`;
    redDiscardContainer.classList.add('discard', 'linear_red');
    playmatContainer.appendChild(redDiscardContainer);

    for (let i = 8; i >= 1; i--) {
        const redDiscardTroop = document.createElement('div');
        redDiscardTroop.id = `discard_red_${i}`;
        redDiscardTroop.classList.add('troop','opa_50');
        redDiscardTroop.style.backgroundPosition = `-${i}00% -100%`;
        redDiscardTroop.classList.add('board-inverted');
        redDiscardContainer.appendChild(redDiscardTroop);
    }    

    /* blueLineContainer */
    const blueLineContainer = document.createElement('div');
    blueLineContainer.id = `blue_line`;
    blueLineContainer.classList.add('line');
    globalContainer.appendChild(blueLineContainer);

    /* blueDeckElement */
    const blueDeckElement = document.createElement('div');
    blueDeckElement.id = `blue_deck`;
    blueDeckElement.classList.add('troop');
    blueDeckElement.style.backgroundPosition = `0% 0%`;
    blueLineContainer.appendChild(blueDeckElement);

    /* blueRackContainer */
    const blueRackContainer = document.createElement('div');
    blueRackContainer.id = `blue_rack`;
    blueRackContainer.classList.add('rack');
    blueRackContainer.classList.add('linear_blue');
    blueLineContainer.appendChild(blueRackContainer);
    if( this.isCurrentPlayerRed() ) {
        Object.values(this.gamedatas.your_hand).forEach((troop, index) => {
            const troopElement = document.createElement('div');
            troopElement.id = `blue_troop_${index + 1}`;
            troopElement.classList.add('troop');
            troopElement.style.backgroundPosition = `-0% -0%`;
            blueRackContainer.appendChild(troopElement);
        });
    }
    else if( this.isSpectator) {
        Object.values(this.gamedatas.my_hand).forEach((troop, index) => {
            const troopElement = document.createElement('div');
            troopElement.id = `blue_troop_${index + 1}`;
            troopElement.classList.add('troop');
            troopElement.style.backgroundPosition = `-0% -0%`;
            blueRackContainer.appendChild(troopElement);
        });
    }
    else {
        Object.values(this.gamedatas.my_hand).forEach(troop => {
            const troopElement = document.createElement('div');
            troopElement.id = `troop_${troop.id}`;
            troopElement.classList.add('troop');

            const troop_type =  troop.type < 10 ? 0 : troop.type % 10;
            const troop_color = troop.type < 10 ? troop.type - 1 : Math.floor(troop.type / 10)-1;
            troopElement.style.backgroundPosition = `-${troop_type}00% -${troop_color}00%`;
            blueRackContainer.appendChild(troopElement);
        });
            
    }
    
 /*  FORMER TEST 8 TROOPS ON A RACK 
    for( let i=1; i<9; i++) {
        const troopElementBlue = document.createElement('div');
        troopElementBlue.id = `troop_red_${i}`;
        troopElementBlue.classList.add('troop');

        myRackContainer.appendChild(troopElementBlue);
    }*/


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
        ['drawTroopPublic', 1]

    ];

    notifs.forEach((notif) => {
        dojo.subscribe(notif[0], this, `notif_${notif[0]}`);
        this.notifqueue.setSynchronous(notif[0], notif[1]);
    });

},

notif_displayNotif: function(notif)
{
    console.log('notif_displayNotif');
    console.log(notif);
},



    /*********************************
     * 
     * one troop is moved from rack to board
     * 
     * 
     */

notif_moveTroop: function(notif)
{
    console.log('notif_moveTroop');
    console.log(notif);

    const player_color = this.players[notif.args.player_id].color;
    const boardContainer = document.getElementById(`board_${this.board_id}`);
    const TB_bases = this.bases[this.board_name];

    /* animation for the active player */
    if( this.player_id == notif.args.player_id) {
        const troopContainer = document.getElementById(notif.args.mobile);
        
        const destination_id = this.isCurrentPlayerRed() ? 'red_'+notif.args.parent : 'blue_'+notif.args.parent;
        const destinationContainer = document.getElementById(destination_id);
        
        const startRect = this.getBoundingClientRectIgnoreZoom(troopContainer);
        const endRect = this.getBoundingClientRectIgnoreZoom(destinationContainer);
        let deltaX = endRect.left - startRect.left;
        let deltaY = endRect.top - startRect.top;

        troopContainer.style.zIndex = notif.args.infos_troop.ordre * 10;

        // gets rotation, if defined
        const existingTransform = window.getComputedStyle(troopContainer).transform;

        // new transformation
        const translateTransform = `translate(${deltaX}px, ${deltaY}px)`;
        const newTransform = existingTransform !== "none" 
            ? `${existingTransform} ${translateTransform}` 
            : translateTransform;
        troopContainer.style.transform = newTransform;
    
        const onTransitionEnd = () => {
            // restore former transform
            troopContainer.style.transform = existingTransform;

            // defines postion on board
            const baseData = TB_bases[notif.args.infos_troop.location_arg];
            troopContainer.style.position = 'absolute';

            const troopColor =  Math.floor(notif.args.infos_troop.type / 10)-1;
            troopContainer.style.top = troopColor == this.BLUE ? `${baseData.top}%` : `${baseData.top+2.5}%`; // red troops are 2.5% down
            troopContainer.style.left = `${baseData.left}%`;

            boardContainer.appendChild(troopContainer);
            troopContainer.removeEventListener("transitionend", onTransitionEnd);
        };
        
        troopContainer.addEventListener("transitionend", onTransitionEnd);
    }
    else {

        // rename Troop id and unhide it
        let moving_troop_id = `red_troop_${notif.args.nb_troops_hand}`;
        if( player_color == this.BLUE_COLOR ) {
            moving_troop_id = `blue_troop_${notif.args.nb_troops_hand}`;
        }

        const troopContainer = document.getElementById(moving_troop_id);
        troopContainer.id = `troop_${notif.args.infos_troop.id}`;
        const x = notif.args.infos_troop.type.toString().slice(-1);
        troopContainer.style.backgroundPositionX = `-${x}00%`;
    
        
        const destination_id = this.isCurrentPlayerRed() ? 'blue_'+notif.args.parent : 'red_'+notif.args.parent;
        const destinationContainer = document.getElementById(destination_id);
    
        const startRect = this.getBoundingClientRectIgnoreZoom(troopContainer);
        const endRect = this.getBoundingClientRectIgnoreZoom(destinationContainer);
        let deltaX = endRect.left - startRect.left;
        let deltaY = endRect.top - startRect.top;
        if( this.isSpectator == false || player_color == this.RED_COLOR ) {
            deltaX = -deltaX;
            deltaY = -deltaY;
        }
    
        troopContainer.style.zIndex = notif.args.infos_troop.ordre * 10;
    


        // gets rotation, if defined
        const existingTransform = window.getComputedStyle(troopContainer).transform;
 
        
        // new transformation
        const translateTransform = `translate(${deltaX}px, ${deltaY}px)`;
        const newTransform = existingTransform !== "none"
            ? `${existingTransform} ${translateTransform}`
            : translateTransform;
    
        troopContainer.style.transform = newTransform;
    
        const onTransitionEnd = () => {

            troopContainer.style.transform = existingTransform;

            const baseData = TB_bases[notif.args.infos_troop.location_arg];
            troopContainer.style.position = 'absolute';

            const troopColor =  Math.floor(notif.args.infos_troop.type / 10)-1;

            troopContainer.style.top = troopColor == this.BLUE ? `${baseData.top}%` : `${baseData.top+2.5}%`; // red troops are 2.5% down
            troopContainer.style.left = `${baseData.left}%`;

            boardContainer.appendChild(troopContainer);
            troopContainer.removeEventListener("transitionend", onTransitionEnd);
        };
    
        troopContainer.addEventListener("transitionend", onTransitionEnd);
    }
},


    /*********************************
     * 
     * one or two troops are drawn. Animation for active players
     * 
     * 
     */

    notif_drawTroopPrivate: function (notif) {
        console.log('notif_drawTroopPrivate');
        console.log(notif);

        const player_color = this.players[notif.args.player_id].color;
        const deckId = player_color == this.BLUE_COLOR ? 'blue_deck' : 'red_deck';
        const rackId = player_color == this.BLUE_COLOR ? 'blue_rack' : 'red_rack';
    
        const deckContainer = document.getElementById(deckId);
        const rackContainer = document.getElementById(rackId);

        console.log('rackContainer start');
        console.log(rackContainer);
        console.log(rackContainer.children);
    
        const animateTroop = (troop, index) => {
            /* troop is created and added to the deck */ 
            const troopElement = document.createElement('div');
            troopElement.id = `troop_${troop.id}`;
            troopElement.classList.add('troop');
            const troop_type = troop.type % 10;
            const troop_color = Math.floor(troop.type / 10) - 1;
            troopElement.style.backgroundPosition = `-${troop_type}00% -${troop_color}00%`;
            deckContainer.appendChild(troopElement);
    
            /* check where to insert the troop */
            const newTroop = { id: troop.id, type: troop.type };

            let insertIndex = this.my_hand.findIndex(t => t.type > newTroop.type);
              if (insertIndex === -1) {
                this.my_hand.push(newTroop); // end of array
            } else {
                this.my_hand.splice(insertIndex, 0, newTroop);
            }
    
            /* room is reserved in the flex */
            const placeholder = document.createElement('div');
            placeholder.classList.add('troop-placeholder');
            if( player_color == this.RED_COLOR ) {
                if( insertIndex === -1) {
                    insertIndex = 0; // TODO vérifier
                }
                else {
                    insertIndex = this.my_hand.length - insertIndex - 1; //TODO vérifier le bon index
                }
            }

            if (insertIndex === rackContainer.children.length) {
                rackContainer.appendChild(placeholder);
            } else {
                rackContainer.insertBefore(placeholder, rackContainer.children[insertIndex]);
            }
            console.log('rackContainer after addition');
            console.log(rackContainer);
            console.log(rackContainer.children);

            const startRect = this.getBoundingClientRectIgnoreZoom(deckContainer);
            const targetRect = this.getBoundingClientRectIgnoreZoom(placeholder);
    
            const deltaX = targetRect.left - startRect.left;
            const deltaY = targetRect.top - startRect.top;
    
            troopElement.style.transform = `translate(${deltaX}px, ${deltaY}px)`;
            troopElement.style.zIndex = 100;
    
            troopElement.addEventListener('transitionend', () => {
                
                troopElement.style.transform = '';
                if( player_color == this.RED_COLOR ) {
                    troopElement.classList.add('board-inverted');
                }
                // placeholder is removed
                
                rackContainer.replaceChild(troopElement, placeholder);
                console.log('rackContainer after replace');
                console.log(rackContainer);
                console.log(rackContainer.children);
    
                // call next animation
                if (index + 1 < notif.args.new_troops.length) {
                    animateTroop(notif.args.new_troops[index + 1], index + 1);
                }
            });
        };
    
        if (notif.args.new_troops.length > 0) {
            animateTroop(notif.args.new_troops[0], 0);
        }
        console.log('rackContainer end');
        console.log(rackContainer);
        console.log(rackContainer.children);
    },


    /*********************************
     * 
     * one or two troops are drawn. Animation shown to other players
     * active player is not affected
     * 
     */

    notif_drawTroopPublic: function(notif)
    {
        console.log('notif_drawTroopPublic');
        console.log(notif);
    
        const player_color = this.players[notif.args.player_id].color;
        const deckId = player_color == this.BLUE_COLOR ? 'blue_deck' : 'red_deck';
        const rackId = player_color == this.BLUE_COLOR ? 'blue_rack' : 'red_rack';
        const deckContainer = document.getElementById(deckId);
        const rackContainer = document.getElementById(rackId);

        if( this.player_id != notif.args.player_id) {
    
            const animateTroop = (index) => {
                /* troop is created and added to the deck */
                let troopElement = document.createElement('div');
                troopElement.classList.add('troop');

                if( this.isCurrentPlayerRed() ){
                    troopElement.id = `blue_troop_${index + parseInt(notif.args.nb_troops_hand) + 1}`;
                    troopElement.style.backgroundPosition = `-0% -0%`;

                }
                else if( this.isCurrentPlayerBlue() ){
                    troopElement.id = `red_troop_${index + parseInt(notif.args.nb_troops_hand) + 1}`;
                    troopElement.style.backgroundPosition = `-0% -100%`;               
                }

                else{ // spectator
                    if( player_color == this.RED_COLOR ) {
                        troopElement.id = `red_troop_${index + parseInt(notif.args.nb_troops_hand) + 1}`;
                        troopElement.style.backgroundPosition = `-0% -100%`; 
                    }
                    else {
                        troopElement.id = `blue_troop_${index + parseInt(notif.args.nb_troops_hand) + 1}`;
                        troopElement.style.backgroundPosition = `-0% -0%`;                    
                    }
                }
                deckContainer.appendChild(troopElement);

                /* room is reserved in the flex */
                const placeholder = document.createElement('div');
                placeholder.classList.add('troop-placeholder');
                rackContainer.appendChild(placeholder);

                const startRect = this.getBoundingClientRectIgnoreZoom(deckContainer);
                const targetRect = this.getBoundingClientRectIgnoreZoom(placeholder);
        
                let deltaX = targetRect.left - startRect.left;
                let deltaY = targetRect.top - startRect.top;

                if( this.isSpectator == false || player_color == this.RED_COLOR) {
                    deltaX = -deltaX;
                    deltaY = -deltaY;
                }

                troopElement.style.transform = `translate(${deltaX}px, ${deltaY}px)`;
                troopElement.style.zIndex = 100;
        
                troopElement.addEventListener('transitionend', () => {
                    troopElement.style.transform = '';
                    // board inversion is restored for Red player
                    if( player_color == this.RED_COLOR ) {
                        troopElement.classList.add('board-inverted');
                    }
                    // placeholder is removed
                    rackContainer.replaceChild(troopElement, placeholder);
        
                    // next animation is called
                    if (index + 1 < notif.args.nb_troops) {
                        animateTroop(index + 1);
                    }
                });
            };
    
            if (notif.args.nb_troops > 0) {
                animateTroop(0);
            }
        }

    },


});             
});
