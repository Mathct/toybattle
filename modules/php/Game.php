<?php

/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * toybattle implementation : © <Mathieu Chatrain> <mathieu.chatrain@gmail.com> && <Yannick Priol> <camertwo@hotmail.com>
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

use Bga\GameFramework\Components\Deck;
use Bga\GameFramework\Table;
use Bga\GameFramework\UserException;
use Bga\GameFramework\VisibleSystemException;

include('Pending.php'); // ATTENTION

class Game extends Table
{
    //private static array $CARD_TYPES; // ATTENTION
    public $_bases;
    public $_regions;
    public $_medals;
    public $_goodies;
    public $_troop_types;
    public $_board_types;
    public $_powers;
    public $_medals_to_win;
    public Deck $troop;
    public $_board_names;



    public static ?Game $instance = null;  // ATTENTION

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
            "endgame" => 11,



        ]);


        self::$instance = $this; // ATTENTION

        $this->troop = $this->bga->deckFactory->createDeck("troop");
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

        // foreach ($players as $player_id => $player) {
        //     // Now you can access both $player_id and $player array
        //     $query_values[] = vsprintf("('%s', '%s', '%s', '%s', '%s')", [
        //         $player_id,
        //         array_shift($default_colors),
        //         $player["player_canal"],
        //         addslashes($player["player_name"]),
        //         addslashes($player["player_avatar"]),
        //     ]);
        // }

        foreach ($players as $player_id => $player) {

            /* FIX 12/2025 spectator mode bug when first player is red
            $index = random_int(0, count($default_colors) - 1); // choix sûr
            $color = $default_colors[$index];
            array_splice($default_colors, $index, 1); // enlève la couleur utilisée

            // Now you can access both $player_id and $player array
            $query_values[] = vsprintf("('%s', '%s', '%s', '%s', '%s')", [
                $player_id,
                $color,
                $player["player_canal"],
                addslashes($player["player_name"]),
                addslashes($player["player_avatar"]),
            ]);*/

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
                "INSERT INTO `player` (`player_id`, `player_color`, `player_canal`, `player_name`, `player_avatar`) VALUES %s",
                implode(",", $query_values)
            )
        );

        //$this->reattributeColorsBasedOnPreferences($players, $gameinfos["player_colors"]);
        $this->reloadPlayersBasicInfos();

        self::initStat('table', 'turns_number', 0);
        self::initStat('table', 'win_by_terrain', 0);
        self::initStat('table', 'win_by_hq', 0);
        self::initStat('table', 'no_board', 0);
        self::initStat('table', 'color_start', 0);
        self::initStat('table', 'color_win', 0);
        self::initStat('table', 'type_victory', 0);

        self::initStat('player', 'turns_number', 0);
        self::initStat('player', 'troops_drawn', 0);
        self::initStat('player', 'troops_played', 0);
        self::initStat('player', 'medals_won', 0);
        self::initStat('player', 'regions_controlled', 0);
        self::initStat('player', 'skully_activated', 0);
        self::initStat('player', 'capn_activated', 0);
        self::initStat('player', 'jumbo_activated', 0);
        self::initStat('player', 'xb42_activated', 0);
        self::initStat('player', 'star_activated', 0);
        self::initStat('player', 'base_activated', 0);


        $this->setGameStateInitialValue("endgame", 0);

        foreach ($players as $player_id => $player) {

            $color = self::getUniqueValueFromDB("SELECT `player_color` FROM `player` WHERE `player_id`={$player_id}");

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

            $numero = self::getUniqueValueFromDB("SELECT `player_no` FROM `player` WHERE `player_id`={$player_id}");
            $color = self::getUniqueValueFromDB("SELECT `player_color` FROM `player` WHERE `player_id`={$player_id}");

            // COLOR A CHANGER SI MODIFICATION DES COULEURS DE BASE DECLAREES DANS GAMEINFOS

            if ($numero == 1) {
                if ($color == 'd1553e') {
                    $this->troop->pickCardsForLocation(3, 'deckred', 'hand');
                    game::$instance->setStat(2, 'color_start');
                }

                if ($color == '4f66a2') {
                    $this->troop->pickCardsForLocation(3, 'deckblue', 'hand');
                    game::$instance->setStat(1, 'color_start');
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
        if (($this->bga->tableOptions->get(101) >= 1) && ($this->bga->tableOptions->get(101) <= 9)) {
            $this->setGameStateValue('board', $this->bga->tableOptions->get(101));
            game::$instance->setStat($this->bga->tableOptions->get(101), 'no_board');
        }

        // BOARD RANDOM
        if ($this->bga->tableOptions->get(101) == 9) {
            $random = bga_rand(1, 8);
            $this->setGameStateValue('board', $random);
            game::$instance->setStat($random, 'no_board');
        }

        // BOARD DU MOIS
        if ($this->bga->tableOptions->get(101) == 10) {
            $valeurs = [1, 2, 3, 4, 5, 6, 7, 8];
            $mois_depart = "Décembre 2024";


            $mois = [
                "Janvier",
                "Février",
                "Mars",
                "Avril",
                "Mai",
                "Juin",
                "Juillet",
                "Août",
                "Septembre",
                "Octobre",
                "Novembre",
                "Décembre"
            ];

            // Extraire le mois et l'année de $mois_depart
            list($mois_nom, $annee_depart) = explode(" ", $mois_depart);
            $index_depart = array_search($mois_nom, $mois); // Trouve l'indice du mois dans la liste

            // Récupérer le mois et l'année actuels
            $mois_actuel = intval(date('n')) - 1; // 'n' renvoie le mois (1 à 12), on convertit en 0-indexé
            $annee_actuelle = intval(date('Y'));

            // Calculer la différence totale en mois
            $diff_mois = ($annee_actuelle - intval($annee_depart)) * 12 + ($mois_actuel - $index_depart);

            // Obtenir l'indice circulaire dans le tableau
            $index_cible = ($diff_mois % count($valeurs) + count($valeurs)) % count($valeurs); // Toujours positif

            $board = $valeurs[$index_cible];

            if (($mois_actuel == 10 || $mois_actuel = 11) && $annee_actuelle == 2025) { // A REMPLACER PAR 11 EN DEC
                $board = 9;
            }
            if (($mois_actuel == 1 || $mois_actuel = 2) && $annee_actuelle == 2026) { // A REMPLACER PAR 11 EN DEC
                $board = 10;
            }


            $this->setGameStateValue('board', $board);
            game::$instance->setStat($board, 'no_board');
        }


        // INIT DE LA BdD ZONE

        $board_name = ["castle", "pool", "clouds", "jungle", "cemetery", "carribean", "station", "battlefield", "christmas", "croisette"];
        $board_selected = $board_name[$this->getGameStateValue('board') - 1];
        // christmas = castle
        $nb_zones = count($this->_regions[$board_selected]);

        for ($i = 1; $i <= $nb_zones; $i++) {
            self::DbQuery("INSERT INTO `zone` (`zone_star`) VALUES ({$this->_regions[$board_selected][$i]['medals']})");
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
        $sql = "SELECT `player_id` `id`, `player_score` score, `player_color` color, `player_no` no, `player_name` name, `player_star` star FROM `player` ORDER BY `player_no`";
        $result['players'] = self::getCollectionFromDb($sql);

        if (!$this->isSpectator()) {
            $result["my_hand"] = self::getObjectListFromDB("SELECT `card_id` `id` , `card_type` type, `card_blocked` blocked FROM `troop` WHERE `card_location` = 'hand' AND `card_type_arg` = '{$current_player_id}' ORDER BY `card_type`");
            $result["your_hand"] = self::getObjectListFromDB("SELECT FLOOR(`card_type` / 10) type, `card_blocked` blocked FROM `troop` WHERE `card_location` = 'hand' AND `card_type_arg` = '{$opponent_id}'");
            $result["my_discard"] = self::getObjectListFromDB("SELECT `card_id` `id`, `card_type` type FROM `troop` WHERE `card_location` = 'discard' AND `card_type_arg` = '{$current_player_id}' ORDER BY `card_type`");
            $result["your_discard"] = self::getObjectListFromDB("SELECT `card_id` `id` , `card_type` type FROM `troop` WHERE `card_location` = 'discard' AND `card_type_arg` = '{$opponent_id}' ORDER BY `card_type`");
        } else {
            $result["my_hand"] = self::getObjectListFromDB("SELECT 0 AS `card_id`, FLOOR(`card_type` / 10) type, `card_blocked` blocked FROM `troop` WHERE `card_location` = 'hand' AND `card_type_arg` = '{$spectator_id}'");
            $result["your_hand"] = self::getObjectListFromDB("SELECT FLOOR(`card_type` / 10) type, `card_blocked` blocked FROM `troop` WHERE `card_location` = 'hand' AND `card_type_arg` = '{$no_spectator_id}'");
            $result["my_discard"] = self::getObjectListFromDB("SELECT `card_id` `id`, `card_type` type  FROM `troop` WHERE `card_location` = 'discard' AND `card_type_arg` = '{$spectator_id}' ORDER BY `card_type`");
            $result["your_discard"] = self::getObjectListFromDB("SELECT `card_id` `id`, `card_type` type FROM `troop` WHERE `card_location` = 'discard' AND `card_type_arg` = '{$no_spectator_id}' ORDER BY `card_type`");
        }
        $result["board_troops"] = self::getObjectListFromDB("SELECT `card_id` `id`, `card_type` type, `card_type_arg` type_arg, `card_location` location, `card_location_arg` location_arg, `card_ordre` ordre FROM `troop` WHERE `card_location` = 'board' ORDER BY `card_ordre`");
        $result["blue_blocked"] = self::getObjectListFromDB("SELECT `card_blocked` blocked FROM `troop` WHERE `card_blocked` > 0 AND `card_type_arg` = '{$spectator_id}'", true);
        $result["red_blocked"] = self::getObjectListFromDB("SELECT `card_blocked` blocked FROM `troop` WHERE `card_blocked` > 0 AND `card_type_arg` = '{$no_spectator_id}'", true);

        $board_name = $this->_board_names[$this->getGameStateValue('board')];
        $board_id = $this->getGameStateValue('board');
        $result["board_name"] = $board_name;
        $result["board_id"] = $board_id;

        $result["bases"] = $this->_bases[$board_name];
        $result["regions"] = $this->_regions[$board_name];
        $result["medals"] = $this->_medals[$board_name];
        $result["goodies"] = $this->_goodies;
        $result["troop_types"] = $this->_troop_types;
        $result["board_type"] = $this->_board_types[$board_id];
        $result["board_types"] = $this->_board_types;

        $result["nb_deck_blue"] = self::getUniqueValueFromDB("SELECT COUNT(`card_id`) FROM `troop` WHERE `card_location`='deckblue'");
        $result["nb_deck_red"] = self::getUniqueValueFromDB("SELECT COUNT(`card_id`) FROM `troop` WHERE `card_location`='deckred'");

        $result["full_regions"] = self::getObjectListFromDB("SELECT `zone_id` `id` FROM `zone` WHERE `zone_star` > 0", true);



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
        if (game::$instance->getGameStateValue('endgame') == 1) {
            return 100;
        } else {

            $max_medals = $this->_medals_to_win[$this->getGameStateValue('board')];
            $players_star = self::getObjectListFromDB("SELECT `player_star` star FROM `player`", true);
            $max_player = max($players_star);

            return floor(($max_player * 100) / $max_medals);
        }
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

    public function addPending(int $player_id, string $function, ?string $arg = NULL, ?string $arg2 = NULL, ?string $arg3 = NULL, ?string $arg4 = NULL): void
    {
        $sql = "INSERT INTO `pending` (`player_id`, `function`, `arg`, `arg2`, `arg3`, `arg4`) 
                VALUES (" . $player_id . ", '" . $function . "', '" . $arg . "', '" . $arg2 . "', '" . $arg3 . "', '" . $arg4 . "')";
        $this->DbQuery($sql);
    }


    // le pending est envoyé au fond (First mais on lit de Bas en Haut)
    public function addPendingFirst(int $player_id, string $function, ?string $arg = NULL, ?string $arg2 = NULL, ?string $arg3 = NULL, ?string $arg4 = NULL): void
    {
        $minid = $this->getUniqueValueFromDB("SELECT MIN(`id`) FROM `pending`") - 1;
        $sql = "INSERT INTO `pending` (`id`, `player_id`, `function`, `arg`, `arg2`, `arg3`, `arg4`) 
                VALUES (" . $minid . "," . $player_id . ", '" . $function . "', '" . $arg . "', '" . $arg2 . "', '" . $arg3 . "', '" . $arg4 . "')";
        $this->DbQuery($sql);
    }

    function checkArgs($arg1)
    {
        $ret = self::argPlayerTurn();

        if (!in_array($arg1, $ret['selectable']) && !in_array($arg1, $ret['buttons'])) {
            throw new UserException("Not a valid selection");
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
            $sql = "SELECT `player_id` FROM `player` WHERE `player_no` = (SELECT MAX(`player_no`) FROM `player`)";
            return (int) $this->getUniqueValueFromDB($sql);
        }
    }

    /**
     * returns player_id having player_no 1, used for any spectator
     */
    function getSpectatorId(): int
    {
        $sql = "SELECT `player_id` FROM `player` WHERE `player_no` = (SELECT MIN(`player_no`) FROM `player`)";
        return (int) $this->getUniqueValueFromDB($sql);
    }





    //VERIFICATION DES BASES SELECTIONNABLES EN FONCTION DE LA TROUPE

    function getPossibleBase($table_start_bases_player, $troop_id, $player_id)
    {
        $possible_bases = [];
        $new_bases = $table_start_bases_player; //base de départ à inspecter en premier
        $dynamic_base = []; // base dynamique pour les nouvelles bases à inspecter
        $visited_bases = []; // Liste des bases déjà visitées


        // recuperation du nom du board
        $tableau_boards_name = ["castle", "pool", "clouds", "jungle", "cemetery", "carribean", "station", "battlefield", "christmas", "croisette"];
        $board_name = $tableau_boards_name[$this->getGameStateValue('board') - 1];

        //recuperation de la force de la troupe selectionnée
        $explode_troop_id = explode("_", $troop_id);
        $troop_selected_force = self::getUniqueValueFromDB("SELECT `card_type` FROM `troop` WHERE `card_id`='{$explode_troop_id[1]}'") % 10;



        while (count($new_bases) != 0) {
            foreach ($new_bases as $base) {
                // Éviter de revisiter une base
                if (in_array($base, $visited_bases)) {
                    continue;
                }

                $visited_bases[] = $base;

                $bases_adjacentes = $this->_bases[$board_name][$base]['adjacents'];

                foreach ($bases_adjacentes as $base_adjacente) {
                    if (!in_array($base_adjacente, $table_start_bases_player))  //ne pas proposer ses propres bases de départ pour positionner sa troupe
                    {
                        // recuperation du pouvoir de la base (pour gérer le board Pool)
                        $base_power = game::$instance->_bases[$board_name][$base_adjacente]['power'];


                        // Vérifie le nombre de troupes sur la base adjacente
                        $nb_troop_on_base = count(self::getObjectListFromDB("SELECT `card_id` FROM `troop` WHERE `card_location` = 'board' AND `card_location_arg` = '{$base_adjacente}'", true));

                        if ($nb_troop_on_base == 0) //si la base est vide
                        {
                            if (($base_power == 21) && (game::$instance->bga->tableOptions->get(100) == 1)) {
                                if (($troop_selected_force == 1) || ($troop_selected_force == 2) || ($troop_selected_force == 8)) {
                                    $possible_bases[] = $base_adjacente;
                                }
                            } elseif (($base_power == 23) && (game::$instance->bga->tableOptions->get(100) == 1)) {
                                if (($troop_selected_force == 3) || ($troop_selected_force == 4) || ($troop_selected_force == 5) || ($troop_selected_force == 8)) {
                                    $possible_bases[] = $base_adjacente;
                                }
                            } elseif (($base_power == 24) && (game::$instance->bga->tableOptions->get(100) == 1)) {
                                if (($troop_selected_force == 4) || ($troop_selected_force == 5) || ($troop_selected_force == 6) || ($troop_selected_force == 7)) {
                                    $possible_bases[] = $base_adjacente;
                                }
                            } elseif (($base_power == 26) && (game::$instance->bga->tableOptions->get(100) == 1)) {
                                if (($troop_selected_force == 6) || ($troop_selected_force == 7) || ($troop_selected_force == 8)) {
                                    $possible_bases[] = $base_adjacente;
                                }
                            } else {
                                $possible_bases[] = $base_adjacente;
                            }
                        } else // si la base est occupée on recupere la troupe avec l'ordre max et on regarde à quel joueur elle appartient
                        {
                            $infos_troopmax = self::getObjectListFromDB("SELECT `card_id` `id`, `card_type` type, `card_type_arg` type_arg, `card_ordre` ordre FROM `troop` WHERE `card_location` = 'board' AND `card_location_arg` = '{$base_adjacente}' AND `card_ordre` = (SELECT MAX(`card_ordre`) FROM `troop` WHERE `card_location` = 'board' AND `card_location_arg` = '{$base_adjacente}')");

                            if ($infos_troopmax[0]['type_arg'] != $player_id) // si elle appartient au joueur adverse on compare les forces
                            {
                                $troop_opponent_force = $infos_troopmax[0]['type'] % 10;

                                if (($troop_opponent_force < $troop_selected_force) || ($troop_opponent_force == 8)) {

                                    if (($base_power == 21) && (game::$instance->bga->tableOptions->get(100) == 1)) {
                                        if (($troop_selected_force == 1) || ($troop_selected_force == 2) || ($troop_selected_force == 8)) {
                                            $possible_bases[] = $base_adjacente;
                                        }
                                    } elseif (($base_power == 23) && (game::$instance->bga->tableOptions->get(100) == 1)) {
                                        if (($troop_selected_force == 3) || ($troop_selected_force == 4) || ($troop_selected_force == 5) || ($troop_selected_force == 8)) {
                                            $possible_bases[] = $base_adjacente;
                                        }
                                    } elseif (($base_power == 24) && (game::$instance->bga->tableOptions->get(100) == 1)) {
                                        if (($troop_selected_force == 4) || ($troop_selected_force == 5) || ($troop_selected_force == 6) || ($troop_selected_force == 7)) {
                                            $possible_bases[] = $base_adjacente;
                                        }
                                    } elseif (($base_power == 26) && (game::$instance->bga->tableOptions->get(100) == 1)) {
                                        if (($troop_selected_force == 6) || ($troop_selected_force == 7) || ($troop_selected_force == 8)) {
                                            $possible_bases[] = $base_adjacente;
                                        }
                                    } else {
                                        $possible_bases[] = $base_adjacente;
                                    }
                                }
                            } else // si elle appartient au joueur actif, on peut s'y positionner
                            {
                                if (($base_power == 21) && (game::$instance->bga->tableOptions->get(100) == 1)) {
                                    if (($troop_selected_force == 1) || ($troop_selected_force == 2) || ($troop_selected_force == 8)) {
                                        $possible_bases[] = $base_adjacente;
                                    }
                                } elseif (($base_power == 23) && (game::$instance->bga->tableOptions->get(100) == 1)) {
                                    if (($troop_selected_force == 3) || ($troop_selected_force == 4) || ($troop_selected_force == 5) || ($troop_selected_force == 8)) {
                                        $possible_bases[] = $base_adjacente;
                                    }
                                } elseif (($base_power == 24) && (game::$instance->bga->tableOptions->get(100) == 1)) {
                                    if (($troop_selected_force == 4) || ($troop_selected_force == 5) || ($troop_selected_force == 6) || ($troop_selected_force == 7)) {
                                        $possible_bases[] = $base_adjacente;
                                    }
                                } elseif (($base_power == 26) && (game::$instance->bga->tableOptions->get(100) == 1)) {
                                    if (($troop_selected_force == 6) || ($troop_selected_force == 7) || ($troop_selected_force == 8)) {
                                        $possible_bases[] = $base_adjacente;
                                    }
                                } else {
                                    $possible_bases[] = $base_adjacente;
                                }

                                $dynamic_base[] = $base_adjacente;  // je mets cette base dans dynamic car on peut continuer au dela d'elle et donc tester ses bases adjacentes

                            }
                        }
                    }
                }
            }

            // Supprime les doublons et s'assurer de ne pas revisiter les bases
            $possible_bases = array_unique($possible_bases);  // suprime les doublons de possibles bases
            $dynamic_base = array_diff(array_unique($dynamic_base), $visited_bases);  // supprime les doublons de dynamic base et enleve les bases deja visitées

            // Met à jour new_bases pour la prochaine itération
            $new_bases = $dynamic_base;
            $dynamic_base = [];
        }


        if ($troop_selected_force == 4) {


            $all_bases = $this->_bases[$board_name];

            $bases_crochet = array_map('strval', array_keys($all_bases));

            $bases_crochet_sans_QG = [];

            foreach ($bases_crochet as $liste_bases_crochet) {
                if (($liste_bases_crochet >= 10) && ($liste_bases_crochet <= 40)) {
                    $bases_crochet_sans_QG[] = $liste_bases_crochet;
                }
            }

            $diff = array_diff($bases_crochet_sans_QG, $possible_bases);

            $bases_crochet_ok = [];

            foreach ($diff as $testotherbase) {
                $nb_troop_on_base = count(self::getObjectListFromDB("SELECT `card_id` FROM `troop` WHERE `card_location` = 'board' AND `card_location_arg` = '{$testotherbase}'", true));

                // recuperation du pouvoir de la base (pour gérer le board Pool)
                $base_power = game::$instance->_bases[$board_name][$testotherbase]['power'];

                if ((($base_power != 21) && ($base_power != 26) && ($base_power != 71)) || (game::$instance->bga->tableOptions->get(100) == 2)) {

                    if ($nb_troop_on_base == 0) //si la base est vide
                    {

                        $bases_crochet_ok[] = $testotherbase;
                    } else // si la base est occupée on recupere la troupe avec l'ordre max et on regarde à quel joueur elle appartient
                    {
                        $infos_troopmax = self::getObjectListFromDB("SELECT `card_id` `id`, `card_type` type, `card_type_arg` type_arg, `card_ordre` ordre FROM `troop` WHERE `card_location` = 'board' AND `card_location_arg` = '{$testotherbase}' AND `card_ordre` = (SELECT MAX(`card_ordre`) FROM `troop` WHERE `card_location` = 'board' AND `card_location_arg` = '{$testotherbase}')");

                        if ($infos_troopmax[0]['type_arg'] != $player_id) // si elle appartient au joueur adverse on compare les forces
                        {
                            $troop_opponent_force = $infos_troopmax[0]['type'] % 10;

                            if (($troop_opponent_force < 4) || ($troop_opponent_force == 8)) {
                                $bases_crochet_ok[] = $testotherbase;
                            }
                        } else // si elle appartient au joueur actif, on peut s'y positionner
                        {
                            $bases_crochet_ok[] = $testotherbase;
                        }
                    }
                }
            }



            $possible_bases = array_merge($possible_bases, $bases_crochet_ok);
        }



        return $possible_bases;
    }


    //VERIFICATION DES REGIONS et GAIN MEDAILLES

    function testZoneAndStar($numero_base_impactee, $board_name) // a chaque fois qu'une troupe est placée ou discard
    {
        $win = 0;

        if (($numero_base_impactee >= 10) && ($numero_base_impactee < 40)) {

            $list_all_zone = [];

            $emptied_regions = [];
            $count_regions = 0;
            $count_medals = 0;

            // on recupere toutes les zones du plateau sous forme de tableau de bases concernées
            foreach (game::$instance->_regions[$board_name] as $zone) {
                $list_all_zone[] = [
                    'value' => $zone['value'],
                    'bases' => $zone['bases'],
                    'medals' => $zone['medals'],
                ];
            }

            //on regarde pour chaque zone 
            foreach ($list_all_zone as $test) {
                // si la zone contient la base impactée par un changement 
                if (in_array($numero_base_impactee, $test['bases'])) {
                    $list_id_player_sur_zone = [];
                    //puis on va regarder si toute les bases de cette zone sont occuppée et si c'est la même couleur
                    foreach ($test['bases'] as $base) {
                        // on va placer l'id du joueur qui detient une base et 0 si elle est vide

                        $count_troop_on_base = count(self::getObjectListFromDB("SELECT `card_id` FROM `troop` WHERE `card_location` = 'board' AND `card_location_arg` = '{$base}'", true));

                        if ($count_troop_on_base >= 1) {

                            $infos_troopmax = self::getObjectListFromDB("SELECT `card_id` `id`, `card_type` type, `card_type_arg` type_arg, `card_ordre` ordre FROM `troop` WHERE `card_location` = 'board' AND `card_location_arg` = '{$base}' AND `card_ordre` = (SELECT MAX(`card_ordre`) FROM `troop` WHERE `card_location` = 'board' AND `card_location_arg` = '{$base}')");
                            $list_id_player_sur_zone[] = $infos_troopmax[0]['type_arg'];
                        } else {

                            $list_id_player_sur_zone[] = 0;
                        }
                    }

                    $idplayer = $list_id_player_sur_zone[0]; // La première valeur du tableau
                    $allEqual = true;



                    foreach ($list_id_player_sur_zone as $value) {
                        if ($value !== $idplayer) {
                            $allEqual = false;
                            break;
                        }
                    }





                    if (($allEqual) && ($idplayer != 0)) {


                        // GAIN REGION POUR JOUEUR $idplayer

                        // TEST SI MEDAILLES ENCORE PRESENTES (pas deja gagnées)
                        $etoile = (int)self::getUniqueValueFromDB("SELECT `zone_star` FROM `zone` WHERE `zone_id` = '{$test['value']}'");

                        if ($etoile >= 1) {
                            self::DbQuery("UPDATE `zone` set `zone_star` = 0 WHERE `zone_id` = '{$test['value']}'");


                            $count_regions = $count_regions + 1;
                            $count_medals = $count_medals + $etoile;

                            $emptied_regions[] = $test['value'];

                            $player_id_gain = $idplayer;
                        }
                    }
                }
            }



            if ($count_medals >= 1) {

                $medals_already_won = (int)self::getUniqueValueFromDB("SELECT `player_star` FROM `player` WHERE `player_id` = '{$player_id_gain}'");
                self::DbQuery("UPDATE `player` set `player_star` = `player_star` + $count_medals WHERE `player_id` = '{$player_id_gain}'");

                $player_name = self::getUniqueValueFromDB("SELECT `player_name` FROM `player` WHERE `player_id` = '{$player_id_gain}'");

                if ($count_regions == 1 && $count_medals == 1) {
                    $txt = clienttranslate('${player_name} controls <b>${nb_region}</b> region and takes <b>${nb_medal}</b>${log}');
                } else if ($count_regions == 1 && $count_medals > 1) {
                    $txt = clienttranslate('${player_name} controls <b>${nb_region}</b> region and takes <b>${nb_medal}</b>${log}');
                } else {
                    $txt = clienttranslate('${player_name} controls <b>${nb_region}</b> regions and takes <b>${nb_medal}</b>${log}');
                }

                game::$instance->notifyAllPlayers(
                    'gainMedal',
                    $txt,
                    array(

                        'player_name' => $player_name,
                        'nb_region' => $count_regions,
                        'nb_medal' => $count_medals,
                        'emptied_regions' => $emptied_regions,
                        'player_id' => $player_id_gain,
                        'medals_already_won' => $medals_already_won,
                        'log' => game::$instance->getLogsType("M"),

                    )
                );

                game::$instance->incStat($count_medals, 'medals_won', $player_id_gain);
                game::$instance->incStat($count_regions, 'regions_controlled', $player_id_gain);

                // attendre que les animations de medailles soient terminées

                $time = 650 * $count_medals;
                $this->bga->notify->all('simplePause', '', ['time' => $time]);

                // Test Fin de partie

                $max_medals = $this->_medals_to_win[$this->getGameStateValue('board')];
                $total_player_medals = (int)self::getUniqueValueFromDB("SELECT `player_star` FROM `player` WHERE `player_id` = '{$player_id_gain}'");


                if ($total_player_medals >= $max_medals) {
                    $win = 1;
                }
            }
        }

        return $win;
    }

    // DEBLOQUER TROUPE EN FIN DE TOUR

    function deblock_troops(int $player_id)
    {
        $nb_bloque = count(self::getObjectListFromDB("SELECT `card_id` FROM `troop` WHERE `card_location` = 'hand' AND `card_type_arg` = '{$player_id}' AND `card_blocked` > 0", true));

        if ($nb_bloque >= 1) {
            $nb_troops_hand = count(self::getObjectListFromDB("SELECT `card_id` FROM `troop` WHERE `card_location` = 'hand' AND `card_type_arg` = '{$player_id}'", true));
            self::DbQuery("UPDATE `troop` set `card_blocked` = 0 WHERE `card_type_arg` = '{$player_id}' AND `card_blocked` > 0");

            game::$instance->notifyAllPlayers(
                'unhideTroopOnRack',
                '',
                array(

                    'player_id' => $player_id,
                    'nb_troops_hand' => $nb_troops_hand,


                )
            );
        }
    }

    /// ICONES POUR LOG

    function getLogsType($type)
    {
        $board_id = $this->getGameStateValue('board');
        if ($board_id == 9) {
            if ($type == 10) {
                return "<div class='icon_log_x icon_blue icon_troop_0' title=''></div>";
            }
            if ($type == 11) {
                return "<div class='icon_log_x icon_blue icon_troop_1' title=''></div>";
            }
            if ($type == 12) {
                return "<div class='icon_log_x icon_blue icon_troop_2' title=''></div>";
            }
            if ($type == 13) {
                return "<div class='icon_log_x icon_blue icon_troop_3' title=''></div>";
            }
            if ($type == 14) {
                return "<div class='icon_log_x icon_blue icon_troop_4' title=''></div>";
            }
            if ($type == 15) {
                return "<div class='icon_log_x icon_blue icon_troop_5' title=''></div>";
            }
            if ($type == 16) {
                return "<div class='icon_log_x icon_blue icon_troop_6' title=''></div>";
            }
            if ($type == 17) {
                return "<div class='icon_log_x icon_blue icon_troop_7' title=''></div>";
            }
            if ($type == 18) {
                return "<div class='icon_log_x icon_blue icon_troop_8' title=''></div>";
            }
            if ($type == 20) {
                return "<div class='icon_log_x icon_red icon_troop_0' title=''></div>";
            }
            if ($type == 21) {
                return "<div class='icon_log_x icon_red icon_troop_1' title=''></div>";
            }
            if ($type == 22) {
                return "<div class='icon_log_x icon_red icon_troop_2' title=''></div>";
            }
            if ($type == 23) {
                return "<div class='icon_log_x icon_red icon_troop_3' title=''></div>";
            }
            if ($type == 24) {
                return "<div class='icon_log_x icon_red icon_troop_4' title=''></div>";
            }
            if ($type == 25) {
                return "<div class='icon_log_x icon_red icon_troop_5' title=''></div>";
            }
            if ($type == 26) {
                return "<div class='icon_log_x icon_red icon_troop_6' title=''></div>";
            }
            if ($type == 27) {
                return "<div class='icon_log_x icon_red icon_troop_7' title=''></div>";
            }
            if ($type == 28) {
                return "<div class='icon_log_x icon_red icon_troop_8' title=''></div>";
            }
            if ($type == "M") {
                return "<div class='icon_medal_log' title=''></div>";
            }
        } else {
            if ($type == 10) {
                return "<div class='icon_log icon_blue icon_troop_0' title=''></div>";
            }
            if ($type == 11) {
                return "<div class='icon_log icon_blue icon_troop_1' title=''></div>";
            }
            if ($type == 12) {
                return "<div class='icon_log icon_blue icon_troop_2' title=''></div>";
            }
            if ($type == 13) {
                return "<div class='icon_log icon_blue icon_troop_3' title=''></div>";
            }
            if ($type == 14) {
                return "<div class='icon_log icon_blue icon_troop_4' title=''></div>";
            }
            if ($type == 15) {
                return "<div class='icon_log icon_blue icon_troop_5' title=''></div>";
            }
            if ($type == 16) {
                return "<div class='icon_log icon_blue icon_troop_6' title=''></div>";
            }
            if ($type == 17) {
                return "<div class='icon_log icon_blue icon_troop_7' title=''></div>";
            }
            if ($type == 18) {
                return "<div class='icon_log icon_blue icon_troop_8' title=''></div>";
            }
            if ($type == 20) {
                return "<div class='icon_log icon_red icon_troop_0' title=''></div>";
            }
            if ($type == 21) {
                return "<div class='icon_log icon_red icon_troop_1' title=''></div>";
            }
            if ($type == 22) {
                return "<div class='icon_log icon_red icon_troop_2' title=''></div>";
            }
            if ($type == 23) {
                return "<div class='icon_log icon_red icon_troop_3' title=''></div>";
            }
            if ($type == 24) {
                return "<div class='icon_log icon_red icon_troop_4' title=''></div>";
            }
            if ($type == 25) {
                return "<div class='icon_log icon_red icon_troop_5' title=''></div>";
            }
            if ($type == 26) {
                return "<div class='icon_log icon_red icon_troop_6' title=''></div>";
            }
            if ($type == 27) {
                return "<div class='icon_log icon_red icon_troop_7' title=''></div>";
            }
            if ($type == 28) {
                return "<div class='icon_log icon_red icon_troop_8' title=''></div>";
            }
            if ($type == "M") {
                return "<div class='icon_medal_log' title=''></div>";
            }
        }
    }


    // Stats turns

    function updateNbTurns()
    {
        $player_id = self::getActivePlayerId();
        $this->incStat(1, 'turns_number', (int)$player_id);
        if (self::getPlayerNoById((int)$player_id) == 1) {
            $this->incStat(1, 'turns_number');
        }
    }


    // DEBUG

    function debug_placeTroopOnBaseFromDeck(int $player_id, int $base)
    {

        $players = self::getObjectListFromDB("SELECT `player_id` FROM `player`", true);

        if (in_array($player_id, $players)) {

            $infos_player = self::getObjectFromDB("SELECT * FROM `player` WHERE `player_id` = {$player_id}");

            if ($infos_player['player_color'] == "4f66a2") {
                //DECLARATION DU DECK
                $player_deck = "deckblue";
            }

            if ($infos_player['player_color'] == "d1553e") {
                //DECLARATION DU DECK
                $player_deck = "deckred";
            }

            $counttroopdeck = count(self::getObjectListFromDB("SELECT `card_id` FROM `troop` WHERE `card_location`='{$player_deck}'", true));

            if ($counttroopdeck >= 1) {
                $tableau_boards_name = ["castle", "pool", "clouds", "jungle", "cemetery", "carribean", "station", "battlefield", "christmas", "croisette"];
                $board_name = $tableau_boards_name[$this->getGameStateValue('board') - 1];

                $all_bases = $this->_bases[$board_name];
                $id_bases = array_map('strval', array_keys($all_bases));

                if (in_array($base, $id_bases)) {

                    $compteur_troop_sur_base = count(self::getObjectListFromDB("SELECT `card_id` FROM `troop` WHERE `card_location` ='board' AND `card_location_arg` = '{$base}'", true));
                    $card = game::$instance->troop->pickCardForLocation($player_deck, 'board', $base);
                    self::DbQuery("UPDATE `troop` set `card_ordre` = $compteur_troop_sur_base + 1 WHERE `card_id` = '{$card['id']}'");
                }
            }
        }
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

        $pending =  self::getObjectFromDB("SELECT* FROM `pending` order by `id` desc limit 1");
        $this->callPending($pending, true, $arg1);
        self::DbQuery("delete from `pending` where `id`=" . $pending['id']);
        //$this->giveExtraTime(self::getActivePlayerId());
        $this->gamestate->nextState('next');
    }

    public function actButton(string $arg1)
    {

        self::checkArgs($arg1);

        $pending =  self::getObjectFromDB("SELECT* FROM `pending` order by `id` desc limit 1");
        $this->callPending($pending, true, $arg1);
        self::DbQuery("delete from `pending` where `id`=" . $pending['id']);
        //$this->giveExtraTime(self::getActivePlayerId());
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
        $pending =  self::getObjectFromDB("SELECT* FROM `pending` order by `id` desc limit 1");
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

        $pending =  self::getObjectFromDB("SELECT * FROM `pending` order by `id` desc limit 1");
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
                self::DbQuery("delete from `pending` where `id`=" . $pending['id']);
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
                        self::DbQuery("delete from `pending` where `player_id` = {$player_id}");
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

        throw new VisibleSystemException("Zombie mode not supported at this game state: \"{$state_name}\".");
    }
}
