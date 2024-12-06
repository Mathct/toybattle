<?php

/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * toybattle implementation : © <Mathieu Chatrain> <mathieu.chatrain@gmail.com> && <Yannick Briol> <camertwo@hotmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * Game.php
 *
 * This is the main file for your game logic.
 *
 * In this PHP file, you are going to defines the rules of the game.
 */

declare(strict_types=1);

namespace Bga\Games\toybattle;

require_once(APP_GAMEMODULE_PATH . "module/table/table.game.php");

include('Pending.php'); // ATTENTION

class Game extends \Table
{
    //private static array $CARD_TYPES; // ATTENTION
    public $_bases;
    public $_zones;
    public $_powers;
    public $_medals_to_win;
    public $troop;



    public static $instance = null;  // ATTENTION

    /**
     * Your global variables labels:
     *
     * Here, you can assign labels to global variables you are using for this game. You can use any number of global
     * variables with IDs between 10 and 99. If your game has options (variants), you also have to associate here a
     * label to the corresponding ID in `gameoptions.inc.php`.
     *
     * NOTE: afterward, you can get/set the global variables with `getGameStateValue`, `setGameStateInitialValue` or
     * `setGameStateValue` functions.
     */
    public function __construct()
    {
        parent::__construct();

        require 'material.inc.php';

        $this->initGameStateLabels([

            "game_mode" => 100,
            "game_board" => 101,
            "board" => 10,



        ]);


        self::$instance = $this; // ATTENTION

        $this->troop = self::getNew("module.common.deck");
        $this->troop->init("troop");
    }


    /**
     * Returns the game name.
     *
     * IMPORTANT: Please do not modify.
     */
    protected function getGameName()
    {
        return "toybattle";
    }

    /////////////////////////////////////////////////////////////////////////////////  
    //       _____                        _____       _ _   _       _ _          _   _             
    //      / ____|                      |_   _|     (_) | (_)     | (_)        | | (_)            
    //     | |  __  __ _ _ __ ___   ___    | |  _ __  _| |_ _  __ _| |_ ______ _| |_ _  ___  _ __  
    //     | | |_ |/ _` | '_ ` _ \ / _ \   | | | '_ \| | __| |/ _` | | |_  / _` | __| |/ _ \| '_ \ 
    //     | |__| | (_| | | | | | |  __/  _| |_| | | | | |_| | (_| | | |/ / (_| | |_| | (_) | | | |
    //      \_____|\__,_|_| |_| |_|\___| |_____|_| |_|_|\__|_|\__,_|_|_/___\__,_|\__|_|\___/|_| |_|
    //                                                                                               
    /////////////////////////////////////////////////////////////////////////////////    


    protected function setupNewGame($players, $options = [])
    {
        // Set the colors of the players with HTML color code. The default below is red/green/blue/orange/brown. The
        // number of colors defined here must correspond to the maximum number of players allowed for the gams.
        $gameinfos = $this->getGameinfos();
        $default_colors = $gameinfos['player_colors'];

        foreach ($players as $player_id => $player) {
            // Now you can access both $player_id and $player array
            $query_values[] = vsprintf("('%s', '%s', '%s', '%s', '%s')", [
                $player_id,
                array_shift($default_colors),
                $player["player_canal"],
                addslashes($player["player_name"]),
                addslashes($player["player_avatar"]),
            ]);
        }


        static::DbQuery(
            sprintf(
                "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES %s",
                implode(",", $query_values)
            )
        );

        //$this->reattributeColorsBasedOnPreferences($players, $gameinfos["player_colors"]);
        $this->reloadPlayersBasicInfos();

        // Init global values with their initial values.
        // $this->setGameStateInitialValue("my_first_global_variable", 0);
        // $this->initStat("table", "table_teststat1", 0);
        // $this->initStat("player", "player_teststat1", 0);

        foreach ($players as $player_id => $player) {

            $color = self::getUniqueValueFromDB("SELECT player_color FROM player WHERE player_id={$player_id}");

            // COLOR A CHANGER SI MODIFICATION DES COULEURS DE BASE DECLAREES DANS GAMEINFOS

            // 4f66a2 = bleu d1553e = rouge

            if ($color == 'd1553e') {
                $red = array();
                for ($i = 1; $i <= 8; $i++) {

                    $red[] = array('type' => '2' . $i, 'type_arg' => $player_id, 'nbr' => 3);
                }

                $this->troop->createCards($red, 'deckred');
            }

            if ($color == '4f66a2') {
                $blue = array();
                for ($i = 1; $i <= 8; $i++) {

                    $blue[] = array('type' => '1' . $i, 'type_arg' => $player_id, 'nbr' => 3);
                }

                $this->troop->createCards($blue, 'deckblue');
            }
        }

        $this->troop->shuffle('deckred');
        $this->troop->shuffle('deckblue');


        // INIT: 4 TROUPES POUR CHAQUE JOUEUR QUI NE SERONT PAS JOUEES

        $this->troop->pickCardsForLocation(4, 'deckred', 'noplay');
        $this->troop->pickCardsForLocation(4, 'deckblue', 'noplay');

        // INIT: 3 TROUPES POUR LE PREMIER JOUEUR ET 4 TROUPES POUR LE SECOND JOUEUR

        foreach ($players as $player_id => $player) {

            $numero = self::getUniqueValueFromDB("SELECT player_no FROM player WHERE player_id={$player_id}");
            $color = self::getUniqueValueFromDB("SELECT player_color FROM player WHERE player_id={$player_id}");

            // COLOR A CHANGER SI MODIFICATION DES COULEURS DE BASE DECLAREES DANS GAMEINFOS

            if ($numero == 1) {
                if ($color == 'd1553e') {
                    $this->troop->pickCardsForLocation(3, 'deckred', 'hand');
                }

                if ($color == '4f66a2') {
                    $this->troop->pickCardsForLocation(3, 'deckblue', 'hand');
                }
            }

            if ($numero == 2) {
                if ($color == 'd1553e') {
                    $this->troop->pickCardsForLocation(4, 'deckred', 'hand');
                }

                if ($color == '4f66a2') {
                    $this->troop->pickCardsForLocation(4, 'deckblue', 'hand');
                }
            }
        }


        // CHOIX DU BOARD (GSV 101)

        // BOARD DE 1 A 8
        if (($this->gamestate->table_globals[101] >= 1) && ($this->gamestate->table_globals[101] <= 8)) {
            $this->setGameStateValue('board', $this->gamestate->table_globals[101]);
        }

        // BOARD RANDOM
        if ($this->gamestate->table_globals[101] == 9) {
            $random = bga_rand(1, 8);
            $this->setGameStateValue('board', $random);
        }

        // BOARD DU MOIS
        if ($this->gamestate->table_globals[101] == 10) {
            // A FAIRE ... POUR LE MOMENT C'EST UN RANDOM
            $random = bga_rand(1, 8);
            $this->setGameStateValue('board', $random);
        }





        /************ Init Pending *****/


        foreach ($players as $player_id => $player) {
            $this->addPendingFirst($player_id, "NormalTurn");
        }
    }

    /////////////////////////////////////////////////////////////////////////////////  
    //               _            _ _ _____        _            
    //              | |     /\   | | |  __ \      | |           
    //     __ _  ___| |_   /  \  | | | |  | | __ _| |_ __ _ ___ 
    //    / _` |/ _ \ __| / /\ \ | | | |  | |/ _` | __/ _` / __|
    //   | (_| |  __/ |_ / ____ \| | | |__| | (_| | || (_| \__ \
    //    \__, |\___|\__/_/    \_\_|_|_____/ \__,_|\__\__,_|___/
    //     __/ |                                                
    //    |___/                                                 
    /////////////////////////////////////////////////////////////////////////////////  

    protected function getAllDatas()
    {
        $result = [];

        // WARNING: We must only return information visible by the current player.

        $current_player_id = (int) $this->getCurrentPlayerId();
        $opponent_id = (int) $this->getOpponentId($current_player_id);
        $spectator_id = $this->getSpectatorId();
        $no_spectator_id = (int) $this->getOpponentId($spectator_id);

        $result['opponent_id'] = $opponent_id;
        $result['spectator_id'] = $spectator_id;
        $result['no_spectator_id'] = $no_spectator_id;

        // Get information about players.
        // NOTE: you can retrieve some extra field you added for "player" table in `dbmodel.sql` if you need it.
        $sql = "SELECT player_id id, player_score score, player_color color, player_no no, player_name name FROM player ORDER BY player_no";
        $result['players'] = self::getCollectionFromDb($sql);

        if (!$this->isSpectator()) {
            $result["my_hand"] = self::getObjectListFromDB("SELECT card_id, card_type FROM troop WHERE card_location = 'hand' AND card_type_arg = '{$current_player_id}' ORDER BY card_type");
            $result["your_hand"] = self::getObjectListFromDB("SELECT card_id, FLOOR(card_type / 10) card_type FROM troop WHERE card_location = 'hand' AND card_type_arg = '{$opponent_id}'");
            $result["my_discard"] = self::getObjectListFromDB("SELECT card_id, card_type FROM troop WHERE card_location = 'discard' AND card_type_arg = '{$current_player_id}'");
            $result["your_discard"] = self::getObjectListFromDB("SELECT card_id, card_type FROM troop WHERE card_location = 'discard' AND card_type_arg = '{$opponent_id}'");
        } else {
            $result["my_hand"] = self::getObjectListFromDB("SELECT card_id, FLOOR(card_type / 10) card_type FROM troop WHERE card_location = 'hand' AND card_type_arg = '{$spectator_id}'");
            $result["your_hand"] = self::getObjectListFromDB("SELECT card_id, FLOOR(card_type / 10) card_type FROM troop WHERE card_location = 'hand' AND card_type_arg = '{$no_spectator_id}'");
            $result["my_discard"] = self::getObjectListFromDB("SELECT card_id, card_type FROM troop WHERE card_location = 'discard' AND card_type_arg = '{$spectator_id}'");
            $result["your_discard"] = self::getObjectListFromDB("SELECT card_id, card_type FROM troop WHERE card_location = 'discard' AND card_type_arg = '{$no_spectator_id}'");
        }
        $result["board_troops"] = self::getObjectListFromDB("SELECT card_id, card_type, card_type_arg, card_location, card_location_arg, ordre FROM troop WHERE card_location = 'board'");

        $result["bases"] = $this->_bases;
        $result["zones"] = $this->_zones;

        $board_name = ["castle", "pool", "clouds", "jungle", "cemetery", "carribean", "station", "battlefield"];
        $result["board_name"] = $board_name[$this->getGameStateValue('board') - 1];

        // TODO: Gather all information about current game situation (visible by player $current_player_id).

        return $result;
    }


    /////////////////////////////////////////////////////////////////////////////////  
    //     _____                      _____                                   _             
    //    / ____|                    |  __ \                                 (_)            
    //   | |  __  __ _ _ __ ___   ___| |__) | __ ___   __ _ _ __ ___  ___ ___ _  ___  _ __  
    //   | | |_ |/ _` | '_ ` _ \ / _ \  ___/ '__/ _ \ / _` | '__/ _ \/ __/ __| |/ _ \| '_ \ 
    //   | |__| | (_| | | | | | |  __/ |   | | | (_) | (_| | | |  __/\__ \__ \ | (_) | | | |
    //    \_____|\__,_|_| |_| |_|\___|_|   |_|  \___/ \__, |_|  \___||___/___/_|\___/|_| |_|
    //                                                 __/ |                                
    //                                                |___/                                 
    /////////////////////////////////////////////////////////////////////////////////  

    public function getGameProgression()
    {


        return 0;
    }


    /////////////////////////////////////////////////////////////////////////////////  
    //     _    _ _   _ _ _ _            __                  _   _                 
    //    | |  | | | (_) (_) |          / _|                | | (_)                
    //    | |  | | |_ _| |_| |_ _   _  | |_ _   _ _ __   ___| |_ _  ___  _ __  ___ 
    //    | |  | | __| | | | __| | | | |  _| | | | '_ \ / __| __| |/ _ \| '_ \/ __|
    //    | |__| | |_| | | | |_| |_| | | | | |_| | | | | (__| |_| | (_) | | | \__ \
    //     \____/ \__|_|_|_|\__|\__, | |_|  \__,_|_| |_|\___|\__|_|\___/|_| |_|___/
    //                           __/ |                                             
    //                          |___/                                              
    /////////////////////////////////////////////////////////////////////////////////  

    function addPending($player_id, $function, $arg = NULL, $arg2 = NULL, $arg3 = NULL, $arg4 = NULL)
    {
        $sql = "INSERT INTO pending (player_id, function, arg, arg2, arg3, arg4) VALUES (" . $player_id . ", '" . $function . "', '" . $arg . "', '" . $arg2 . "', '" . $arg3 . "', '" . $arg4 . "')";
        self::DbQuery($sql);
    }

    function addPendingFirst($player_id, $function, $arg = NULL, $arg2 = NULL, $arg3 = NULL, $arg4 = NULL)
    {
        $minid = self::getUniqueValueFromDB("select min(id) from pending") - 1;
        $sql = "INSERT INTO pending (id, player_id, function, arg, arg2) VALUES (" . $minid . "," . $player_id . ", '" . $function . "', '" . $arg . "', '" . $arg2 . "')";
        self::DbQuery($sql);
    }

    function checkArgs($arg1)
    {
        $ret = self::argPlayerTurn();

        if (!in_array($arg1, $ret['selectable']) && !in_array($arg1, $ret['buttons'])) {
            throw new \feException("Not a valid selection");
        }
    }


    /**
     * Returns the opponent's player id
     */
    function getOpponentId(int $player_id): int
    {
        $player_ids = array_keys($this->loadPlayersBasicInfos());
        if (in_array($player_id, $player_ids)) {
            return (int) array_values(array_diff($player_ids, [$player_id]))[0];
        } else {
            $sql = "SELECT player_id FROM player WHERE player_no = (SELECT MAX(player_no) FROM player)";
            return (int) $this->getUniqueValueFromDB($sql);
        }
    }

    /**
     * returns player_id having player_no 1, used for any spectator
     */
    function getSpectatorId(): int
    {
        $sql = "SELECT player_id FROM player WHERE player_no = (SELECT MIN(player_no) FROM player)";
        return (int) $this->getUniqueValueFromDB($sql);
    }

    ///////////////////////////////////////////////////////////////////////////////// 
    //     _____  _                                    _   _                 
    //    |  __ \| |                                  | | (_)                
    //    | |__) | | __ _ _   _  ___ _ __    __ _  ___| |_ _  ___  _ __  ___ 
    //    |  ___/| |/ _` | | | |/ _ \ '__|  / _` |/ __| __| |/ _ \| '_ \/ __|
    //    | |    | | (_| | |_| |  __/ |    | (_| | (__| |_| | (_) | | | \__ \
    //    |_|    |_|\__,_|\__, |\___|_|     \__,_|\___|\__|_|\___/|_| |_|___/
    //                     __/ |                                             
    //                    |___/                                              
    /////////////////////////////////////////////////////////////////////////////////


    public function actSelect(string $arg1)
    {

        self::checkArgs($arg1);

        $pending =  self::getObjectFromDB("SELECT* FROM pending order by id desc limit 1");
        $this->callPending($pending, true, $arg1);
        self::DbQuery("delete from pending where id=" . $pending['id']);
        $this->giveExtraTime(self::getActivePlayerId());
        $this->gamestate->nextState('next');
    }

    public function actButton(string $arg1)
    {

        self::checkArgs($arg1);

        $pending =  self::getObjectFromDB("SELECT* FROM pending order by id desc limit 1");
        $this->callPending($pending, true, $arg1);
        self::DbQuery("delete from pending where id=" . $pending['id']);
        $this->giveExtraTime(self::getActivePlayerId());
        $this->gamestate->nextState('next');
    }

    ///////////////////////////////////////////////////////////////////////////////// 
    //     _____                             _        _                                                    _       
    //    / ____|                           | |      | |                                                  | |      
    //    | |  __  __ _ _ __ ___   ___   ___| |_ __ _| |_ ___    __ _ _ __ __ _ _   _ _ __ ___   ___ _ __ | |_ ___ 
    //    | | |_ |/ _` | '_ ` _ \ / _ \ / __| __/ _` | __/ _ \  / _` | '__/ _` | | | | '_ ` _ \ / _ \ '_ \| __/ __|
    //    | |__| | (_| | | | | | |  __/ \__ \ || (_| | ||  __/ | (_| | | | (_| | |_| | | | | | |  __/ | | | |_\__ \
    //     \_____|\__,_|_| |_| |_|\___| |___/\__\__,_|\__\___|  \__,_|_|  \__, |\__,_|_| |_| |_|\___|_| |_|\__|___/
    //                                                                    __/ |                                   
    //                                                                   |___/                                    
    ///////////////////////////////////////////////////////////////////////////////// 


    public function argPlayerTurn()
    {
        $pending =  self::getObjectFromDB("SELECT* FROM pending order by id desc limit 1");
        $arg = $this->callPending($pending, false);

        return $arg;
    }


    ///////////////////////////////////////////////////////////////////////////////// 
    //      _____                            _        _                    _   _                 
    //     / ____|                          | |      | |                  | | (_)                
    //    | |  __  __ _ _ __ ___   ___   ___| |_ __ _| |_ ___    __ _  ___| |_ _  ___  _ __  ___ 
    //    | | |_ |/ _` | '_ ` _ \ / _ \ / __| __/ _` | __/ _ \  / _` |/ __| __| |/ _ \| '_ \/ __|
    //    | |__| | (_| | | | | | |  __/ \__ \ || (_| | ||  __/ | (_| | (__| |_| | (_) | | | \__ \
    //     \_____|\__,_|_| |_| |_|\___| |___/\__\__,_|\__\___|  \__,_|\___|\__|_|\___/|_| |_|___/
    //                                                                                       
    /////////////////////////////////////////////////////////////////////////////////     


    public function callPending($pending, $execute, $arg1 = null, $arg2 = null)
    {

        $obj = $this;
        if ($pending['player_id'] != null) {
            $obj = new Pending($pending['player_id']);
        }

        $fname = "";
        if (!$execute) {
            $fname .= "arg";
        }
        $fname .= $pending['function'];

        $ret = null;
        if (method_exists($obj, $fname)) {
            $ret = $obj->$fname($pending['arg'], $pending['arg2'], $arg1, $arg2);
        }

        return $ret;
    }


    public function stPending()
    {

        $pending =  self::getObjectFromDB("SELECT * FROM pending order by id desc limit 1");
        if ($pending == null) {

            $this->gamestate->nextState('end');
        } else {
            $args = $this->callPending($pending, false);

            if ($pending['player_id'] != self::getActivePlayerId()) {


                //change active player      
                $this->gamestate->changeActivePlayer($pending['player_id']);
                $this->gamestate->nextState('same');
            } else if ($args == null || (count($args['selectable']) == 0 && count($args['buttons']) == 0)) {
                //no args required, execute
                $this->callPending($pending, true);
                self::DbQuery("delete from pending where id=" . $pending['id']);
                $this->gamestate->nextState('same');
            } else {
                $this->gamestate->nextState('player');
            }
        }
    }

    ///////////////////////////////////////////////////////////////////////////////// 
    //     _____  ____                                    _      
    //    |  __ \|  _ \                                  | |     
    //    | |  | | |_) |  _   _ _ __   __ _ _ __ __ _  __| | ___ 
    //    | |  | |  _ <  | | | | '_ \ / _` | '__/ _` |/ _` |/ _ \
    //    | |__| | |_) | | |_| | |_) | (_| | | | (_| | (_| |  __/
    //    |_____/|____/   \__,_| .__/ \__, |_|  \__,_|\__,_|\___|
    //                         | |     __/ |                     
    //                         |_|    |___/                      
    /////////////////////////////////////////////////////////////////////////////////  


    public function upgradeTableDb($from_version) {}




    /////////////////////////////////////////////////////////////////////////////////
    //    ______               _     _      
    //   |___  /              | |   (_)     
    //      / / ___  _ __ ___ | |__  _  ___ 
    //     / / / _ \| '_ ` _ \| '_ \| |/ _ \
    //    / /_| (_) | | | | | | |_) | |  __/
    //   /_____\___/|_| |_| |_|_.__/|_|\___|
    //                                   
    /////////////////////////////////////////////////////////////////////////////////     

    protected function zombieTurn(array $state, int $active_player): void
    {
        $state_name = $state["name"];

        if ($state["type"] === "activeplayer") {
            switch ($state_name) {
                default: {
                        $player_id = $this->getActivePlayerId();
                        self::DbQuery("delete from pending where player_id = {$player_id}");
                        $this->gamestate->nextState("zombiePass");
                        break;
                    }
            }

            return;
        }

        // Make sure player is in a non-blocking status for role turn.
        if ($state["type"] === "multipleactiveplayer") {
            $this->gamestate->setPlayerNonMultiactive($active_player, '');
            return;
        }

        throw new \feException("Zombie mode not supported at this game state: \"{$state_name}\".");
    }
}
