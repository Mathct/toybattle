<?php

namespace Bga\Games\toybattle; // ATTENTION

trait TroopsTrait  // ATTENTION
{

    public function argVerifTroop($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} places a Troop');
        $ret['titleyou'] = clienttranslate('${you} place a Troop');

        return $ret;
    }

    public function VerifTroop($parg1, $parg2, $varg1, $varg2)
    {

        ///Annule l'effet de la troupe sur les bases speciales du board "station" (71)
        $numero_power = game::$instance->_bases[$this->board_name][$parg2]['power'];

        if (($numero_power != 71) || (game::$instance->gamestate->table_globals[100] == 2)) {

            $force_troop = self::getUniqueValueFromDB("SELECT card_type FROM troop WHERE card_id = '{$parg1}'") % 10;


            if ($force_troop == 1) {
                game::$instance->addPending($this->player_id, "Troop1_Step1", $parg2);
            }

            if ($force_troop == 2) {
                game::$instance->addPending($this->player_id, "Troop2_Step1", $parg2);
            }

            if ($force_troop == 3) {
                game::$instance->addPending($this->player_id, "Troop3_Step1", $parg2);
            }

            if ($force_troop == 4) {
                game::$instance->addPending($this->player_id, "VerifBase");
            }

            if ($force_troop == 5) {
                game::$instance->addPending($this->player_id, "Troop5_Step1", $parg2);
            }

            if ($force_troop == 6) {
                game::$instance->addPending($this->player_id, "Troop6_Step1", $parg2);
            }

            if ($force_troop >= 7) {
                game::$instance->addPending($this->player_id, "VerifBase");
            }
        } else {
            game::$instance->addPending($this->player_id, "VerifBase");
        }
    }

    ///////////////////////////
    ///////// TROOP 1 /////////
    ///////////////////////////

    public function argTroop1_Step1($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} places a Troop');

        if ($this->player_color_title == 'blue') {
            $ret['icon'] = '<span class="icon_bandeau icon_troop_1 icon_blue"></span>';
        }

        if ($this->player_color_title == 'red') {
            $ret['icon'] = '<span class="icon_bandeau icon_troop_1 icon_red"></span>';
        }



        $counttroopdeck = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='{$this->player_deck}'", true));
        $counttroophand = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='hand' AND card_type_arg = '{$this->player_id}'", true));

        if (($counttroopdeck >= 2) && ($counttroophand <= 6)) {
            $ret['titleyou'] = clienttranslate('#icon# can draw 2 Troops');
            $ret["selectable"][] = $this->player_deck_id;
            $ret['buttons'][] = 'btn_draw_2';
            $ret['buttons'][] = 'btn_pass';
        }

        if ((($counttroopdeck == 1) && ($counttroophand <= 7)) || (($counttroopdeck >= 1) && ($counttroophand == 7))) {
            $ret['titleyou'] = clienttranslate('#icon# can draw 1 Troop');
            $ret["selectable"][] = $this->player_deck_id;
            $ret['buttons'][] = 'btn_draw_1';
            $ret['buttons'][] = 'btn_pass';
        }


        if (($counttroopdeck == 0) || ($counttroophand == 8)) {
            $ret['titleyou'] = clienttranslate('#icon# cannot draw Troops');
            $ret['buttons'][] = 'btn_continue';
        }


        return $ret;
    }

    public function Troop1_Step1($parg1, $parg2, $varg1, $varg2)
    {
        if (($varg1 == "btn_draw_1") || ($varg1 == "btn_draw_2") || ($varg1 == $this->player_deck_id)) {

            $nb_troops_hand = self::getUniqueValueFromDB("SELECT COUNT(card_id) FROM troop WHERE card_location = 'hand' AND card_type_arg = '{$this->player_id}'");
            $old_troops = self::getObjectListFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_ordre ordre FROM troop WHERE card_location = 'hand' AND card_type_arg ='{$this->player_id}'");
            $counttroopdeck = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='{$this->player_deck}'", true));

            if (($varg1 == 'btn_draw_2') || (($varg1 == $this->player_deck_id) && ($nb_troops_hand <= 6) && ($counttroopdeck >= 2))) {
                game::$instance->incStat(2, 'troops_drawn', $this->player_id);
                game::$instance->incStat(1, 'skully_activated', $this->player_id);

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
                        'log0' => game::$instance->getLogsType($type0),


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
                game::$instance->incStat(1, 'skully_activated', $this->player_id);

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
        }


        game::$instance->addPending($this->player_id, "VerifBase");
    }

    ///////////////////////////
    ///////// TROOP 2 /////////
    ///////////////////////////


    public function argTroop2_Step1($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} places a Troop');

        if ($this->player_color_title == 'blue') {
            $ret['icon'] = '<span class="icon_bandeau icon_troop_2 icon_blue"></span>';
        }

        if ($this->player_color_title == 'red') {
            $ret['icon'] = '<span class="icon_bandeau icon_troop_2 icon_red"></span>';
        }

        // TEST SI TROUPES NON BLOQUEES PEUVENT ETRE PLACEES

        $counttroophand_noblocked = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='hand' AND card_type_arg = '{$this->player_id}' AND card_blocked = 0", true));

        if ($counttroophand_noblocked >= 1) {
            $place_ok = 0;
            $list_troop = self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='hand' AND card_type_arg = '{$this->player_id}' AND card_blocked = 0", true);
            foreach ($list_troop as $troop) {
                $troop_id = 'troop_' . $troop;
                $possible_base = game::$instance->getPossibleBase($this->start_base, $troop_id, $this->player_id);
                if (count($possible_base) >= 1) {
                    $place_ok = 1;
                }
            }

            if ($place_ok == 1) {
                $ret['titleyou'] = clienttranslate('#icon# can place another Troop');
                $ret['buttons'][] = 'btn_place_troop';
                $ret['buttons'][] = 'btn_pass';

                $list_troop = self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='hand' AND card_type_arg = '{$this->player_id}'  AND card_blocked = 0", true);
                foreach ($list_troop as $troop) {
                    $troop_id = 'troop_' . $troop;
                    $possible_base = game::$instance->getPossibleBase($this->start_base, $troop_id, $this->player_id);
                    if (count($possible_base) >= 1) {
                        $ret["selectable"][] = $troop_id;
                    }
                }
            }
        } else {
            $ret['titleyou'] = clienttranslate('#icon# cannot place another Troop');
            $ret['buttons'][] = 'btn_continue';
        }





        return $ret;
    }

    public function Troop2_Step1($parg1, $parg2, $varg1, $varg2)
    {

        if ($varg1 == 'btn_place_troop') {

            game::$instance->addPending($this->player_id, "Troop2_Step2", $parg1);
        } elseif (strpos($varg1, 'troop') === 0) {
            game::$instance->addPending($this->player_id, "Troop2_Step3", $varg1, $parg1);
        } else {

            game::$instance->addPending($this->player_id, "VerifBase");
        }
    }

    function argTroop2_Step2($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} places a Troop');

        if ($this->player_color_title == 'blue') {
            $ret['icon'] = '<span class="icon_bandeau icon_troop_2 icon_blue"></span>';
        }

        if ($this->player_color_title == 'red') {
            $ret['icon'] = '<span class="icon_bandeau icon_troop_2 icon_red"></span>';
        }


        $ret['titleyou'] = clienttranslate('#icon# must choose a Troop');

        $list_troop = self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='hand' AND card_type_arg = '{$this->player_id}'  AND card_blocked = 0", true);
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

    function Troop2_Step2($parg1, $parg2, $varg1, $varg2)
    {
        if ($varg1 == "btn_cancel") {
            game::$instance->addPending($this->player_id, "Troop2_Step1", $parg1);
        } else {
            game::$instance->addPending($this->player_id, "Troop2_Step3", $varg1, $parg1);
        }
    }

    function argTroop2_Step3($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} places a Troop');

        if ($this->player_color_title == 'blue') {
            $ret['icon'] = '<span class="icon_bandeau icon_troop_2 icon_blue"></span>';
        }

        if ($this->player_color_title == 'red') {
            $ret['icon'] = '<span class="icon_bandeau icon_troop_2 icon_red"></span>';
        }


        $ret["selected"][] = $parg1;

        $tableau_boards_name = ["castle", "pool", "clouds", "jungle", "cemetery", "carribean", "station", "battlefield", "christmas"];
        $board_name = $tableau_boards_name[game::$instance->getGameStateValue('board') - 1];


        $possible_base = game::$instance->getPossibleBase($this->start_base, $parg1, $this->player_id);

        if (count($possible_base) >= 1) {
            $ret['titleyou'] = clienttranslate('#icon# must choose a base');
        } else {
            $ret['titleyou'] = clienttranslate('#icon# cannot place this Troop');
        }

        foreach ($possible_base as $base) {

            $ret["selectable"][] = "base_" . $board_name . "_" . $base;
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

    function Troop2_Step3($parg1, $parg2, $varg1, $varg2)
    {

        if ($varg1 == "btn_cancel") {
            game::$instance->addPending($this->player_id, "Troop2_Step1", $parg2);
        } elseif (strpos($varg1, 'troop') === 0) {
            game::$instance->addPending($this->player_id, "Troop2_Step3", $varg1, $parg1);
        } else {

            if ($this->player_pref_confirm == 1) {

                $couple_troop_base_selected = $parg1 . "_" . $varg1;
                game::$instance->addPending($this->player_id, "Troop2_Confirm", $couple_troop_base_selected, $parg2);
            }

            if ($this->player_pref_confirm == 2) {

                game::$instance->incStat(1, 'troops_played', $this->player_id);
                game::$instance->incStat(1, 'capn_activated', $this->player_id);

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

    function argTroop2_Confirm($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} places a Troop');

        if ($this->player_color_title == 'blue') {
            $ret['icon'] = '<span class="icon_bandeau icon_troop_2 icon_blue"></span>';
        }

        if ($this->player_color_title == 'red') {
            $ret['icon'] = '<span class="icon_bandeau icon_troop_2 icon_red"></span>';
        }

        $ret['titleyou'] = clienttranslate('#icon# must confirm');

        $explode = explode("_", $parg1);
        $ret["selected"][] = "troop_" . $explode[1];
        $ret["selected"][] = "base_" . $explode[3] . "_" . $explode[4];


        $ret['buttons'][] = 'btn_yes';
        $ret['buttons'][] = 'btn_no';

        return $ret;
    }

    function Troop2_Confirm($parg1, $parg2, $varg1, $varg2)
    {
        if ($varg1 == "btn_yes") {
            game::$instance->incStat(1, 'troops_played', $this->player_id);
            game::$instance->incStat(1, 'capn_activated', $this->player_id);

            $explode = explode("_", $parg1);

            $compteur_troop_sur_base = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location ='board' AND card_location_arg = '{$explode[4]}'", true));

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

            game::$instance->troop->moveCard($explode[1], 'board', $explode[4]);
            self::DbQuery("UPDATE troop set card_ordre = $compteur_troop_sur_base + 1 WHERE card_id = '{$explode[1]}'");

            $infos_troop = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_ordre ordre FROM troop WHERE card_id = '{$explode[1]}'");


            $type1 = $infos_troop['type'];


            game::$instance->notifyAllPlayers(
                'moveTroop',
                clienttranslate('${player_name} places ${log1}'),
                array(

                    'base_id' => 'base_' . $explode[3] . '_' . $explode[4],
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





            $numero_base = $explode[4];
            $troop_id = $explode[1];

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
            game::$instance->addPending($this->player_id, "Troop2_Step1", $parg2);
        }
    }




    ///////////////////////////
    ///////// TROOP 3 /////////
    ///////////////////////////


    public function argTroop3_Step1($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} places a Troop');

        if ($this->player_color_title == 'blue') {
            $ret['icon'] = '<span class="icon_bandeau icon_troop_3 icon_blue"></span>';
        }

        if ($this->player_color_title == 'red') {
            $ret['icon'] = '<span class="icon_bandeau icon_troop_3 icon_red"></span>';
        }


        $base_mastok = $parg1;

        $list_base_discard_possible = [];
        $bases_adjacentes_mastok = game::$instance->_bases[$this->board_name][$base_mastok]['adjacents'];

        foreach ($bases_adjacentes_mastok as $base) {
            $infos_troopmax = self::getObjectListFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location_arg location_arg, card_ordre ordre FROM troop WHERE card_location = 'board' AND card_location_arg = '{$base}' AND card_ordre = (SELECT MAX(card_ordre) FROM troop WHERE card_location = 'board' AND card_location_arg = '{$base}')");
            if (count($infos_troopmax) != 0) {

                if ($infos_troopmax[0]['type_arg'] != $this->player_id) {
                    $list_base_discard_possible[] = $infos_troopmax[0]['location_arg'];
                }
            }
        }

        if (count($list_base_discard_possible) == 0) {
            $ret['titleyou'] = clienttranslate('#icon# cannot discard an adjacent Troop');
            $ret['buttons'][] = 'btn_continue';
        } else {
            $ret['titleyou'] = clienttranslate('#icon# can discard an adjacent Troop');

            foreach ($list_base_discard_possible as $base_discard) {
                $ret["selectable"][] = "base_" . $this->board_name . "_" . $base_discard;
            }


            $ret['buttons'][] = 'btn_pass';
        }



        return $ret;
    }

    public function Troop3_Step1($parg1, $parg2, $varg1, $varg2)
    {

        if (($varg1 == 'btn_continue') || ($varg1 == 'btn_pass')) {
            game::$instance->addPending($this->player_id, "VerifBase");
        } else {
            if ($this->player_pref_confirm == 1) {
                game::$instance->addPending($this->player_id, "Troop3_Confirm", $varg1, $parg1);
            }

            if ($this->player_pref_confirm == 2) {

                game::$instance->incStat(1, 'jumbo_activated', $this->player_id);

                $explode = explode("_", $varg1);
                $infos_troop = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_ordre ordre FROM troop WHERE card_location = 'board' AND card_location_arg = '{$explode[2]}' AND card_ordre = (SELECT MAX(card_ordre) FROM troop WHERE card_location = 'board' AND card_location_arg = '{$explode[2]}')");

                game::$instance->troop->moveCard($infos_troop['id'], 'discard', 0);

                self::DbQuery("UPDATE troop set card_ordre = 1 WHERE card_id = '{$infos_troop['id']}'");

                $type1 = $infos_troop['type'];

                game::$instance->notifyAllPlayers(
                    'discardTroopFromBoard',
                    clienttranslate('${player_name} discards ${log1} from the Terrain'),
                    array(

                        'player_name' => $this->player_name,
                        'infos_troop' => $infos_troop,
                        'log1' => game::$instance->getLogsType($type1),

                    )
                );

                $win = game::$instance->testZoneAndStar($infos_troop['location_arg'], $this->board_name);

                if ($win == 0) {
                    game::$instance->addPending($this->player_id, "VerifBase");
                }

                if ($win == 1) {
                    game::$instance->setGameStateValue("endgame", 1); // pour progression
                    game::$instance->addPending($this->player_id, "FinGame1", 2);
                }
            }
        }
    }

    public function argTroop3_Confirm($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} places a Troop');

        if ($this->player_color_title == 'blue') {
            $ret['icon'] = '<span class="icon_bandeau icon_troop_3 icon_blue"></span>';
        }

        if ($this->player_color_title == 'red') {
            $ret['icon'] = '<span class="icon_bandeau icon_troop_3 icon_red"></span>';
        }

        $ret['titleyou'] = clienttranslate('#icon# must confirm');

        $ret["selected"][] = $parg1;

        $ret['buttons'][] = 'btn_yes';
        $ret['buttons'][] = 'btn_no';





        return $ret;
    }

    public function Troop3_Confirm($parg1, $parg2, $varg1, $varg2)
    {

        if ($varg1 == 'btn_no') {
            game::$instance->addPending($this->player_id, "Troop3_Step1", $parg2);
        }

        if ($varg1 == 'btn_yes') {

            game::$instance->incStat(1, 'jumbo_activated', $this->player_id);

            $explode = explode("_", $parg1);
            $infos_troop = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_ordre ordre FROM troop WHERE card_location = 'board' AND card_location_arg = '{$explode[2]}' AND card_ordre = (SELECT MAX(card_ordre) FROM troop WHERE card_location = 'board' AND card_location_arg = '{$explode[2]}')");

            game::$instance->troop->moveCard($infos_troop['id'], 'discard', 0);

            self::DbQuery("UPDATE troop set card_ordre = 1 WHERE card_id = '{$infos_troop['id']}'");

            $type1 = $infos_troop['type'];

            game::$instance->notifyAllPlayers(
                'discardTroopFromBoard',
                clienttranslate('${player_name} discards ${log1} from the Terrain'),
                array(

                    'player_name' => $this->player_name,
                    'infos_troop' => $infos_troop,
                    'log1' => game::$instance->getLogsType($type1),

                )
            );


            $win = game::$instance->testZoneAndStar($infos_troop['location_arg'], $this->board_name);

            if ($win == 0) {
                game::$instance->addPending($this->player_id, "VerifBase");
            }

            if ($win == 1) {
                game::$instance->setGameStateValue("endgame", 1); // pour progression
                game::$instance->addPending($this->player_id, "FinGame1", 2);
            }
        }
    }

    ///////////////////////////
    ///////// TROOP 5 /////////
    ///////////////////////////


    public function argTroop5_Step1($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} places a Troop');

        if ($this->player_color_title == 'blue') {
            $ret['icon'] = '<span class="icon_bandeau icon_troop_5 icon_blue"></span>';
        }

        if ($this->player_color_title == 'red') {
            $ret['icon'] = '<span class="icon_bandeau icon_troop_5 icon_red"></span>';
        }

        $troop_id_opponent_hand = self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location = 'hand' AND card_type_arg != '{$this->player_id}'", true);
        $count = count($troop_id_opponent_hand);

        $ret['opponent'] = '<span style="color: #' . $this->player_color_opponent . ';">' . $this->player_name_opponent . '</span>';

        if ($count >= 1) {
            $ret['titleyou'] = clienttranslate('#icon# can discard a Troop from the #opponent#\'s rack');

            if ($this->player_pref_discard_block == 2) {
                $ret['buttons'][] = 'btn_discard';
            }
            $ret['buttons'][] = 'btn_pass';

            if ($this->player_pref_discard_block == 1) {
                $troop_id_opponent_hand = self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location = 'hand' AND card_type_arg != '{$this->player_id}'", true);
                $count = count($troop_id_opponent_hand);

                for ($i = 1; $i <= $count; $i++) {
                    if ($this->player_color_title == 'blue') {
                        $ret["selectable"][] = 'red_troop_' . $i;
                    }

                    if ($this->player_color_title == 'red') {
                        $ret["selectable"][] = 'blue_troop_' . $i;
                    }
                }
            }
        }

        if ($count == 0) {
            $ret['titleyou'] = clienttranslate('#icon# cannot discard a Troop from the #opponent#\'s rack');

            $ret['buttons'][] = 'btn_continue';
        }





        return $ret;
    }

    public function Troop5_Step1($parg1, $parg2, $varg1, $varg2)
    {

        if (($varg1 == 'btn_pass') || ($varg1 == 'btn_continue')) {
            game::$instance->addPending($this->player_id, "VerifBase");
        }

        if ((strpos($varg1, 'red_troop') === 0) || (strpos($varg1, 'blue_troop') === 0)) {
            game::$instance->incStat(1, 'xb42_activated', $this->player_id);

            $troop_id_opponent_hand = self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location = 'hand' AND card_type_arg != '{$this->player_id}'", true);
            $count = count($troop_id_opponent_hand);
            $rand = bga_rand(1, $count);
            $rand_troop_id = $troop_id_opponent_hand[$rand - 1];

            $infos_troop = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_ordre ordre FROM troop WHERE card_id = '{$rand_troop_id}'");

            $explode = explode("_", $varg1);
            $selected_troop = $explode[2];

            game::$instance->troop->moveCard($rand_troop_id, 'discard', 0);

            $type1 = $infos_troop['type'];

            game::$instance->notifyAllPlayers(
                'discardTroopFromHand',
                clienttranslate('${player_name} discards ${log1} from the ${opponent}\'s rack'),
                array(
                    'opponent' =>    [
                        'log' => '<b style="color: #${color};">${opponent_name}</b>',
                        'args' => ['opponent_name' => $this->player_name_opponent, 'color' => $this->player_color_opponent]
                    ],

                    'player_name' => $this->player_name,
                    'infos_troop' => $infos_troop, // info_troop a discard avant discard
                    'selected_troop' => $selected_troop, // 0 si le joueur n'a pas choisi... de 1 à 8 si le joueur a choisi lui même
                    'nb_cards_in_hand' => $count,
                    'log1' => game::$instance->getLogsType($type1),

                )
            );

            game::$instance->addPending($this->player_id, "VerifBase");
        }

        if ($varg1 == 'btn_discard') {

            game::$instance->incStat(1, 'xb42_activated', $this->player_id);

            $troop_id_opponent_hand = self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location = 'hand' AND card_type_arg != '{$this->player_id}'", true);
            $count = count($troop_id_opponent_hand);
            $rand = bga_rand(1, $count);
            $rand_troop_id = $troop_id_opponent_hand[$rand - 1];

            $infos_troop = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_ordre ordre FROM troop WHERE card_id = '{$rand_troop_id}'");

            $selected_troop = 0;

            game::$instance->troop->moveCard($rand_troop_id, 'discard', 0);

            $type1 = $infos_troop['type'];

            game::$instance->notifyAllPlayers(
                'discardTroopFromHand',
                clienttranslate('${player_name} discards ${log1} from the ${opponent}\'s rack'),
                array(
                    'opponent' =>    [
                        'log' => '<b style="color: #${color};">${opponent_name}</b>',
                        'args' => ['opponent_name' => $this->player_name_opponent, 'color' => $this->player_color_opponent]
                    ],

                    'player_name' => $this->player_name,
                    'infos_troop' => $infos_troop, // info_troop a discard avant discard
                    'selected_troop' => $selected_troop, // 0 si le joueur n'a pas choisi... de 1 à 8 si le joueur a choisi lui même
                    'nb_cards_in_hand' => $count,
                    'log1' => game::$instance->getLogsType($type1),

                )
            );


            game::$instance->addPending($this->player_id, "VerifBase");
        }
    }


    public function argTroop5_Step2($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} places a Troop');

        if ($this->player_color_title == 'blue') {
            $ret['icon'] = '<span class="icon_bandeau icon_troop_5 icon_blue"></span>';
        }

        if ($this->player_color_title == 'red') {
            $ret['icon'] = '<span class="icon_bandeau icon_troop_5 icon_red"></span>';
        }

        $ret['titleyou'] = clienttranslate('#icon# chooses a Troop to discard');

        $troop_id_opponent_hand = self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location = 'hand' AND card_type_arg != '{$this->player_id}'", true);
        $count = count($troop_id_opponent_hand);

        for ($i = 1; $i <= $count; $i++) {
            if ($this->player_color_title == 'blue') {
                $ret["selectable"][] = 'red_troop_' . $i;
            }

            if ($this->player_color_title == 'red') {
                $ret["selectable"][] = 'blue_troop_' . $i;
            }
        }


        $ret['buttons'][] = 'btn_cancel';



        return $ret;
    }

    public function Troop5_Step2($parg1, $parg2, $varg1, $varg2)
    {

        if ($varg1 == 'btn_cancel') {
            game::$instance->addPending($this->player_id, "Troop5_Step1", $parg1);
        } else {

            game::$instance->incStat(1, 'xb42_activated', $this->player_id);

            $troop_id_opponent_hand = self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location = 'hand' AND card_type_arg != '{$this->player_id}'", true);
            $count = count($troop_id_opponent_hand);
            $rand = bga_rand(1, $count);
            $rand_troop_id = $troop_id_opponent_hand[$rand - 1];

            $infos_troop = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_ordre ordre FROM troop WHERE card_id = '{$rand_troop_id}'");

            $explode = explode("_", $varg1);
            $selected_troop = $explode[2];

            game::$instance->troop->moveCard($rand_troop_id, 'discard', 0);

            $type1 = $infos_troop['type'];

            game::$instance->notifyAllPlayers(
                'discardTroopFromHand',
                clienttranslate('${player_name} discards ${log1} from the ${opponent}\'s rack'),
                array(
                    'opponent' =>    [
                        'log' => '<b style="color: #${color};">${opponent_name}</b>',
                        'args' => ['opponent_name' => $this->player_name_opponent, 'color' => $this->player_color_opponent]
                    ],

                    'player_name' => $this->player_name,
                    'infos_troop' => $infos_troop, // info_troop a discard avant discard
                    'selected_troop' => $selected_troop, // 0 si le joueur n'a pas choisi... de 1 à 8 si le joueur a choisi lui même
                    'nb_cards_in_hand' => $count,
                    'log1' => game::$instance->getLogsType($type1),

                )
            );

            game::$instance->addPending($this->player_id, "VerifBase");
        }
    }


    ///////////////////////////
    ///////// TROOP 6 /////////
    ///////////////////////////


    public function argTroop6_Step1($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} places a Troop');

        if ($this->player_color_title == 'blue') {
            $ret['icon'] = '<span class="icon_bandeau icon_troop_6 icon_blue"></span>';
        }

        if ($this->player_color_title == 'red') {
            $ret['icon'] = '<span class="icon_bandeau icon_troop_6 icon_red"></span>';
        }

        $counttroopdeck = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='{$this->player_deck}'", true));
        $counttroophand = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='hand' AND card_type_arg = '{$this->player_id}'", true));


        if (($counttroopdeck >= 1) && ($counttroophand <= 7)) {
            $ret['titleyou'] = clienttranslate('#icon# can draw 1 Troop');
            $ret["selectable"][] = $this->player_deck_id;
            $ret['buttons'][] = 'btn_draw_1';
            $ret['buttons'][] = 'btn_pass';
        }


        if (($counttroopdeck == 0) || ($counttroophand == 8)) {
            $ret['titleyou'] = clienttranslate('#icon# cannot draw Troops');
            $ret['buttons'][] = 'btn_continue';
        }


        return $ret;
    }

    public function Troop6_Step1($parg1, $parg2, $varg1, $varg2)
    {
        if (($varg1 == "btn_draw_1") || ($varg1 == $this->player_deck_id)) {

            game::$instance->incStat(1, 'troops_drawn', $this->player_id);
            game::$instance->incStat(1, 'star_activated', $this->player_id);

            $nb_troops_hand = self::getUniqueValueFromDB("SELECT COUNT(card_id) FROM troop WHERE card_location = 'hand' AND card_type_arg = '{$this->player_id}'");
            $old_troops = self::getObjectListFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_ordre ordre FROM troop WHERE card_location = 'hand' AND card_type_arg ='{$this->player_id}'");

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


        game::$instance->addPending($this->player_id, "VerifBase");
    }
}
