<?php

namespace Bga\Games\toybattle; // ATTENTION

trait BasesTrait  // ATTENTION
{

    public function argVerifBase($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer}');
        $ret['titleyou'] = clienttranslate('${you}');

        return $ret;
    }

    public function VerifBase($parg1, $parg2, $varg1, $varg2)
    {

        // MODE AVEC BASES SPECIALES ET SANS POOL NI STATION 
        if ((game::$instance->gamestate->table_globals[100] == 1) && ($this->board_name != 'pool') && ($this->board_name != 'station') && ($this->board_name != 'carribean')) {

            // On recupere les bases contrôlées dans l'ORDRE à vérifier
            $check = self::getObjectListFromDB("SELECT id id, troop_id troop_id, base base FROM checkbase ORDER BY id ASC LIMIT 1");

            //Si y a plus de bases à contrôler.. fin tour
            if ($check == null) {
                game::$instance->giveExtraTime($this->player_id);
                game::$instance->deblock_troops($this->player_id);
                game::$instance->addPendingFirst($this->player_id, "NormalTurn");
            } else {
                //Sinon on recupere le pouvoir de la PREMIERE base à contrôler
                $numero_power = game::$instance->_bases[$this->board_name][$check[0]['base']]['power'];
                // si c'est une base spéciale on va sur la fonction
                if ($numero_power != 0) {
                    self::DbQuery("DELETE FROM checkbase WHERE id = '{$check[0]['id']}'");

                    $troop_id = $check[0]['troop_id'];
                    $base = $check[0]['base'];

                    /////// EN FONCTION DU BOARD //////

                    if ($numero_power == 11) // CASTLE
                    {
                        game::$instance->addPending($this->player_id, "Base11_Step1", $troop_id, $base);
                    } elseif ($numero_power == 31) // CLOUDS
                    {
                        game::$instance->addPending($this->player_id, "Base31_Step1", $base);
                    } elseif ($numero_power == 41) // JUNGLE
                    {
                        game::$instance->addPending($this->player_id, "Base41_Step1", $base);
                    } elseif ($numero_power == 51) // CIMETIERE
                    {
                        game::$instance->addPending($this->player_id, "Base51_Step1", $base);
                    } elseif ($numero_power == 81) // BATTLE
                    {
                        game::$instance->addPending($this->player_id, "Base81_Step1", $base);
                    } else {
                        game::$instance->addPending($this->player_id, "VerifBase");
                    }
                } else {
                    // sinon on supprime la ligne et on va recontrôler celles qui suivent
                    self::DbQuery("DELETE FROM checkbase WHERE id = '{$check[0]['id']}'");
                    game::$instance->addPending($this->player_id, "VerifBase");
                }
            }
        }

        // MODE SANS BASES SPECIALES OU AVEC POOL OU STATION 
        if ((game::$instance->gamestate->table_globals[100] == 2) || ($this->board_name == 'pool') || ($this->board_name == 'station')) {

            self::DbQuery("DELETE FROM `checkbase`;");
            game::$instance->giveExtraTime($this->player_id);
            game::$instance->deblock_troops($this->player_id);
            game::$instance->addPendingFirst($this->player_id, "NormalTurn");
        }
    }


    /////////// BASE 11 /////////

    public function argBase11_Step1($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} activates a special base');

        $ret['opponent'] = '<span style="color: #' . $this->player_color_opponent . ';">' . $this->player_name_opponent . '</span>';
        
        $ret['icon'] = '<span class="icon_power_bandeau icon_powerbase_1"></span>';

        $ret["selected"][] = 'base_' . $this->board_name . '_' . $parg2;

        $test = 0;
        $all_bases = game::$instance->_bases[$this->board_name];
        $all_bases_a_checker = array_map('strval', array_keys($all_bases));
        $all_bases_sans_QG = [];

        $counttroophand = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='hand' AND card_type_arg = '{$this->player_id}'", true));

        if ($counttroophand < 8) {
            foreach ($all_bases_a_checker as $allbase) {
                if (($allbase >= 10) && ($allbase <= 40) && ($allbase != $parg2))  // ATTENTION !!!!! j'enleve aussi la base déclenchée pour le moment
                {
                    $all_bases_sans_QG[] = $allbase;
                }
            }

            if (count($all_bases_sans_QG) != 0) {

                foreach ($all_bases_sans_QG as $base_sans_QG) {
                    $count_troop_on_base = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location = 'board' AND card_location_arg = '{$base_sans_QG}'", true));
                    if ($count_troop_on_base >= 1) {

                        $infos_troopmax = self::getObjectListFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_ordre ordre FROM troop WHERE card_location = 'board' AND card_location_arg = '{$base_sans_QG}' AND card_ordre = (SELECT MAX(card_ordre) FROM troop WHERE card_location = 'board' AND card_location_arg = '{$base_sans_QG}')");

                        if ($infos_troopmax[0]['type_arg'] == $this->player_id) // si elle appartient au joueur actif
                        {
                            $ret["selectable"][] = 'base_' . $this->board_name . '_' . $base_sans_QG;
                            $test = 1;
                        }
                    }
                }
            }
        }



        if ($test == 1) {
            $ret['titleyou'] = clienttranslate('#icon# ${you} can recover an other Troop on the Terrain (Be careful! This can trigger a region control for #opponent#)');
            $ret['buttons'][] = 'btn_pass';
        }

        if ($test == 0) {
            $ret['titleyou'] = clienttranslate('#icon# ${you} cannot place back a Troop on your rack');
            $ret['buttons'][] = 'btn_continue';
        }

        
        return $ret;
    }

    public function Base11_Step1($parg1, $parg2, $varg1, $varg2)
    {
        if (($varg1 == "btn_pass")||($varg1 == "btn_continue")) {
            game::$instance->addPending($this->player_id, "VerifBase");
        }

        else {

            if ($this->player_pref_confirm == 1) {
                $duo_check = $parg1 . '_' . $parg2;
                game::$instance->addPending($this->player_id, "Base11_Confirm", $varg1, $duo_check);
            }

            if ($this->player_pref_confirm == 2) {


                $explode = explode("_", $varg1);

                $nb_troops_hand = self::getUniqueValueFromDB("SELECT COUNT(card_id) FROM troop WHERE card_location = 'hand' AND card_type_arg = '{$this->player_id}'");

                $infos_troopmax = self::getObjectListFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_ordre ordre FROM troop WHERE card_location = 'board' AND card_location_arg = '{$explode[2]}' AND card_ordre = (SELECT MAX(card_ordre) FROM troop WHERE card_location = 'board' AND card_location_arg = '{$explode[2]}')");

                game::$instance->troop->moveCard($infos_troopmax[0]['id'], 'hand', 0);
                self::DbQuery("UPDATE troop set card_ordre = 1 WHERE card_id = '{$infos_troopmax[0]['id']}'");

                self::DbQuery("DELETE FROM checkbase WHERE troop_id = '{$infos_troopmax[0]['id']}'");

                $type1 = $infos_troopmax[0]['type'];

                game::$instance->notifyAllPlayers(
                    'recoverTroopFromBoard',
                    clienttranslate('${player_name} places back ${log1} from Terrain to rack'),
                    array(

                        'player_name' => $this->player_name,
                        'infos_troop' => $infos_troopmax[0],
                        'nb_troops_hand' => $nb_troops_hand,
                        'log1' => game::$instance->getLogsType($type1),

                    )
                );


                ///// ATTENTION SI LE JOUEUR ENLEVE UNE TROOP SUR UNE BASE 
                $win = game::$instance->testZoneAndStar($infos_troopmax[0]['location_arg'], $this->board_name);

                if ($win == 0) {
                    game::$instance->addPending($this->player_id, "VerifBase");
                }

                if ($win == 1) {
                    game::$instance->addPending($this->player_id, "FinGame1", 2);
                }
            }
        }
    }

    
    function argBase11_Confirm($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} places a Troop');

        $ret['icon'] = '<span class="icon_power_bandeau icon_powerbase_1"></span>';

        $ret['titleyou'] = clienttranslate('#icon# ${you} must confirm');

        $ret["selected"][] = $parg1;
        $ret['buttons'][] = 'btn_yes';
        $ret['buttons'][] = 'btn_no';

        return $ret;
    }

    function Base11_Confirm($parg1, $parg2, $varg1, $varg2)
    {
        if ($varg1 == "btn_yes") {


            $explode = explode("_", $parg1);

            $nb_troops_hand = self::getUniqueValueFromDB("SELECT COUNT(card_id) FROM troop WHERE card_location = 'hand' AND card_type_arg = '{$this->player_id}'");

            $infos_troopmax = self::getObjectListFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_ordre ordre FROM troop WHERE card_location = 'board' AND card_location_arg = '{$explode[2]}' AND card_ordre = (SELECT MAX(card_ordre) FROM troop WHERE card_location = 'board' AND card_location_arg = '{$explode[2]}')");

            game::$instance->troop->moveCard($infos_troopmax[0]['id'], 'hand', 0);
            self::DbQuery("UPDATE troop set card_ordre = 1 WHERE card_id = '{$infos_troopmax[0]['id']}'");

            self::DbQuery("DELETE FROM checkbase WHERE troop_id = '{$infos_troopmax[0]['id']}'");

            $type1 = $infos_troopmax[0]['type'];

            game::$instance->notifyAllPlayers(
                'recoverTroopFromBoard',
                clienttranslate('${player_name} places back ${log1} from Terrain to rack'),
                array(

                    'player_name' => $this->player_name,
                    'infos_troop' => $infos_troopmax[0],
                    'nb_troops_hand' => $nb_troops_hand,
                    'log1' => game::$instance->getLogsType($type1),

                )
            );

            ///// ATTENTION SI LE JOUEUR ENLEVE UNE TROOP SUR UNE BASE 
            $win = game::$instance->testZoneAndStar($infos_troopmax[0]['location_arg'], $this->board_name);

            if ($win == 0) {
                game::$instance->addPending($this->player_id, "VerifBase");
            }

            if ($win == 1) {
                game::$instance->addPending($this->player_id, "FinGame1", 2);
            }
        }

        if ($varg1 == "btn_no") {

            $explode = explode("_", $parg2);
            game::$instance->addPending($this->player_id, "Base11_Step1", $explode[0], $explode[1]);
        }
    }


    /////////// BASE 31 /////////

    public function argBase31_Step1($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} activates a special base');

        $ret['icon'] = '<span class="icon_power_bandeau icon_powerbase_3"></span>';

        $ret["selected"][] = 'base_' . $this->board_name . '_' . $parg1;

        $counttroopdeck = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='{$this->player_deck}'", true));
        $counttroophand = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='hand' AND card_type_arg = '{$this->player_id}'", true));


        if (($counttroopdeck >= 1) && ($counttroophand <= 7)) {
            $ret['titleyou'] = clienttranslate('#icon# ${you} can draw 1 Troop');
            $ret['buttons'][] = 'btn_draw_1';
            $ret['buttons'][] = 'btn_pass';
        }


        if (($counttroopdeck == 0) || ($counttroophand == 8)) {
            $ret['titleyou'] = clienttranslate('#icon# ${you} cannot draw Troops');
            $ret['buttons'][] = 'btn_continue';
        }


        return $ret;
    }

    public function Base31_Step1($parg1, $parg2, $varg1, $varg2)
    {

        if ($varg1 == "btn_draw_1") {

            $nb_troops_hand = self::getUniqueValueFromDB("SELECT COUNT(card_id) FROM troop WHERE card_location = 'hand' AND card_type_arg = '{$this->player_id}'");
            $old_troops = self::getObjectListFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_ordre ordre FROM troop WHERE card_location = 'hand' AND card_type_arg ='{$this->player_id}'");

            $new_troops = game::$instance->troop->pickCardsForLocation(1, $this->player_deck, 'hand');

            $type1 = $this->player_color_number . $new_troops[0]['type'] % 10;

            game::$instance->notifyPlayer(
                $this->player_id,
                'drawTroopPrivate',
                clienttranslate('${you} draw ${log1}'),
                array(
                    'you' =>    [   'log' => '<b style="color: #${color};">${you_name}</b>',
                                        'args'=> ['you_name' => clienttranslate('You'), 'color'=>$this->player_color, 'i18n' => ['you_name'] ]
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


    /////////// BASE 41 /////////

    public function argBase41_Step1($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} activates a special base');

        $ret['icon'] = '<span class="icon_power_bandeau icon_powerbase_4"></span>';

        $ret["selected"][] = 'base_' . $this->board_name . '_' . $parg1;

        $test = 0;

        $bases_adjacentes = game::$instance->_bases[$this->board_name][$parg1]['adjacents'];
        foreach ($bases_adjacentes as $base_adjacente) {
            $nb_troop_on_base = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location = 'board' AND card_location_arg = '{$base_adjacente}'", true));
            if ($nb_troop_on_base >= 1) {
                $infos_troopmax = self::getObjectListFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location_arg location_arg, card_ordre ordre FROM troop WHERE card_location = 'board' AND card_location_arg = '{$base_adjacente}' AND card_ordre = (SELECT MAX(card_ordre) FROM troop WHERE card_location = 'board' AND card_location_arg = '{$base_adjacente}')");
                if ($infos_troopmax[0]['type_arg'] != $this->player_id) // si elle appartient au joueur adverse 
                {
                    $ret["selectable"][] = 'base_' . $this->board_name . '_' . $infos_troopmax[0]['location_arg'];
                    $test = 1;
                }
            }
        }

        $ret['opponent'] = '<span style="color: #' . $this->player_color_opponent . ';">' . $this->player_name_opponent . '</span>';

        if ($test == 1) {
            $ret['titleyou'] = clienttranslate('#icon# ${you} can choose an enemy Troop adjacent to this base and move it on any base adjacent to #opponent#\'s starting base');
            
            $ret['buttons'][] = 'btn_pass';
        }

        if ($test == 0) {

            $ret['titleyou'] = clienttranslate('#icon# There are no enemy Troops adjacent to this base');
            $ret['buttons'][] = 'btn_continue';
        }





        return $ret;
    }

    public function Base41_Step1($parg1, $parg2, $varg1, $varg2)
    {
        if (($varg1 == "btn_pass") || ($varg1 == "btn_continue")) {
            game::$instance->addPending($this->player_id, "VerifBase");
        }

        else {
            game::$instance->addPending($this->player_id, "Base41_Step2", $varg1, $parg1);
        }
    }

    
    public function argBase41_Step2($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} activates a special base');

        $ret['icon'] = '<span class="icon_power_bandeau icon_powerbase_4"></span>';

        $ret['titleyou'] = clienttranslate('#icon# ${you} must choose the destination base');

        $ret["selected"][] = $parg1;

        $bases_adjacentes_opponent_start = game::$instance->_bases[$this->board_name][$this->opponent_start_base[0]]['adjacents'];

        foreach ($bases_adjacentes_opponent_start as $base) {
            $ret["selectable"][] = 'base_' . $this->board_name . '_' . $base;
        }


        $ret['buttons'][] = 'btn_cancel';


        return $ret;
    }

    public function Base41_Step2($parg1, $parg2, $varg1, $varg2)
    {
        if ($varg1 == "btn_cancel") {
            game::$instance->addPending($this->player_id, "Base41_Step1", $parg2);
        } else {
            if ($this->player_pref_confirm == 1) {

                $couple_base = $parg1 . '_' . $varg1;
                game::$instance->addPending($this->player_id, "Base41_Confirm", $couple_base, $parg2);
            }

            if ($this->player_pref_confirm == 2) {
                $explode1 = explode("_", $parg1);
                $explode2 = explode("_", $varg1);

                $infos_troopmax = self::getObjectListFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_ordre ordre FROM troop WHERE card_location = 'board' AND card_location_arg = '{$explode1[2]}' AND card_ordre = (SELECT MAX(card_ordre) FROM troop WHERE card_location = 'board' AND card_location_arg = '{$explode1[2]}')");
                $compteur_troop_sur_base = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location ='board' AND card_location_arg = '{$explode2[2]}'", true));

                game::$instance->troop->moveCard($infos_troopmax[0]['id'], 'board', $explode2[2]);
                self::DbQuery("UPDATE troop set card_ordre = $compteur_troop_sur_base + 1 WHERE card_id = '{$infos_troopmax[0]['id']}'");

                $type1 = $infos_troopmax[0]['type'];

                game::$instance->notifyAllPlayers(
                    'moveTroopBoardToBoard',
                    clienttranslate('${player_name} moves ${log1}'),
                    array(
                        'player_name' => $this->player_name,
                        'infos_troop' => $infos_troopmax[0],
                        'base_id' => $explode2[2],
                        'ordre' => $compteur_troop_sur_base + 1,
                        'log1' => game::$instance->getLogsType($type1),

                    )
                );

                $win = game::$instance->testZoneAndStar($explode2[2], $this->board_name);

                if ($win == 0) {
                    game::$instance->addPending($this->player_id, "VerifBase");
                }

                if ($win == 1) {
                    game::$instance->addPending($this->player_id, "FinGame1", 2);
                }
            }
        }
    }

    public function argBase41_Confirm($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} activates a special base');

        $ret['icon'] = '<span class="icon_power_bandeau icon_powerbase_4"></span>';

        $ret['titleyou'] = clienttranslate('#icon# ${you} must confirm');

        $explode = explode("_", $parg1);

        $ret["selected"][] = 'base_' . $this->board_name . '_' . $explode[2];
        $ret["selected"][] = 'base_' . $this->board_name . '_' . $explode[5];


        $ret['buttons'][] = 'btn_yes';
        $ret['buttons'][] = 'btn_no';



        return $ret;
    }

    public function Base41_Confirm($parg1, $parg2, $varg1, $varg2)
    {
        if ($varg1 == "btn_no") {
            game::$instance->addPending($this->player_id, "Base41_Step1", $parg2);
        }

        if ($varg1 == "btn_yes") {
            $explode = explode("_", $parg1);

            $infos_troopmax = self::getObjectListFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_ordre ordre FROM troop WHERE card_location = 'board' AND card_location_arg = '{$explode[2]}' AND card_ordre = (SELECT MAX(card_ordre) FROM troop WHERE card_location = 'board' AND card_location_arg = '{$explode[2]}')");
            $compteur_troop_sur_base = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location ='board' AND card_location_arg = '{$explode[5]}'", true));

            game::$instance->troop->moveCard($infos_troopmax[0]['id'], 'board', $explode[5]);
            self::DbQuery("UPDATE troop set card_ordre = $compteur_troop_sur_base + 1 WHERE card_id = '{$infos_troopmax[0]['id']}'");

            $type1 = $infos_troopmax[0]['type'];

            game::$instance->notifyAllPlayers(
                'moveTroopBoardToBoard',
                clienttranslate('${player_name} moves ${log1}'),
                array(
                    'player_name' => $this->player_name,
                    'infos_troop' => $infos_troopmax[0],
                    'base_id' => $explode[5],
                    'ordre' => $compteur_troop_sur_base + 1,
                    'log1' => game::$instance->getLogsType($type1),

                )
            );

            $win = game::$instance->testZoneAndStar($explode[5], $this->board_name);

            if ($win == 0) {
                game::$instance->addPending($this->player_id, "VerifBase");
            }

            if ($win == 1) {
                game::$instance->addPending($this->player_id, "FinGame1", 2);
            }
        }
    }


    /////////// BASE 51 /////////

    public function argBase51_Step1($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} activates a special base');


        $ret['icon'] = '<span class="icon_power_bandeau icon_powerbase_5"></span>';

        $ret["selected"][] = 'base_' . $this->board_name . '_' . $parg1;

        $nb_troops_on_discard = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='discard' AND card_type_arg = '{$this->player_id}'", true));
        $counttroophand = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='hand' AND card_type_arg = '{$this->player_id}'", true));

        if (($nb_troops_on_discard >= 1) && ($counttroophand <= 7)) {
            $ret['titleyou'] = clienttranslate('#icon# ${you} can place back on rack a discarded Troop');

            $troops_discard = self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='discard' AND card_type_arg = '{$this->player_id}'", true);
            foreach ($troops_discard as $troop_discard) {
                $ret["selectable"][] = 'troop_' . $troop_discard;
            }
            
            $ret['buttons'][] = 'btn_pass';
        } else {
            $ret['titleyou'] = clienttranslate('#icon# ${you} cannot place back a discarded Troop');
            $ret['buttons'][] = 'btn_continue';
        }



        return $ret;
    }

    public function Base51_Step1($parg1, $parg2, $varg1, $varg2)
    {
        if (($varg1 == "btn_pass") || ($varg1 == "btn_continue")) {
            game::$instance->addPending($this->player_id, "VerifBase");
        }

        else {
            if ($this->player_pref_confirm == 1) {

                game::$instance->addPending($this->player_id, "Base51_Confirm", $varg1, $parg1);
            }

            if ($this->player_pref_confirm == 2) {
                $explode_troop = explode("_", $varg1);

                $infos_troop = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_ordre ordre FROM troop WHERE card_id = '{$explode_troop[1]}'");

                game::$instance->troop->moveCard($explode_troop[1], 'hand');
                self::DbQuery("UPDATE troop set card_ordre = 1 WHERE card_id = '{$explode_troop[1]}'");

                $type1 = $infos_troop['type'];

                game::$instance->notifyAllPlayers(
                    'recoverTroopFromDiscard',
                    clienttranslate('${player_name} places back ${log1} from discard to rack'),
                    array(
                        'player_name' => $this->player_name,
                        'infos_troop' => $infos_troop,
                        'log1' => game::$instance->getLogsType($type1),
                    )
                );


                game::$instance->addPending($this->player_id, "VerifBase");
            }
        }
    }

    
    public function argBase51_Confirm($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} activates a special base');

        $ret['icon'] = '<span class="icon_power_bandeau icon_powerbase_5"></span>';

        $ret['titleyou'] = clienttranslate('#icon# ${you} must confirm');

        $ret["selected"][] = $parg1;


        $ret['buttons'][] = 'btn_yes';
        $ret['buttons'][] = 'btn_no';



        return $ret;
    }

    public function Base51_Confirm($parg1, $parg2, $varg1, $varg2)
    {
        if ($varg1 == "btn_no") {
            game::$instance->addPending($this->player_id, "Base51_Step1", $parg2);
        }

        if ($varg1 == "btn_yes") {

            $explode_troop = explode("_", $parg1);

            $infos_troop = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_ordre ordre FROM troop WHERE card_id = '{$explode_troop[1]}'");

            game::$instance->troop->moveCard($explode_troop[1], 'hand');
            self::DbQuery("UPDATE troop set card_ordre = 1 WHERE card_id = '{$explode_troop[1]}'");

            $type1 = $infos_troop['type'];

            game::$instance->notifyAllPlayers(
                'recoverTroopFromDiscard',
                clienttranslate('${player_name} places back ${log1} from discard to rack'),
                array(
                    'player_name' => $this->player_name,
                    'infos_troop' => $infos_troop,
                    'log1' => game::$instance->getLogsType($type1),
                )
            );

            game::$instance->addPending($this->player_id, "VerifBase");
        }
    }


    /////////// BASE 81 /////////

    public function argBase81_Step1($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} activates a special base');

        $ret['icon'] = '<span class="icon_power_bandeau icon_powerbase_8"></span>';

        $ret["selected"][] = 'base_' . $this->board_name . '_' . $parg1;

        $counttroophandopponent_noblocked = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='hand' AND card_type_arg != '{$this->player_id}' AND card_blocked = 0", true));

        $ret['opponent'] = '<span style="color: #' . $this->player_color_opponent . ';">' . $this->player_name_opponent . '</span>';

        if ($counttroophandopponent_noblocked >= 1) {
            $ret['titleyou'] = clienttranslate('#icon# ${you} can point a Troop on #opponent#\'s rack (without looking at it)... #opponent# will not be able to place on their next turn');
            $ret['buttons'][] = 'btn_point';
            $ret['buttons'][] = 'btn_pass';
        }

        if ($counttroophandopponent_noblocked == 0) {
            $ret['titleyou'] = clienttranslate('#icon# #opponent# has no Troops on rack');
            $ret['buttons'][] = 'btn_continue';
        }




        return $ret;
    }

    public function Base81_Step1($parg1, $parg2, $varg1, $varg2)
    {
        if (($varg1 == "btn_pass") || ($varg1 == "btn_continue")) {
            game::$instance->addPending($this->player_id, "VerifBase");
        }

        if ($varg1 == "btn_point") {
            if ($this->player_pref_discard_block == 1) {
                game::$instance->addPending($this->player_id, "Base81_Step2", $parg1);
            }

            if ($this->player_pref_discard_block == 2) {

                $troops_noblocked = self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location = 'hand' AND card_type_arg != '{$this->player_id}' AND card_blocked = 0", true);
                $count = count($troops_noblocked);
                $rand = bga_rand(1, $count);
                $troopid_blocked = $troops_noblocked[$rand - 1];

                $all_valeur_block = self::getObjectListFromDB("SELECT card_blocked FROM troop WHERE card_location = 'hand' AND card_type_arg != '{$this->player_id}'", true);
                $count_all_troupe = count($all_valeur_block);
                $possible = [];
                for ($i = 1; $i <= $count_all_troupe; $i++) {
                    if (!in_array($i, $all_valeur_block)) {
                        $possible[] = $i;
                    }
                }
                //$cout_possible = count($possible);
                //$rand2 = bga_rand(1, $cout_possible);
                //$choix_auto = $possible[$rand2 - 1];

                $choix_auto = $possible[0];



                $infos_troop_before = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_ordre ordre, card_blocked blocked FROM troop WHERE card_id = '{$troopid_blocked}'");

                self::DbQuery("UPDATE troop set card_blocked = $choix_auto WHERE card_id = '{$troopid_blocked}'");

                $infos_troop_after = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_ordre ordre, card_blocked blocked FROM troop WHERE card_id = '{$troopid_blocked}'");

                $type1 = $infos_troop_before['type'];

                game::$instance->notifyPlayer(
                    $this->player_id_opponent,
                    'hideTroopOnRackPrivate',
                    clienttranslate('${player_name} blocks ${log1}'),
                    array(
                        'player_name' => $this->player_name,
                        'player_id' => $this->player_id,
                        'infos_troop_before' => $infos_troop_before,
                        'infos_troop_after' => $infos_troop_after,
                        'log1' => game::$instance->getLogsType($type1),


                    )
                );

                game::$instance->notifyAllPlayers(
                    'hideTroopOnRackPublic',
                    '',
                    array(
                        'player_name' => $this->player_name,
                        'player_id' => $this->player_id,
                        'card_blocked' => $choix_auto,
                    )
                );

                $type0 = $this->opponent_color_number . "0";

                game::$instance->notifyAllPlayers(
                    'message_allplayers_without_player',
                    clienttranslate('${player_name} blocks ${log0}'),
                    array(
                        'player_name' => $this->player_name,
                        'player_id' => $this->player_id_opponent,
                        'log0' => game::$instance->getLogsType($type0),


                    )
                );



                game::$instance->addPending($this->player_id, "VerifBase");
            }
        }
    }


    public function argBase81_Step2($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} places a Troop');

        $ret['icon'] = '<span class="icon_power_bandeau icon_powerbase_8"></span>';

        $ret['titleyou'] = clienttranslate('#icon# ${you} must choose a Troop to lay down');

        $troop_id_opponent_hand = self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location = 'hand' AND card_type_arg != '{$this->player_id}'", true);
        $count = count($troop_id_opponent_hand);

        $troops_blocked = self::getObjectListFromDB("SELECT card_blocked FROM troop WHERE card_location = 'hand' AND card_type_arg != '{$this->player_id}' AND card_blocked != 0", true);

        for ($i = 1; $i <= $count; $i++) {
            if (!in_array($i, $troops_blocked)) {
                if ($this->player_color_title == 'blue') {
                    $ret["selectable"][] = 'red_troop_' . $i;
                }

                if ($this->player_color_title == 'red') {
                    $ret["selectable"][] = 'blue_troop_' . $i;
                }
            }
        }


        $ret['buttons'][] = 'btn_cancel';




        return $ret;
    }

    public function Base81_Step2($parg1, $parg2, $varg1, $varg2)
    {
        if ($varg1 == 'btn_cancel') {
            game::$instance->addPending($this->player_id, "Base81_Step1", $parg1);
        } else {
            $explode = explode("_", $varg1);

            $troops_noblocked = self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location = 'hand' AND card_type_arg != '{$this->player_id}' AND card_blocked = 0", true);
            $count = count($troops_noblocked);
            $rand = bga_rand(1, $count);

            $troopid_blocked = $troops_noblocked[$rand - 1];

            $infos_troop_before = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_ordre ordre, card_blocked blocked FROM troop WHERE card_id = '{$troopid_blocked}'");

            self::DbQuery("UPDATE troop set card_blocked = $explode[2] WHERE card_id = '{$troopid_blocked}'");

            $infos_troop_after = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_ordre ordre, card_blocked blocked FROM troop WHERE card_id = '{$troopid_blocked}'");

            $type1 = $infos_troop_before['type'];

            game::$instance->notifyPlayer(
                $this->player_id_opponent,
                'hideTroopOnRackPrivate',
                clienttranslate('${player_name} blocks ${log1}'),
                array(
                    'player_name' => $this->player_name,
                    'player_id' => $this->player_id,
                    'infos_troop_before' => $infos_troop_before,
                    'infos_troop_after' => $infos_troop_after,
                    'log1' => game::$instance->getLogsType($type1),


                )
            );

            game::$instance->notifyAllPlayers(
                'hideTroopOnRackPublic',
                '',
                array(
                    'player_name' => $this->player_name,
                    'player_id' => $this->player_id,
                    'card_blocked' => $explode[2],
                )
            );

            $type0 = $this->opponent_color_number . "0";

            game::$instance->notifyAllPlayers(
                'message_allplayers_without_player',
                clienttranslate('${player_name} blocks ${log0}'),
                array(
                    'player_name' => $this->player_name,
                    'player_id' => $this->player_id_opponent,
                    'log0' => game::$instance->getLogsType($type0),


                )
            );

            game::$instance->addPending($this->player_id, "VerifBase");
        }
    }
}
