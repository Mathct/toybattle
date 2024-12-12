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
        $ret['title'] = clienttranslate('${actplayer} places a troop');
        $ret['titleyou'] = clienttranslate('${you} place a troop');

        return $ret;
    }

    public function VerifTroop($parg1, $parg2, $varg1, $varg2)
    {

        /// il y aura une verif base speciale qui annule l'effet de la troupe sur le board station
        
        
        if($parg1 == 1)
        {
            game::$instance->addPending($this->player_id, "Troop1_Step1", $parg2);
        }

        if($parg1 == 2)
        {
            game::$instance->addPending($this->player_id, "Troop2_Step1", $parg2);
        }

        if($parg1 == 3)
        {
            game::$instance->addPending($this->player_id, "Troop3_Step1", $parg2);
        }

        if($parg1 == 4)
        {
            game::$instance->addPending($this->player_id, "VerifBase", $parg2);
        }

        if($parg1 == 5)
        {
            game::$instance->addPending($this->player_id, "Troop5_Step1", $parg2);
        }

        if($parg1 == 6)
        {
            game::$instance->addPending($this->player_id, "Troop6_Step1", $parg2);
        }

        if($parg1 > 6)
        {
            game::$instance->addPending($this->player_id, "VerifBase", $parg2);
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
        $ret['title'] = clienttranslate('${actplayer} places a troop');

        $counttroopdeck = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='{$this->player_deck}'", true));
        $counttroophand = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='hand' AND card_type_arg = '{$this->player_id}'", true));

        if (($counttroopdeck >= 2) && ($counttroophand <= 6)) 
        {
            $ret['titleyou'] = clienttranslate('SKULLY: ${you} can draw 2 troops');
            $ret['buttons'][] = 'btn_draw_2';
            $ret['buttons'][] = 'btn_no';
            
        }

        if ((($counttroopdeck == 1) && ($counttroophand <= 7))||(($counttroopdeck >= 1) && ($counttroophand == 7)))
        {
            $ret['titleyou'] = clienttranslate('SKULLY: ${you} can draw 1 troop');
            $ret['buttons'][] = 'btn_draw_1';
            $ret['buttons'][] = 'btn_no';
        }

        
        if (($counttroopdeck == 0)||($counttroophand==8))
        {
            $ret['titleyou'] = clienttranslate('SKULLY: ${you} cannot draw troops');
            $ret['buttons'][] = 'btn_continue';
        }
        
        
        return $ret;
    }

    public function Troop1_Step1($parg1, $parg2, $varg1, $varg2)
    {
        if ((($varg1 == "btn_draw_1") || ($varg1 == "btn_draw_2"))) 
        {
            
            $nb_troops_hand = self::getUniqueValueFromDB("SELECT COUNT(card_id) FROM troop WHERE card_location = 'hand' AND card_type_arg = '{$this->player_id}'");
            $old_troops = self::getObjectListFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_ordre ordre FROM troop WHERE card_location = 'hand' AND card_type_arg ='{$this->player_id}'");

            if ($varg1 == 'btn_draw_2') 
            {
                $new_troops = game::$instance->troop->pickCardsForLocation(2, $this->player_deck, 'hand');
                

                game::$instance->notifyPlayer(
                    $this->player_id,
                    'drawTroopPrivate',
                    clienttranslate('You draw icon1 icon2 (icon a mettre en place plus tard)'),
                    array(
                        'player_id' => $this->player_id,
                        'origine' => "deck",
                        'new_troops' => $new_troops,
                        'old_troops' => $old_troops




                    )
                );

                game::$instance->notifyAllPlayers(
                    'drawTroopPublic',
                    clienttranslate('${player_name} draws 2 troops'),
                    array(
                        'player_name' => $this->player_name,
                        'player_id' => $this->player_id,
                        'origine' => "deck",
                        'nb_troops' => 2,
                        'nb_troops_hand' => $nb_troops_hand


                    )
                );
            }

            if ($varg1 == 'btn_draw_1') 
            {
                $new_troops = game::$instance->troop->pickCardsForLocation(1, $this->player_deck, 'hand');
                
                game::$instance->notifyPlayer(
                    $this->player_id,
                    'drawTroopPrivate',
                    clienttranslate('You draw icon1 (icon a mettre en place plus tard)'),
                    array(
                        'player_id' => $this->player_id,
                        'origine' => "deck",
                        'new_troops' => $new_troops



                    )
                );

                game::$instance->notifyAllPlayers(
                    'drawTroopPublic',
                    clienttranslate('${player_name} draws 1 troop'),
                    array(
                        'player_name' => $this->player_name,
                        'player_id' => $this->player_id,
                        'origine' => "deck",
                        'nb_troops' => 1,
                        'nb_troops_hand' => $nb_troops_hand


                    )
                );
            }

            
        }


        game::$instance->addPending($this->player_id, "VerifBase", $parg1);
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
        $ret['title'] = clienttranslate('${actplayer} places a troop');

        $counttroophand = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='hand' AND card_type_arg = '{$this->player_id}'", true));

        if($counttroophand >= 1)
        {
            $place_ok = 0;
            $list_troop = self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='hand' AND card_type_arg = '{$this->player_id}'", true);
            foreach ($list_troop as $troop)
            {
                $troop_id = 'troop_'.$troop;
                $possible_base = game::$instance->getPossibleBase($this->start_base, $troop_id, $this->player_id);
                if(count($possible_base) >= 1)
                {
                    $place_ok = 1;
                }


            }

            if ($place_ok == 1)
            {
            $ret['titleyou'] = clienttranslate('CAP\'TAINE: ${you} can place an other troop');
            $ret['buttons'][] = 'btn_place_troop';
            $ret['buttons'][] = 'btn_no';
            }
        }

        else
        {
            $ret['titleyou'] = clienttranslate('CAP\'TAINE: ${you} cannot place an other troop');
            $ret['buttons'][] = 'btn_continue';
        }

        

            
        
        return $ret;
    }

    public function Troop2_Step1($parg1, $parg2, $varg1, $varg2)
    {
        
        if($varg1 == 'btn_place_troop')
        {

            game::$instance->addPending($this->player_id, "Troop2_Step2", $parg1);
        }

        else{
            
            game::$instance->addPending($this->player_id, "VerifBase", $parg1);
        }
        
        
    }

    function argTroop2_Step2($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} places a troop');
        $ret['titleyou'] = clienttranslate('${you} must choose a troop');

        $list_troop = self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='hand' AND card_type_arg = '{$this->player_id}'", true);
        foreach ($list_troop as $troop)
        {
            $troop_id = 'troop_'.$troop;
            $possible_base = game::$instance->getPossibleBase($this->start_base, $troop_id, $this->player_id);
            if(count($possible_base) >= 1)
            {
                $ret["selectable"][] = $troop_id;
            }


        }


        
        $ret['buttons'][] = 'btn_cancel';


        return $ret;
    }

    function Troop2_Step2($parg1, $parg2, $varg1, $varg2)
    {
        if ($varg1 == "btn_cancel") 
        {
            game::$instance->addPending($this->player_id, "Troop2_Step1", $parg1);
        } 
        
        else 
        {
            game::$instance->addPending($this->player_id, "Troop2_Step3", $varg1, $parg1);
        }
    }

    function argTroop2_Step3($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} places a troop');
        
            

        $ret["selected"][] = $parg1;

        $tableau_boards_name = ["castle", "pool", "clouds", "jungle", "cemetery", "carribean", "station", "battlefield"];
        $board_name = $tableau_boards_name[game::$instance->getGameStateValue('board') - 1];

        
        $possible_base = game::$instance->getPossibleBase($this->start_base, $parg1, $this->player_id);

        if(count($possible_base) >= 1)
        {
            $ret['titleyou'] = clienttranslate('${you} must choose a base');
        }
        else
        {
            $ret['titleyou'] = clienttranslate('${you} cannot place this troop');
        }
        
        foreach ($possible_base as $base) {

            $ret["selectable"][] = "base_" . $board_name . "_" . $base;
        }
        
        
        
        $ret['buttons'][] = 'btn_cancel';


        return $ret;
    }

    function Troop2_Step3($parg1, $parg2, $varg1, $varg2)
    {

        if ($varg1 == "btn_cancel") 
        {
            game::$instance->addPending($this->player_id, "Troop2_Step2", $parg2);
        } 
        
        else 
        {

            if ($this->player_pref_confirm == 1) 
            {
                
                $couple_troop_base_selected = $parg1."_".$varg1;
                game::$instance->addPending($this->player_id, "Troop2_Confirm", $couple_troop_base_selected, $parg2);
            }

            if ($this->player_pref_confirm == 2) 
            {
                $explode_troop = explode("_", $parg1);
                $explode_base = explode("_", $varg1);

                $compteur_troop_sur_base = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location ='board' AND card_location_arg = '{$explode_base[2]}'", true));

                $nb_troops_hand = self::getUniqueValueFromDB("SELECT COUNT(card_id) FROM troop WHERE card_location = 'hand' AND card_type_arg = '{$this->player_id}'");


                game::$instance->troop->moveCard($explode_troop[1], 'board', $explode_base[2]);
                self::DbQuery("UPDATE troop set card_ordre = $compteur_troop_sur_base + 1 WHERE card_id = '{$explode_troop[1]}'");

                $infos_troop = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_ordre ordre FROM troop WHERE card_id = '{$explode_troop[1]}'");


                game::$instance->notifyAllPlayers(
                    'moveTroop',
                    clienttranslate('${player_name} places troop'),
                    array(
                        
                        'base_id' => $varg1,
                        'ordre' => $compteur_troop_sur_base + 1,
                        'player_name' => $this->player_name,
                        'player_id' => $this->player_id,
                        'origine' => "hand",
                        'infos_troop' => $infos_troop,
                        'nb_troops_hand' => $nb_troops_hand


                    )
                );

                $force_troop = self::getUniqueValueFromDB("SELECT card_type FROM troop WHERE card_id = '{$explode_troop[1]}'") %10;

                $bases = $parg2."_".$explode_base[2];
                game::$instance->addPending($this->player_id, "VerifTroop", $force_troop, $bases);
            }
        }
    }

    function argTroop2_Confirm($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} places a troop');
        $ret['titleyou'] = clienttranslate('${you} must confirm');

        $explode = explode("_", $parg1);
        $ret["selected"][] = "troop_".$explode[1];
        $ret["selected"][] = "base_".$explode[3]."_".$explode[4];


        $ret['buttons'][] = 'btn_yes';
        $ret['buttons'][] = 'btn_no';

        return $ret;
    }

    function Troop2_Confirm($parg1, $parg2, $varg1, $varg2)
    {
        if ($varg1 == "btn_yes") {
            $explode = explode("_", $parg1);
            
            $compteur_troop_sur_base = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location ='board' AND card_location_arg = '{$explode[4]}'", true));

            $nb_troops_hand = self::getUniqueValueFromDB("SELECT COUNT(card_id) FROM troop WHERE card_location = 'hand' AND card_type_arg = '{$this->player_id}'");

            game::$instance->troop->moveCard($explode[1], 'board', $explode[4]);
            self::DbQuery("UPDATE troop set card_ordre = $compteur_troop_sur_base + 1 WHERE card_id = '{$explode[1]}'");

            $infos_troop = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_ordre ordre FROM troop WHERE card_id = '{$explode[1]}'");


            game::$instance->notifyAllPlayers(
                'moveTroop',
                clienttranslate('${player_name} places troop'),
                array(
                    
                    'base_id' => 'base_'.$explode[3].'_'.$explode[4],
                    'ordre' => $compteur_troop_sur_base + 1,
                    'player_name' => $this->player_name,
                    'player_id' => $this->player_id,
                    'origine' => "hand",
                    'infos_troop' => $infos_troop,
                    'nb_troops_hand' => $nb_troops_hand
                )
            );

            

            $force_troop = self::getUniqueValueFromDB("SELECT card_type FROM troop WHERE card_id = '{$explode[1]}'") %10;

            $bases = $parg2."_".$explode[4];
            game::$instance->addPending($this->player_id, "VerifTroop", $force_troop, $bases);
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
        $ret['title'] = clienttranslate('${actplayer} places a troop');

        $explode = explode("_", $parg1);
        $base_mastok = end($explode);

        $list_base_discard_possible = [];
        $bases_adjacentes_mastok = game::$instance->_bases[$this->board_name][$base_mastok]['adjacents'];

        foreach($bases_adjacentes_mastok as $base)
        {
            $infos_troopmax = self::getObjectListFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location_arg location_arg, card_ordre ordre FROM troop WHERE card_location = 'board' AND card_location_arg = '{$base}' AND card_ordre = (SELECT MAX(card_ordre) FROM troop WHERE card_location = 'board' AND card_location_arg = '{$base}')");
            if(count($infos_troopmax) != 0)
            {

                if($infos_troopmax[0]['type_arg'] != $this->player_id)
                {
                    $list_base_discard_possible[] = $infos_troopmax[0]['location_arg'];
                }

            }
                       
        }

        if(count($list_base_discard_possible) == 0)
        {
            $ret['titleyou'] = clienttranslate('MASTOK: ${you} cannot discard an adjacent troop');
            $ret['buttons'][] = 'btn_continue';
        }

        else
        {
            $ret['titleyou'] = clienttranslate('MASTOK: ${you} can discard an adjacent troop');

            foreach($list_base_discard_possible as $base_discard)
            {
                $ret["selectable"][] = "base_".$this->board_name."_".$base_discard;
            }

            
            $ret['buttons'][] = 'btn_no';

        }

                        
        
        return $ret;
    }

    public function Troop3_Step1($parg1, $parg2, $varg1, $varg2)
    {

        if(($varg1 == 'btn_continue')||($varg1 == 'btn_no'))
        {
            game::$instance->addPending($this->player_id, "VerifBase", $parg1);
        }

        else 
        {
            if ($this->player_pref_confirm == 1)
            {
                game::$instance->addPending($this->player_id, "Troop3_Confirm", $varg1, $parg1);
            }

            if ($this->player_pref_confirm == 2)
            {
                $explode = explode("_", $varg1);
                $infos_troop = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_ordre ordre FROM troop WHERE card_location = 'board' AND card_location_arg = '{$explode[2]}' AND card_ordre = (SELECT MAX(card_ordre) FROM troop WHERE card_location = 'board' AND card_location_arg = '{$explode[2]}')");

                game::$instance->troop->moveCard($infos_troop['id'], 'discard', 0);

                self::DbQuery( "UPDATE troop set card_ordre = 1 WHERE card_id = '{$infos_troop['id']}'" );

                game::$instance->notifyAllPlayers(
                    'discardTroopFromBoard',
                    clienttranslate('${player_name} discards an opposing troop from the board'),
                    array(
                        
                        'player_name' => $this->player_name,
                        'infos_troop' => $infos_troop,
                        
                    )
                );
                game::$instance->addPending($this->player_id, "VerifBase", $parg1);
            }
            
        }
   
        
    }

    public function argTroop3_Confirm($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} places a troop');

        $ret['titleyou'] = clienttranslate('${you} must confirm');

        $ret["selected"][] = $parg1;

        $ret['buttons'][] = 'btn_yes';
        $ret['buttons'][] = 'btn_no';

        

                                      
        
        return $ret;
    }

    public function Troop3_Confirm($parg1, $parg2, $varg1, $varg2)
    {

        if($varg1 == 'btn_no')
        {
            game::$instance->addPending($this->player_id, "Troop3_Step1", $parg2);
        }

        if($varg1 == 'btn_yes')
        {
            
            $explode = explode("_", $parg1);
            $infos_troop = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_ordre ordre FROM troop WHERE card_location = 'board' AND card_location_arg = '{$explode[2]}' AND card_ordre = (SELECT MAX(card_ordre) FROM troop WHERE card_location = 'board' AND card_location_arg = '{$explode[2]}')");

            game::$instance->troop->moveCard($infos_troop['id'], 'discard', 0);

            self::DbQuery( "UPDATE troop set card_ordre = 1 WHERE card_id = '{$infos_troop['id']}'" );

            game::$instance->notifyAllPlayers(
                'discardTroopFromBoard',
                clienttranslate('${player_name} discards an opposing troop from the board'),
                array(
                    
                    'player_name' => $this->player_name,
                    'infos_troop' => $infos_troop,
                    
                )
            );

            

            game::$instance->addPending($this->player_id, "VerifBase", $parg2);
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
        $ret['title'] = clienttranslate('${actplayer} places a troop');

        $troop_id_opponent_hand = self::getObjectListFromDB( "SELECT card_id FROM troop WHERE card_location = 'hand' AND card_type_arg != '{$this->player_id}'", true );
        $count = count($troop_id_opponent_hand);

        if ($count >=1)
        {
            $ret['titleyou'] = clienttranslate('XB-42: ${you} can discard a troop from the opponent\'s hand');

            $ret['buttons'][] = 'btn_yes';
            $ret['buttons'][] = 'btn_no';

        }

        if ($count == 0)
        {
            $ret['titleyou'] = clienttranslate('XB-42: ${you} cannot discard a troop from the opponent\'s hand');

            $ret['buttons'][] = 'btn_continue';
            

        }

        

                
        
        return $ret;
    }

    public function Troop5_Step1($parg1, $parg2, $varg1, $varg2)
    {

        if(($varg1 == 'btn_no')||($varg1 == 'btn_continue'))
        {
            game::$instance->addPending($this->player_id, "VerifBase", $parg1);
        }

        if($varg1 == 'btn_yes')
        {
            if($this->player_pref_discard == 1)
            {
                game::$instance->addPending($this->player_id, "Troop5_Step2", $parg1);
            }

            if($this->player_pref_discard == 2)
            {
                $troop_id_opponent_hand = self::getObjectListFromDB( "SELECT card_id FROM troop WHERE card_location = 'hand' AND card_type_arg != '{$this->player_id}'", true );
                $count = count($troop_id_opponent_hand);
                $rand = bga_rand(1, $count);
                $rand_troop_id = $troop_id_opponent_hand[$rand-1];

                $infos_troop = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_ordre ordre FROM troop WHERE card_id = '{$rand_troop_id}'");

                $selected_troop = 0;

                game::$instance->troop->moveCard($rand_troop_id, 'discard', 0);

                game::$instance->notifyAllPlayers(
                    'discardTroopFromHand',
                    clienttranslate('${player_name} discards an opposing troop from the hand'),
                    array(
                        
                        'player_name' => $this->player_name,
                        'infos_troop' => $infos_troop, // info_troop a discard avant discard
                        'selected_troop' => $selected_troop, // 0 si le joueur n'a pas choisi... de 1 à 8 si le joueur a choisi lui même
                        'nb_cards_in_hand' => $count, 
                        
                    )
                );


                game::$instance->addPending($this->player_id, "VerifBase", $parg1);
            }
            
        }
        

        
    }


    public function argTroop5_Step2($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} places a troop');

        $ret['titleyou'] = clienttranslate('${you} must choose a troop to discard');

        $troop_id_opponent_hand = self::getObjectListFromDB( "SELECT card_id FROM troop WHERE card_location = 'hand' AND card_type_arg != '{$this->player_id}'", true );
        $count = count($troop_id_opponent_hand);

        for ($i=1; $i<=$count; $i++)
        {
            if($this->player_color_title == 'blue')
            {
                $ret["selectable"][] = 'red_troop_'.$i;
            }

            if($this->player_color_title == 'red')
            {
                $ret["selectable"][] = 'blue_troop_'.$i;
            }
        }


        $ret['buttons'][] = 'btn_cancel';

                
        
        return $ret;
    }

    public function Troop5_Step2($parg1, $parg2, $varg1, $varg2)
    {

        if($varg1 == 'btn_cancel')
        {
            game::$instance->addPending($this->player_id, "Troop5_Step1", $parg1);
        }

        else
        {
          
            $troop_id_opponent_hand = self::getObjectListFromDB( "SELECT card_id FROM troop WHERE card_location = 'hand' AND card_type_arg != '{$this->player_id}'", true );
            $count = count($troop_id_opponent_hand);
            $rand = bga_rand(1, $count);
            $rand_troop_id = $troop_id_opponent_hand[$rand-1];

            $infos_troop = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_ordre ordre FROM troop WHERE card_id = '{$rand_troop_id}'");

            $explode = explode("_", $varg1);
            $selected_troop = $explode[2];

            game::$instance->troop->moveCard($rand_troop_id, 'discard', 0);

            game::$instance->notifyAllPlayers(
                'discardTroopFromHand',
                clienttranslate('${player_name} discards an opposing troop from the hand'),
                array(
                    
                    'player_name' => $this->player_name,
                    'infos_troop' => $infos_troop, // info_troop a discard avant discard
                    'selected_troop' => $selected_troop, // 0 si le joueur n'a pas choisi... de 1 à 8 si le joueur a choisi lui même
                    'nb_cards_in_hand' => $count, 
                    
                )
            );

            game::$instance->addPending($this->player_id, "VerifBase", $parg1);
           
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
        $ret['title'] = clienttranslate('${actplayer} places a troop');

        $counttroopdeck = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='{$this->player_deck}'", true));
        $counttroophand = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='hand' AND card_type_arg = '{$this->player_id}'", true));

       
        if (($counttroopdeck >= 1) && ($counttroophand <= 7))
        {
            $ret['titleyou'] = clienttranslate('STAR: ${you} can draw 1 troop');
            $ret['buttons'][] = 'btn_draw_1';
            $ret['buttons'][] = 'btn_no';
        }

        
        if (($counttroopdeck == 0)||($counttroophand == 8))
        {
            $ret['titleyou'] = clienttranslate('STAR: ${you} cannot draw troops');
            $ret['buttons'][] = 'btn_continue';
        }
        
        
        return $ret;
    }

    public function Troop6_Step1($parg1, $parg2, $varg1, $varg2)
    {
        if ($varg1 == "btn_draw_1")
        {
            
            $nb_troops_hand = self::getUniqueValueFromDB("SELECT COUNT(card_id) FROM troop WHERE card_location = 'hand' AND card_type_arg = '{$this->player_id}'");
            $old_troops = self::getObjectListFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_ordre ordre FROM troop WHERE card_location = 'hand' AND card_type_arg ='{$this->player_id}'");
            
            $new_troops = game::$instance->troop->pickCardsForLocation(1, $this->player_deck, 'hand');
            
            game::$instance->notifyPlayer(
                $this->player_id,
                'drawTroopPrivate',
                clienttranslate('You draw icon1 (icon a mettre en place plus tard)'),
                array(
                    'player_id' => $this->player_id,
                    'origine' => "deck",
                    'new_troops' => $new_troops



                )
            );

            game::$instance->notifyAllPlayers(
                'drawTroopPublic',
                clienttranslate('${player_name} draws 1 troop'),
                array(
                    'player_name' => $this->player_name,
                    'player_id' => $this->player_id,
                    'origine' => "deck",
                    'nb_troops' => 1,
                    'nb_troops_hand' => $nb_troops_hand


                )
            );
            

            
        }


        game::$instance->addPending($this->player_id, "VerifBase", $parg1);
    
    }








}