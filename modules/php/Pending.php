<?php

namespace Bga\Games\toybattle;   // ATTENTION NOM DU JEU
use APP_GameClass; // ATTENTION

require_once 'actions/Troops.php';
require_once 'actions/Bases.php';

class Pending extends APP_GameClass
{
    use TroopsTrait; // ATTENTION
    use BasesTrait; // ATTENTION

    public function __construct($player_id)
    {
        $this->player_id = $player_id;
        $p = self::getObjectFromDB("SELECT * FROM player WHERE player_id = {$player_id}");
        $this->player_no = $p['player_no'];
        $this->player_id = $p['player_id'];
        $this->player_name = $p['player_name'];
        $this->player_score = $p['player_score'];
        $this->player_color = $p['player_color'];

        $tableau_boards_name = ["castle", "pool", "clouds", "jungle", "cemetery", "carribean", "station", "battlefield", "christmas", "croisette"];
        $this->board_name = $tableau_boards_name[game::$instance->getGameStateValue('board') - 1];

        $this->player_id_opponent = self::getUniqueValueFromDB("SELECT player_id FROM player WHERE player_id != '{$this->player_id}'");
        $this->player_name_opponent = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id != '{$this->player_id}'");
        $this->player_color_opponent = self::getUniqueValueFromDB("SELECT player_color FROM player WHERE player_id != '{$this->player_id}'");


        // COLOR A CHANGER SI MODIFICATION DES COULEURS DE BASE DECLAREES DANS GAMEINFOS

        if ($p['player_color'] == "4f66a2") {
            //DECLARATION DU DECK
            $this->player_deck = "deckblue";
            $this->player_deck_id = "blue_deck";

            //DECLARATION DE LA COULEUR TITLE
            $this->player_color_title = "blue";

            //DECLARATION DU NUMERO DE COULEUR
            $this->player_color_number = 1;
            $this->opponent_color_number = 2;

            $this->player_color_text = "blue";
            $this->opponent_color_text = "red";

            //DECLARATION DES BASES DE DEPART
            if (($this->board_name == 'castle') || ($this->board_name == 'clouds') || ($this->board_name == 'jungle') || ($this->board_name == 'cemetery') || ($this->board_name == 'station') || ($this->board_name == 'battlefield') || ($this->board_name == 'christmas') || ($this->board_name == 'croisette')) {
                $this->start_base = [1];
                $this->opponent_start_base = [41];
            }

            if ($this->board_name == 'pool') {
                $this->start_base = [1, 2];
                $this->opponent_start_base = [41, 42];
            }

            if ($this->board_name == 'carribean') {
                $this->start_base = [1, 2];
                $this->opponent_start_base = [41];
            }
        }

        if ($p['player_color'] == "d1553e") {
            //DECLARATION DU DECK
            $this->player_deck = "deckred";
            $this->player_deck_id = "red_deck";

            //DECLARATION DE LA COULEUR TITLE
            $this->player_color_title = "red";

            //DECLARATION DU NUMERO DE COULEUR
            $this->player_color_number = 2;
            $this->opponent_color_number = 1;

            $this->player_color_text = "red";
            $this->opponent_color_text = "blue";

            //DECLARATION DES BASES DE DEPART
            if (($this->board_name == 'castle') || ($this->board_name == 'clouds') || ($this->board_name == 'jungle') || ($this->board_name == 'cemetery') || ($this->board_name == 'station') || ($this->board_name == 'battlefield') || ($this->board_name == 'christmas') || ($this->board_name == 'croisette')) {
                $this->start_base = [41];
                $this->opponent_start_base = [1];
            }

            if ($this->board_name == 'pool') {
                $this->start_base = [41, 42];
                $this->opponent_start_base = [1, 2];
            }

            if ($this->board_name == 'carribean') {
                $this->start_base = [41];
                $this->opponent_start_base = [1, 2];
            }
        }


        /// PREFERENCE DE CONFIRMATION

        $this->player_pref_confirm = self::getUniqueValueFromDB("SELECT pgp_value FROM bga_user_preferences WHERE pgp_player='{$this->player_id}' AND pgp_preference_id = 100");

        /// PREFERENCE DE DISCARD OR BLOCK

        $this->player_pref_discard_block = self::getUniqueValueFromDB("SELECT pgp_value FROM bga_user_preferences WHERE pgp_player='{$this->player_id}' AND pgp_preference_id = 101");
    }







    function argNormalTurn($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} must choose an action');
        $ret['titleyou'] = clienttranslate('${you} must choose an action');


        $counttroopdeck = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='{$this->player_deck}'", true));
        $counttroophand = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='hand' AND card_type_arg = '{$this->player_id}'", true));

        $counttroophand_noblocked = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='hand' AND card_type_arg = '{$this->player_id}' AND card_blocked = 0", true));


        if (($counttroopdeck >= 2) && ($counttroophand <= 6)) {
            $ret["selectable"][] = $this->player_deck_id;
            $ret['buttons'][] = 'btn_draw_2';
        }

        if ((($counttroopdeck == 1) && ($counttroophand <= 7)) || (($counttroopdeck >= 1) && ($counttroophand == 7))) {
            $ret["selectable"][] = $this->player_deck_id;
            $ret['buttons'][] = 'btn_draw_1';
        }


        // TEST SI TROUPES NON BLOQUEES PEUVENT ETRE PLACEES
        if ($counttroophand_noblocked >= 1) {
            $place_ok = 0;
            $list_troop = self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='hand' AND card_type_arg = '{$this->player_id}'", true);
            foreach ($list_troop as $troop) {
                $troop_id = 'troop_' . $troop;
                $possible_base = game::$instance->getPossibleBase($this->start_base, $troop_id, $this->player_id);
                if (count($possible_base) >= 1) {
                    $place_ok = 1;
                }
            }

            if ($place_ok == 1) {
                $ret['buttons'][] = 'btn_place_troop';

                $list_troop = self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='hand' AND card_type_arg = '{$this->player_id}' AND card_blocked = 0", true);
                foreach ($list_troop as $troop) {
                    $troop_id = 'troop_' . $troop;
                    $possible_base = game::$instance->getPossibleBase($this->start_base, $troop_id, $this->player_id);
                    if (count($possible_base) >= 1) {
                        $ret["selectable"][] = $troop_id;
                    }
                }
            }
        }



        return $ret;
    }

    function NormalTurn($parg1, $parg2, $varg1, $varg2)
    {

        if ((($varg1 == "btn_draw_1") || ($varg1 == "btn_draw_2") || ($varg1 == $this->player_deck_id)) && ($this->player_pref_confirm == 1)) {
            game::$instance->addPending($this->player_id, "ConfirmDraw", $varg1);
        }

        if ((($varg1 == "btn_draw_1") || ($varg1 == "btn_draw_2") || ($varg1 == $this->player_deck_id)) && ($this->player_pref_confirm == 2)) {

            $nb_troops_hand = self::getUniqueValueFromDB("SELECT COUNT(card_id) FROM troop WHERE card_location = 'hand' AND card_type_arg = '{$this->player_id}'");
            $old_troops = self::getObjectListFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_ordre ordre FROM troop WHERE card_location = 'hand' AND card_type_arg ='{$this->player_id}'");

            $counttroopdeck = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='{$this->player_deck}'", true));

            if (($varg1 == 'btn_draw_2') || (($varg1 == $this->player_deck_id) && ($nb_troops_hand <= 6) && ($counttroopdeck >= 2))) {
                game::$instance->incStat(2, 'troops_drawn', $this->player_id);
                $new_troops = game::$instance->troop->pickCardsForLocation(2, $this->player_deck, 'hand');

                $type1 = $this->player_color_number . $new_troops[0]['type'] % 10;
                $type2 = $this->player_color_number . $new_troops[1]['type'] % 10;

                game::$instance->notifyPlayer(
                    $this->player_id,
                    'drawTroopPrivate',
                    clienttranslate('${you} draw ${log1} ${log2}'),
                    array(
                        'you' =>    [
                            'log' => '<b style="color: #${color};">${you_name}</b>',
                            'args' => ['you_name' => clienttranslate('You'), 'color' => $this->player_color, 'i18n' => ['you_name']]
                        ],
                        'player_id' => $this->player_id,
                        'origine' => "deck",
                        'new_troops' => $new_troops,
                        'old_troops' => $old_troops,
                        'log1' => game::$instance->getLogsType($type1),
                        'log2' => game::$instance->getLogsType($type2),




                    )
                );

                $type0 = $this->player_color_number . "0";

                game::$instance->notifyAllPlayers(
                    'drawTroopPublic',
                    '',
                    array(
                        'player_name' => $this->player_name,
                        'player_id' => $this->player_id,
                        'origine' => "deck",
                        'nb_troops' => 2,
                        'nb_troops_hand' => $nb_troops_hand,
                        'log0' => game::$instance->getLogsType($type0)


                    )
                );

                game::$instance->notifyAllPlayers(
                    'message_allplayers_without_player',
                    clienttranslate('${player_name} draws ${log0} ${log0}'),
                    array(
                        'player_name' => $this->player_name,
                        'player_id' => $this->player_id,
                        'log0' => game::$instance->getLogsType($type0),


                    )
                );
            }

            if (($varg1 == 'btn_draw_1') || (($varg1 == $this->player_deck_id) && ($nb_troops_hand == 7) && ($counttroopdeck >= 2)) || (($varg1 == $this->player_deck_id) && ($nb_troops_hand <= 7) && ($counttroopdeck == 1))) {
                game::$instance->incStat(1, 'troops_drawn', $this->player_id);
                $new_troops = game::$instance->troop->pickCardsForLocation(1, $this->player_deck, 'hand');

                $type1 = $this->player_color_number . $new_troops[0]['type'] % 10;

                game::$instance->notifyPlayer(
                    $this->player_id,
                    'drawTroopPrivate',
                    clienttranslate('${you} draw ${log1}'),
                    array(
                        'you' =>    [
                            'log' => '<b style="color: #${color};">${you_name}</b>',
                            'args' => ['you_name' => clienttranslate('You'), 'color' => $this->player_color, 'i18n' => ['you_name']]
                        ],
                        'player_id' => $this->player_id,
                        'origine' => "deck",
                        'new_troops' => $new_troops,
                        'log1' => game::$instance->getLogsType($type1),



                    )
                );

                $type0 = $this->player_color_number . "0";

                game::$instance->notifyAllPlayers(
                    'drawTroopPublic',
                    '',
                    array(
                        'player_name' => $this->player_name,
                        'player_id' => $this->player_id,
                        'origine' => "deck",
                        'nb_troops' => 1,
                        'nb_troops_hand' => $nb_troops_hand,
                        'log0' => game::$instance->getLogsType($type0),


                    )
                );

                game::$instance->notifyAllPlayers(
                    'message_allplayers_without_player',
                    clienttranslate('${player_name} draws ${log0}'),
                    array(
                        'player_name' => $this->player_name,
                        'player_id' => $this->player_id,
                        'log0' => game::$instance->getLogsType($type0),


                    )
                );
            }

            game::$instance->giveExtraTime($this->player_id);
            game::$instance->deblock_troops($this->player_id);
            game::$instance->updateNbTurns();
            game::$instance->addPendingFirst($this->player_id, "NormalTurn");
        }

        if (($varg1 == "btn_place_troop")) {
            game::$instance->addPending($this->player_id, "ChooseTroop");
        }

        if (strpos($varg1, 'troop') === 0) {
            game::$instance->addPending($this->player_id, "ChooseBase", $varg1);
        }

        if ($varg1 == null) {
            game::$instance->setGameStateValue("endgame", 1); // pour progression
            game::$instance->addPending($this->player_id, "FinGame1", 1);
        }
    }



    function argChooseTroop($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} places a Troop');
        $ret['titleyou'] = clienttranslate('${you} must choose a Troop to place');



        $list_troop = self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='hand' AND card_type_arg = '{$this->player_id}' AND card_blocked = 0", true);
        foreach ($list_troop as $troop) {
            $troop_id = 'troop_' . $troop;
            $possible_base = game::$instance->getPossibleBase($this->start_base, $troop_id, $this->player_id);
            if (count($possible_base) >= 1) {
                $ret["selectable"][] = $troop_id;
            }
        }



        $ret['buttons'][] = 'btn_cancel';


        return $ret;
    }

    function ChooseTroop($parg1, $parg2, $varg1, $varg2)
    {
        if ($varg1 == "btn_cancel") {
            game::$instance->addPending($this->player_id, "NormalTurn");
        } else {
            game::$instance->addPending($this->player_id, "ChooseBase", $varg1);
        }
    }

    function argChooseBase($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} places a Troop');




        $ret["selected"][] = $parg1;


        $possible_base = game::$instance->getPossibleBase($this->start_base, $parg1, $this->player_id);

        if (count($possible_base) >= 1) {
            $ret['titleyou'] = clienttranslate('${you} must choose a base or change Troop');
        } else {
            $ret['titleyou'] = clienttranslate('${you} cannot place this Troop');
        }

        foreach ($possible_base as $base) {

            $ret["selectable"][] = "base_" . $this->board_name . "_" . $base;
        }


        $list_troop = self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='hand' AND card_type_arg = '{$this->player_id}' AND card_blocked = 0", true);
        foreach ($list_troop as $troop) {
            $troop_id = 'troop_' . $troop;
            $possible_base = game::$instance->getPossibleBase($this->start_base, $troop_id, $this->player_id);
            if ((count($possible_base) >= 1) && ($troop_id != $parg1)) {
                $ret["selectable"][] = $troop_id;
            }
        }



        $ret['buttons'][] = 'btn_cancel';


        return $ret;
    }

    function ChooseBase($parg1, $parg2, $varg1, $varg2)
    {

        if ($varg1 == "btn_cancel") {
            game::$instance->addPending($this->player_id, "NormalTurn");
        } elseif (strpos($varg1, 'troop') === 0) {
            game::$instance->addPending($this->player_id, "ChooseBase", $varg1);
        } else {

            if ($this->player_pref_confirm == 1) {
                game::$instance->addPending($this->player_id, "ConfirmPlace", $parg1, $varg1);
            }

            if ($this->player_pref_confirm == 2) {

                game::$instance->incStat(1, 'troops_played', $this->player_id);

                $explode_troop = explode("_", $parg1);
                $explode_base = explode("_", $varg1);

                $compteur_troop_sur_base = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location ='board' AND card_location_arg = '{$explode_base[2]}'", true));

                $nb_troops_hand = self::getUniqueValueFromDB("SELECT COUNT(card_id) FROM troop WHERE card_location = 'hand' AND card_type_arg = '{$this->player_id}'");

                $numbers_no_blocked = [];
                $troops_blocked = self::getObjectListFromDB("SELECT card_blocked FROM troop WHERE card_location = 'hand' AND card_type_arg = '{$this->player_id}' AND card_blocked != 0", true);
                for ($i = 1; $i <= $nb_troops_hand; $i++) {
                    if (!in_array($i, $troops_blocked)) {
                        $numbers_no_blocked[] = $i;
                    }
                }

                if (count($numbers_no_blocked) >= 1) {
                    $valeur_max = max($numbers_no_blocked);
                    self::DbQuery("UPDATE troop set card_blocked = card_blocked - 1 WHERE card_location = 'hand' AND card_type_arg = '{$this->player_id}' AND card_blocked > '{$valeur_max}'");
                }


                game::$instance->troop->moveCard($explode_troop[1], 'board', $explode_base[2]);

                self::DbQuery("UPDATE troop set card_ordre = $compteur_troop_sur_base + 1 WHERE card_id = '{$explode_troop[1]}'");

                $infos_troop = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_ordre ordre FROM troop WHERE card_id = '{$explode_troop[1]}'");

                $type1 = $infos_troop['type'];


                game::$instance->notifyAllPlayers(
                    'moveTroop',
                    clienttranslate('${player_name} places ${log1}'),
                    array(
                        'base_id' => $varg1,
                        'ordre' => $compteur_troop_sur_base + 1,
                        'player_name' => $this->player_name,
                        'player_id' => $this->player_id,
                        'origine' => "hand",
                        'infos_troop' => $infos_troop,
                        'nb_troops_hand' => $nb_troops_hand,
                        'log1' => game::$instance->getLogsType($type1),
                        'numbers_no_blocked' => $numbers_no_blocked,


                    )
                );


                $numero_base = $explode_base[2];
                $troop_id = $explode_troop[1];

                self::DbQuery("INSERT INTO checkbase (troop_id, base) VALUES ({$troop_id}, {$numero_base})");

                $win = game::$instance->testZoneAndStar($numero_base, $this->board_name);

                if ($win == 0) {
                    if (in_array($numero_base, $this->opponent_start_base)) {
                        game::$instance->setGameStateValue("endgame", 1); // pour progression
                        $numtroop = intval($type1) % 10;
                        game::$instance->addPending($this->player_id, "FinGame1", 3, $numtroop);
                    } else {
                        game::$instance->addPending($this->player_id, "VerifTroop", $troop_id, $numero_base);
                    }
                }

                if ($win == 1) {
                    game::$instance->setGameStateValue("endgame", 1); // pour progression
                    game::$instance->addPending($this->player_id, "FinGame1", 2);
                }
            }
        }
    }




    //////////////////// ACTION WITH CONFIRM PREF /////////////////////////////

    function argConfirmPlace($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} places a Troop');
        $ret['titleyou'] = clienttranslate('${you} must confirm');

        $ret["selected"][] = $parg1;
        $ret["selected"][] = $parg2;


        $ret['buttons'][] = 'btn_yes';
        $ret['buttons'][] = 'btn_no';

        return $ret;
    }

    function ConfirmPlace($parg1, $parg2, $varg1, $varg2)
    {
        if ($varg1 == "btn_yes") {

            game::$instance->incStat(1, 'troops_played', $this->player_id);

            $explode_troop = explode("_", $parg1);
            $explode_base = explode("_", $parg2);

            $compteur_troop_sur_base = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location ='board' AND card_location_arg = '{$explode_base[2]}'", true));

            $nb_troops_hand = self::getUniqueValueFromDB("SELECT COUNT(card_id) FROM troop WHERE card_location = 'hand' AND card_type_arg = '{$this->player_id}'");

            $numbers_no_blocked = [];
            $troops_blocked = self::getObjectListFromDB("SELECT card_blocked FROM troop WHERE card_location = 'hand' AND card_type_arg = '{$this->player_id}' AND card_blocked != 0", true);
            for ($i = 1; $i <= $nb_troops_hand; $i++) {
                if (!in_array($i, $troops_blocked)) {
                    $numbers_no_blocked[] = $i;
                }
            }

            if (count($numbers_no_blocked) >= 1) {
                $valeur_max = max($numbers_no_blocked);
                self::DbQuery("UPDATE troop set card_blocked = card_blocked - 1 WHERE card_location = 'hand' AND card_type_arg = '{$this->player_id}' AND card_blocked > '{$valeur_max}'");
            }

            game::$instance->troop->moveCard($explode_troop[1], 'board', $explode_base[2]);
            self::DbQuery("UPDATE troop set card_ordre = $compteur_troop_sur_base + 1 WHERE card_id = '{$explode_troop[1]}'");

            $infos_troop = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_ordre ordre FROM troop WHERE card_id = '{$explode_troop[1]}'");

            $type1 = $infos_troop['type'];


            game::$instance->notifyAllPlayers(
                'moveTroop',
                clienttranslate('${player_name} places ${log1}'),
                array(

                    'base_id' => $parg2,
                    'ordre' => $compteur_troop_sur_base + 1,
                    'player_name' => $this->player_name,
                    'player_id' => $this->player_id,
                    'origine' => "hand",
                    'infos_troop' => $infos_troop,
                    'nb_troops_hand' => $nb_troops_hand,
                    'log1' => game::$instance->getLogsType($type1),
                    'numbers_no_blocked' => $numbers_no_blocked,
                )
            );


            $numero_base = $explode_base[2];
            $troop_id = $explode_troop[1];

            self::DbQuery("INSERT INTO checkbase (troop_id, base) VALUES ({$troop_id}, {$numero_base})");


            $win = game::$instance->testZoneAndStar($numero_base, $this->board_name);

            if ($win == 0) {
                if (in_array($numero_base, $this->opponent_start_base)) {
                    game::$instance->setGameStateValue("endgame", 1); // pour progression
                    $numtroop = intval($type1) % 10;
                    game::$instance->addPending($this->player_id, "FinGame1", 3, $numtroop);
                } else {
                    game::$instance->addPending($this->player_id, "VerifTroop", $troop_id, $numero_base);
                }
            }

            if ($win == 1) {
                game::$instance->setGameStateValue("endgame", 1); // pour progression
                game::$instance->addPending($this->player_id, "FinGame1", 2);
            }
        }

        if ($varg1 == "btn_no") {
            game::$instance->addPending($this->player_id, "NormalTurn");
        }
    }


    function argConfirmDraw($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} draws Troops');
        $ret['titleyou'] = clienttranslate('${you} must confirm');

        $ret["selected"][] = $this->player_deck_id;

        $ret['buttons'][] = 'btn_yes';
        $ret['buttons'][] = 'btn_no';


        return $ret;
    }

    function ConfirmDraw($parg1, $parg2, $varg1, $varg2)
    {

        if ($varg1 == "btn_yes") {

            $nb_troops_hand = self::getUniqueValueFromDB("SELECT COUNT(card_id) FROM troop WHERE card_location = 'hand' AND card_type_arg = '{$this->player_id}'");
            $old_troops = self::getObjectListFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_ordre ordre FROM troop WHERE card_location = 'hand' AND card_type_arg ='{$this->player_id}'");
            $counttroopdeck = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='{$this->player_deck}'", true));

            if (($parg1 == 'btn_draw_2') || (($parg1 == $this->player_deck_id) && ($nb_troops_hand <= 6) && ($counttroopdeck >= 2))) {
                game::$instance->incStat(2, 'troops_drawn', $this->player_id);

                $new_troops = game::$instance->troop->pickCardsForLocation(2, $this->player_deck, 'hand');




                $type1 = $this->player_color_number . $new_troops[0]['type'] % 10;
                $type2 = $this->player_color_number . $new_troops[1]['type'] % 10;



                game::$instance->notifyPlayer(
                    $this->player_id,
                    'drawTroopPrivate',
                    clienttranslate('${you} draw ${log1} ${log2}'),
                    array(
                        'you' =>    [
                            'log' => '<b style="color: #${color};">${you_name}</b>',
                            'args' => ['you_name' => clienttranslate('You'), 'color' => $this->player_color, 'i18n' => ['you_name']]
                        ],

                        'player_id' => $this->player_id,
                        'origine' => "deck",
                        'new_troops' => $new_troops,
                        'old_troops' => $old_troops,
                        'log1' => game::$instance->getLogsType($type1),
                        'log2' => game::$instance->getLogsType($type2),




                    )
                );

                $type0 = $this->player_color_number . "0";

                game::$instance->notifyAllPlayers(
                    'drawTroopPublic',
                    '',
                    array(
                        'player_name' => $this->player_name,
                        'player_id' => $this->player_id,
                        'origine' => "deck",
                        'nb_troops' => 2,
                        'nb_troops_hand' => $nb_troops_hand,



                    )
                );

                game::$instance->notifyAllPlayers(
                    'message_allplayers_without_player',
                    clienttranslate('${player_name} draws ${log0} ${log0}'),
                    array(
                        'player_name' => $this->player_name,
                        'player_id' => $this->player_id,
                        'log0' => game::$instance->getLogsType($type0),


                    )
                );
            }

            if (($parg1 == 'btn_draw_1') || (($parg1 == $this->player_deck_id) && ($nb_troops_hand == 7) && ($counttroopdeck >= 2)) || (($parg1 == $this->player_deck_id) && ($nb_troops_hand <= 7) && ($counttroopdeck == 1))) {
                game::$instance->incStat(1, 'troops_drawn', $this->player_id);

                $new_troops = game::$instance->troop->pickCardsForLocation(1, $this->player_deck, 'hand');

                $type1 = $this->player_color_number . $new_troops[0]['type'] % 10;

                game::$instance->notifyPlayer(
                    $this->player_id,
                    'drawTroopPrivate',
                    clienttranslate('${you} draw ${log1}'),
                    array(
                        'you' =>    [
                            'log' => '<b style="color: #${color};">${you_name}</b>',
                            'args' => ['you_name' => clienttranslate('You'), 'color' => $this->player_color, 'i18n' => ['you_name']]
                        ],
                        'player_id' => $this->player_id,
                        'origine' => "deck",
                        'new_troops' => $new_troops,
                        'log1' => game::$instance->getLogsType($type1),



                    )
                );

                $type0 = $this->player_color_number . "0";

                game::$instance->notifyAllPlayers(
                    'drawTroopPublic',
                    '',
                    array(
                        'player_name' => $this->player_name,
                        'player_id' => $this->player_id,
                        'origine' => "deck",
                        'nb_troops' => 1,
                        'nb_troops_hand' => $nb_troops_hand,
                        'log0' => game::$instance->getLogsType($type0),


                    )
                );

                game::$instance->notifyAllPlayers(
                    'message_allplayers_without_player',
                    clienttranslate('${player_name} draws ${log0}'),
                    array(
                        'player_name' => $this->player_name,
                        'player_id' => $this->player_id,
                        'log0' => game::$instance->getLogsType($type0),


                    )
                );
            }

            game::$instance->giveExtraTime($this->player_id);
            game::$instance->deblock_troops($this->player_id);
            game::$instance->updateNbTurns();
            game::$instance->addPendingFirst($this->player_id, "NormalTurn");
        }

        if ($varg1 == "btn_no") {
            game::$instance->addPending($this->player_id, "NormalTurn");
        }
    }



    /// FONCTION FIN DE GAME ////

    function argFinGame1($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('END OF GAME');
        $ret['titleyou'] = clienttranslate('END OF GAME');


        return $ret;
    }

    function FinGame1($parg1, $parg2, $varg1, $varg2)
    {



        if ($parg1 == "1") {

            game::$instance->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} can\'t play anymore'),  //NE PEUT PLUS JOUER (NI DRAW NI PLACE TROOP)
                array(
                    'player_name' => $this->player_name,

                )
            );

            $star_player = self::getUniqueValueFromDB("SELECT player_star FROM player WHERE player_id='{$this->player_id}'");
            $star_opponent = self::getUniqueValueFromDB("SELECT player_star FROM player WHERE player_id='{$this->player_id_opponent}'");

            if ($star_player > $star_opponent) {
                $victory = 1;
                $colorvictory = $this->player_color_text;
                $troop_victory = 0;

                self::DbQuery("UPDATE player set player_score = 1 WHERE player_id = '{$this->player_id}'");

                game::$instance->notifyAllPlayers(
                    'score',
                    '',
                    array(
                        'playerid' => $this->player_id,
                        'score' => 1,

                    )
                );
            } else {
                $victory = 1;
                $colorvictory = $this->opponent_color_text;
                $troop_victory = 0;

                self::DbQuery("UPDATE player set player_score = 1 WHERE player_id = '{$this->player_id_opponent}'");

                game::$instance->notifyAllPlayers(
                    'score',
                    '',
                    array(
                        'playerid' => $this->player_id_opponent,
                        'score' => 1,

                    )
                );
            }

            game::$instance->incStat(1, 'win_by_terrain');
            game::$instance->setStat(2, 'type_victory');
        }

        if ($parg1 == "2") {

            $star_player = self::getUniqueValueFromDB("SELECT player_star FROM player WHERE player_id='{$this->player_id}'");
            $star_opponent = self::getUniqueValueFromDB("SELECT player_star FROM player WHERE player_id='{$this->player_id_opponent}'");
            $max_medals = game::$instance->_medals_to_win[game::$instance->getGameStateValue('board')];

            if ($star_player >= $max_medals) {

                $victory = 1;
                $colorvictory = $this->player_color_text;
                $troop_victory = 0;

                game::$instance->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} has won the <b>${max_medals}</b> necessary medals'), //A ATTEINT L OBJECTIF MEDAILLE
                    array(
                        'player_name' => $this->player_name,
                        'max_medals' => $max_medals,

                    )
                );

                self::DbQuery("UPDATE player set player_score = 1 WHERE player_id = '{$this->player_id}'");

                game::$instance->notifyAllPlayers(
                    'score',
                    '',
                    array(
                        'playerid' => $this->player_id,
                        'score' => 1,

                    )
                );
            }

            if ($star_opponent >= $max_medals) {

                $victory = 1;
                $colorvictory = $this->opponent_color_text;
                $troop_victory = 0;

                game::$instance->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} has won the <b>${max_medals}</b> necessary medals'), //A ATTEINT L OBJECTIF MEDAILLE
                    array(
                        'player_name' => $this->player_name_opponent,
                        'max_medals' => $max_medals,

                    )
                );

                self::DbQuery("UPDATE player set player_score = 1 WHERE player_id = '{$this->player_id_opponent}'");

                game::$instance->notifyAllPlayers(
                    'score',
                    '',
                    array(
                        'playerid' => $this->player_id_opponent,
                        'score' => 1,

                    )
                );
            }



            game::$instance->incStat(1, 'win_by_terrain');
            game::$instance->setStat(2, 'type_victory');
        }

        if ($parg1 == "3") {

            $victory = 2;
            $colorvictory = $this->player_color_text;
            $troop_victory = $parg2;

            game::$instance->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} captured ${opponent}\'s starting base'), //A PRIS LA BASE ADVERSE
                array(
                    'opponent' =>    [
                        'log' => '<b style="color: #${color};">${opponent_name}</b>',
                        'args' => ['opponent_name' => $this->player_name_opponent, 'color' => $this->player_color_opponent]
                    ],
                    'player_name' => $this->player_name,

                )
            );

            self::DbQuery("UPDATE player set player_score = 1 WHERE player_id = '{$this->player_id}'");

            game::$instance->notifyAllPlayers(
                'score',
                '',
                array(
                    'playerid' => $this->player_id,
                    'score' => 1,

                )
            );

            game::$instance->incStat(1, 'win_by_hq');
            game::$instance->setStat(1, 'type_victory');
        }


        game::$instance->notifyAllPlayers(
            'victory',
            '',
            array(
                'typevictory' => $victory,
                'colorvictory' => $colorvictory,
                'troopvictory' => $troop_victory,

            )
        );

        if ($colorvictory == 'blue') {
            game::$instance->setStat(1, 'color_win');
        }

        if ($colorvictory == 'red') {
            game::$instance->setStat(2, 'color_win');
        }


        game::$instance->notifyAllPlayers('simplePause', '', ['time' => 1000]);

        game::$instance->gamestate->nextState('end');
    }
}
