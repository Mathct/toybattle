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

    this.BLUE = "4f66a2";
    this.RED = "d1553e";

    this.spectator_id =  gamedatas.spectator_id;
    this.other_player_id =  gamedatas.other_player_id;

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

            /*this.gamedatas.gamestate.descriptionmyturn = _(this.args.titleyou);
            this.gamedatas.gamestate.description = _(this.args.title);
            this.updatePageTitle();*/

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

setupBoard: function()
{
    const globalContainer = document.getElementById('global_id');

    const yourLineContainer = document.createElement('div');
    yourLineContainer.id = `your_line`;
    yourLineContainer.classList.add('line');
    yourLineContainer.style.transform = 'rotate(180deg)';
    globalContainer.appendChild(yourLineContainer);


    const yourDeckContainer = document.createElement('div');
    yourDeckContainer.id = `your_deck`;
    yourDeckContainer.classList.add('troop');
    const your_deck_color = this.isCurrentPlayerRed() ? 0 : 1;

    yourDeckContainer.style.backgroundPosition = `0% -${your_deck_color}00%`;
    yourLineContainer.appendChild(yourDeckContainer);

    const yourRackContainer = document.createElement('div');
    yourRackContainer.id = `your_rack`;
    yourRackContainer.classList.add('rack');
    yourRackContainer.classList.add(this.isCurrentPlayerRed() ? 'linear_blue' : 'linear_red_top');
    
    yourLineContainer.appendChild(yourRackContainer);

    Object.values(this.gamedatas.your_hand).forEach(troop => {
        const troopElement = document.createElement('div');
        troopElement.id = `troop_${troop.card_id}`;
        troopElement.classList.add('troop');

        const troop_type =  troop.card_type < 10 ? 0 : troop.card_type % 10;
        const troop_color = troop.card_type < 10 ? troop.card_type - 1 : Math.floor(troop.card_type / 10)-1;

        troopElement.style.backgroundPosition = `-${troop_type}00% -${troop_color}00%`;
        yourRackContainer.appendChild(troopElement);
    });

   



    const playmatContainer = document.createElement('div');
    playmatContainer.id = `playmat_id`;
    playmatContainer.classList.add('playmat');
    globalContainer.appendChild(playmatContainer);

    if( this.isCurrentPlayerRed() == true)
    {
        playmatContainer.classList.add('board-inverted');
    }


    const blueDiscardContainer = document.createElement('div');
    blueDiscardContainer.id = `discard_blue`;
    blueDiscardContainer.classList.add('discard', 'linear_blue');
    playmatContainer.appendChild(blueDiscardContainer);
    for( let i=1; i<9; i++ ) {
        const blueDiscardTroop = document.createElement('div');
        blueDiscardTroop.id = `discard_blue_${i}`;
        blueDiscardTroop.classList.add('troop','opa_50');
        blueDiscardTroop.style.backgroundPosition = `-${i}00% -000%`;
        blueDiscardContainer.appendChild(blueDiscardTroop);
    }


   
    const boardContainer = document.createElement('div');
    boardContainer.id = `board_${this.board_id}`;
    boardContainer.classList.add('board');
    const index = this.boards.indexOf(this.board_name);
    const background_x = (this.board_id - 1) % 4;  
    const background_y = Math.floor( (this.board_id - 1) / 4);
    boardContainer.style.backgroundPosition = `-${background_x}00% -${background_y}00%`;





        
    const TB_bases = this.bases[this.board_name];

    Object.values(this.gamedatas.board_troops).forEach(troop => {
        const troopElement = document.createElement('div');
        troopElement.id = `troop_${troop.card_id}`;
        troopElement.classList.add('troop');
        const troop_type =  troop.card_type < 10 ? 0 : troop.card_type % 10;
        const troop_color = troop.card_type < 10 ? troop.card_type - 1 : Math.floor(troop.card_type / 10)-1;
        troopElement.style.backgroundPosition = `-${troop_type}00% -${troop_color}00%`;
        boardContainer.appendChild(troopElement);
        const baseData = TB_bases[troop.card_location_arg];
        console.log( 'baseData', baseData);
        troopElement.style.position = 'absolute';
        troopElement.style.top = troop_color == 0 ? `${baseData.top}%` : `${baseData.top+2.5}%`;
        troopElement.style.left = `${baseData.left}%`;
        console.log( 'baseData style top', troopElement.style.top);
        console.log( 'baseData style left', troopElement.style.left);
        if( troop_color == 1 ) {
            troopElement.style.transform = 'rotate(180deg)';
        }
        
    });

/*    for (const baseId of Object.keys(TB_bases)) {
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

    const myLineContainer = document.createElement('div');
    myLineContainer.id = `my_line`;
    myLineContainer.classList.add('line');
    globalContainer.appendChild(myLineContainer);

    const myDeckContainer = document.createElement('div');
    myDeckContainer.id = `my_deck`;
    myDeckContainer.classList.add('troop');
    const my_deck_color = this.isCurrentPlayerRed() ? 1 : 0;

    myDeckContainer.style.backgroundPosition = `0% -${my_deck_color}00%`;

    myLineContainer.appendChild(myDeckContainer);

    const myRackContainer = document.createElement('div');
    myRackContainer.id = `my_rack`;
    myRackContainer.classList.add('rack');
    myRackContainer.classList.add(this.isCurrentPlayerRed() ? 'linear_red_top' : 'linear_blue');
    myLineContainer.appendChild(myRackContainer);


    Object.values(this.gamedatas.my_hand).forEach(troop => {
        const troopElement = document.createElement('div');
        troopElement.id = `troop_${troop.card_id}`;
        troopElement.classList.add('troop');

        const troop_type =  troop.card_type < 10 ? 0 : troop.card_type % 10;
        const troop_color = troop.card_type < 10 ? troop.card_type - 1 : Math.floor(troop.card_type / 10)-1;

        troopElement.style.backgroundPosition = `-${troop_type}00% -${troop_color}00%`;
        myRackContainer.appendChild(troopElement);
    });
        
    
    
 /*   for( let i=1; i<9; i++) {
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
