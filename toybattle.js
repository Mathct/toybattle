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
    "ebg/counter"
],
function (dojo, declare) {
    return declare("bgagame.toybattle", ebg.core.gamegui, {
        constructor: function(){
            console.log('toybattle constructor');
              
            // this.myGlobalValue = 0;

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

    this.bases = gamedatas.bases;
    this.zones = gamedatas.zones;
    this.board_name = gamedatas.board_name;
    this.board_id = gamedatas.board_id;

    this.boards = ["castle", "pool", "clouds", "jungle", "cemetery", "carribean","station", "battlefield"];
    this.medals_to_win = [7, 6, 8, 7, 7, 5, 7, 8];

    this.BLUE = "4f66a2";
    this.RED = "d1553e";

    this.spectator_id =  gamedatas.spectator_id;
    this.other_player_id =  gamedatas.other_player_id;

    this.board_troops = gamedatas.board_troops;

    this.setupPlayersBoard();
    this.setupBoard();


    // Setup game notifications to handle (see "setupNotifications" method below)
    this.setupNotifications();


    // RAJOUTER LES CONNECTS GAMEDATAS
    //dojo.query(".carre").connect('onclick', this, 'onSelect' )






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

    dojo.query(".selectable").removeClass("selectable");
    
    switch( stateName )
    {
    
    case 'playerTurn':
        this.args = args.args;
        for( var sid in this.args.selectable)
        {
            if(this.isCurrentPlayerActive())
            {
                dojo.query("#"+this.args.selectable[sid]).addClass("selectable");
            }
        }

        if( this.isCurrentPlayerActive() )
        {
            if(args.args.titleyou != null)
            {
                $('pagemaintitletext').innerHTML = 	this.format_string_recursive(_(args.args.titleyou).replace('${you}', this.divYou()).replace('#nb#',args.args.nb).replace('#nb2#',args.args.nb2).replace('#icon#',args.args.icon).replace('#icon2#',args.args.icon2), args.args);   
            }
        } 
        else
        {
            if(args.args.title != null)
            {
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
    
    switch( stateName )
    {
    
      
   
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
              
    if( this.isCurrentPlayerActive() )
        {            
            switch( stateName )
            {

                case "playerTurn":
                    for( var nb in args.buttons )
                    { 
                            
                        if(args.buttons[nb] == "cancel")
                        {
                            this.addActionButton( 'cancel', _("Cancel") ,'onOpButton', null, null, 'red' );
                        }
                        if(args.buttons[nb] == "pass")
                        {
                            this.addActionButton( 'pass', _("Pass") ,'onOpButton', null, null, 'red' );
                        }
                        if(args.buttons[nb] == "draw_2")
                        {
                            this.addActionButton( 'draw_2', _("Draw 2 Troops") ,'onOpButton', null, null, 'blue' );
                        }
                        if(args.buttons[nb] == "draw_1")
                        {
                            this.addActionButton( 'draw_1', _("Draw 1 Troop") ,'onOpButton', null, null, 'blue' );
                        }
                        if(args.buttons[nb] == "place_troop")
                        {
                            this.addActionButton( 'place_troop', _("Place 1 Troop") ,'onOpButton', null, null, 'blue' );
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
    
    var color = this.players[this.player_id].color;
    var color_bg = "";
    var you = "<span style=\"font-weight:bold;color:#" + color + ";" + color_bg + "\">" + _("You") + "</span>";
    return you;
},

divActPlayer : function() {        	
    var color = this.players[this.getActivePlayerId()].color;
    var name = this.players[this.getActivePlayerId()].name;
    var color_bg = "";
    var you = "<span style=\"font-weight:bold;color:#" + color + ";" + color_bg + "\">" + name + "</span>";
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

    var src = dojo.position(mobile);
    if (place_position)
        mobile.style.position = place_position;
    dojo.place(mobile, new_parent, relation);
    mobile.offsetTop;//force re-flow
    var tgt = dojo.position(mobile);
    var box = dojo.marginBox(mobile);
    var cbox = dojo.contentBox(mobile);
    var left = box.l + src.x - tgt.x;
    var top = box.t + src.y - tgt.y;

    mobile.style.position = "absolute";
    mobile.style.left = left + "px";
    mobile.style.top = top + "px";
    box.l += box.w - cbox.w;
    box.t += box.h - cbox.h;
    mobile.offsetTop;//force re-flow
    return box;
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

setupBoard: function()
{
    /*  creates global container with yourLine, playMat and myLine containers  */
    const globalContainer = document.getElementById('global_id');

    /*  yourLineContainer definition 
        contains yourDeckContainer and yourRackContainer
    */
    const yourLineContainer = document.createElement('div');
    yourLineContainer.id = `your_line`;
    yourLineContainer.classList.add('line');
    yourLineContainer.style.transform = 'rotate(180deg)';
    globalContainer.appendChild(yourLineContainer);

    /*  yourDeckContainer definition 
        contains Deck and number of Troops TODO
    */
    const yourDeckContainer = document.createElement('div');
    yourDeckContainer.id = `your_deck`;
    yourDeckContainer.classList.add('troop');
    const your_deck_color = this.isCurrentPlayerRed() ? 0 : 1;
    yourDeckContainer.style.backgroundPosition = `0% -${your_deck_color}00%`;
    yourLineContainer.appendChild(yourDeckContainer);


    /*  youRackkContainer definition 
        contains Rack and all Troops in Hand
    */
    const yourRackContainer = document.createElement('div');
    yourRackContainer.id = `your_rack`;
    yourRackContainer.classList.add('rack');
    yourRackContainer.classList.add(this.isCurrentPlayerRed() ? 'linear_blue' : 'linear_red_top');
    yourLineContainer.appendChild(yourRackContainer);

    Object.values(this.gamedatas.your_hand).forEach(troop => {
        const troopElement = document.createElement('div');
        troopElement.id = `troop_${troop.id}`;
        troopElement.classList.add('troop');

        const troop_type =  troop.type < 10 ? 0 : troop.type % 10;
        const troop_color = troop.type < 10 ? troop.type - 1 : Math.floor(troop.type / 10)-1;
        troopElement.style.backgroundPosition = `-${troop_type}00% -${troop_color}00%`;
        yourRackContainer.appendChild(troopElement);
    });

   
    /*  PlaymatContainer definition 
        contains blueDiscard, Board and redDiscard
    */
    const playmatContainer = document.createElement('div');
    playmatContainer.id = `playmat_id`;
    playmatContainer.classList.add('playmat');
    if( this.isCurrentPlayerRed() == true) {
        playmatContainer.classList.add('board-inverted');
    }
    globalContainer.appendChild(playmatContainer);

    /*  blueDiscardContainer definition 
        contains possible Troops in opacity 50 and all discarded ones TODO
    */
    const blueDiscardContainer = document.createElement('div');
    blueDiscardContainer.id = `discard_blue`;
    blueDiscardContainer.classList.add('discard', 'linear_blue');
    
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
    Object.values(this.board_troops).forEach(troop => {
        const troopElement = document.createElement('div');
        troopElement.id = `troop_${troop.id}`;
        troopElement.classList.add('troop');
        // defines troop type and color
        const troop_type =  troop.type < 10 ? 0 : troop.type % 10;
        const troop_color = troop.type < 10 ? troop.type - 1 : Math.floor(troop.type / 10)-1;
        troopElement.style.backgroundPosition = `-${troop_type}00% -${troop_color}00%`;
        boardContainer.appendChild(troopElement);
        
        // defines position on board
        const baseData = TB_bases[troop.location_arg];
        troopElement.style.position = 'absolute';
        troopElement.style.top = troop_color == 0 ? `${baseData.top}%` : `${baseData.top+2.5}%`;
        troopElement.style.left = `${baseData.left}%`;
        troopElement.style.zIndex = 10 * troop.ordre;
        if( troop_color == 1 ) {
            troopElement.style.transform = 'rotate(180deg)';
        }
        
    });



/* FORMER CODE FOR CHECKING ALL BASES
for (const baseId of Object.keys(TB_bases)) {
        const baseElement = document.createElement('div');
        baseElement.id = `base_${this.board_name}_${baseId}`;
        baseElement.classList.add('base');
        const baseData = TB_bases[baseId];
        baseElement.style.top = `${baseData.top}%`;
        baseElement.style.left = `${baseData.left}%`;
        boardContainer.appendChild(baseElement);

        const baseElementRed = document.createElement('div');
        baseElementRed.id = `base_red_${this.board_name}_${baseId}`;
        baseElementRed.classList.add('base_red');
        baseElementRed.style.top = `${baseData.top+2.5}%`;
        baseElementRed.style.left = `${baseData.left}%`;
        baseElementRed.style.transform = 'rotate(180deg)';

        boardContainer.appendChild(baseElementRed);

    }*/

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

    /* myLineContainer */
    const myLineContainer = document.createElement('div');
    myLineContainer.id = `my_line`;
    myLineContainer.classList.add('line');
    globalContainer.appendChild(myLineContainer);

    /* myDeckContainer */
    const myDeckContainer = document.createElement('div');
    myDeckContainer.id = `my_deck`;
    myDeckContainer.classList.add('troop');
    const my_deck_color = this.isCurrentPlayerRed() ? 1 : 0;
    myDeckContainer.style.backgroundPosition = `0% -${my_deck_color}00%`;
    myLineContainer.appendChild(myDeckContainer);

    /* myRackContainer */
    const myRackContainer = document.createElement('div');
    myRackContainer.id = `my_rack`;
    myRackContainer.classList.add('rack');
    myRackContainer.classList.add(this.isCurrentPlayerRed() ? 'linear_red_top' : 'linear_blue');
    myLineContainer.appendChild(myRackContainer);
    Object.values(this.gamedatas.my_hand).forEach(troop => {
        const troopElement = document.createElement('div');
        troopElement.id = `troop_${troop.id}`;
        troopElement.classList.add('troop');

        const troop_type =  troop.type < 10 ? 0 : troop.type % 10;
        const troop_color = troop.type < 10 ? troop.type - 1 : Math.floor(troop.type / 10)-1;
        troopElement.style.backgroundPosition = `-${troop_type}00% -${troop_color}00%`;
        myRackContainer.appendChild(troopElement);
    });
        
    
    
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
        if( this.gamedatas.players[ this.player_id ].color == this.RED )
        {   return true;   }
    }
    return false;
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

        
onSelect: function(evt)
{        	 
    // Preventing default browser reaction
     dojo.stopEvent( evt );

    
     
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
    dojo.stopEvent( evt );
    
    this.bgaPerformAction('actButton', { arg1: evt.currentTarget.id });
    
    

},






/// GESTION DE LA PREF DE CONFIMATION VERS LE BACK /// 

onGameUserPreferenceChanged(pref_id, pref_value) {

    if(!this.isReadOnly())
    {

        if ((pref_id === 100)&&(!this.isSpectator))
        {  
           
            //this.bgaPerformAction('actConfirmPref', { arg1: this.getCurrentPlayerId(), arg2: pref_value}, { lock: false, checkAction: false });
                        
        }
    }
},

// GESTION DE LA PREF DE CONFRMATION: GESTION DU REPLAY QUI NE FONCTIONNE PAS AVEC CETTE PREF

isReadOnly: function () { 
    return this.isSpectator || typeof g_replayFrom != 'undefined' || g_archive_mode; 
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
    
    // TODO: here, associate your game notifications with local methods
    
    // Example 1: standard notification handling
    // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
    
    // Example 2: standard notification handling + tell the user interface to wait
    //            during 3 seconds after calling the method in order to let the players
    //            see what is happening in the game.
    // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
    // this.notifqueue.setSynchronous( 'cardPlayed', 3000 );
    // 
},  

// TODO: from this point and below, you can write your game notifications handling methods

/*
Example:

notif_cardPlayed: function( notif )
{
    console.log( 'notif_cardPlayed' );
    console.log( notif );
    
    // Note: notif.args contains the arguments specified during you "notifyAllPlayers" / "notifyPlayer" PHP call
    
    // TODO: play the card in the user interface.
},    

*/






});             
});
