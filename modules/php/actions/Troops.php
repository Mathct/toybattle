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

        if($parg1 > 3)
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

        
        if ($counttroopdeck == 0)
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
            $ret['titleyou'] = clienttranslate('CAP\'TAINE: ${you} can place a second troop');
            $ret['buttons'][] = 'btn_place_troop';
            $ret['buttons'][] = 'btn_no';
        }

        else
        {
            $ret['titleyou'] = clienttranslate('CAP\'TAINE: ${you} cannot place a second troop');
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


        $troops = self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_type_arg ='{$this->player_id}' AND card_location='hand'", true);
        foreach ($troops as $troop_id) 
        {
            $ret["selectable"][] = "troop_" . $troop_id;
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
                    clienttranslate('${player_name} moves troop'),
                    array(
                        'mobile' =>  $parg1,
                        'parent' => $varg1,
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
                clienttranslate('${player_name} moves troop'),
                array(
                    'mobile' =>  'troop_'.$explode[1],
                    'parent' => 'base_'.$explode[3].'_'.$explode[4],
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

        $ret['titleyou'] = clienttranslate('MASTOK');

        if(($this->player_name = "Backstar0")||($this->player_name = "Backstar1")) //etc en fonction du nombre de joueurs
        {
            var_dump($base_mastok);
        }

        //$ret['titleyou'] = clienttranslate('MASTOK: ${you} can discard an adjacent and visible troop');

        //$ret['titleyou'] = clienttranslate('MASTOK: ${you} cannot discard an adjacent, visible troop');

        
$ret['buttons'][] = 'btn_cancel';
                
        
        return $ret;
    }

    public function Troop3_Step1($parg1, $parg2, $varg1, $varg2)
    {
        


        game::$instance->addPending($this->player_id, "VerifBase", $parg1);
    }


    ///////////////////////////
    ///////// TROOP 4 /////////
    ///////////////////////////


    public function argTroop4_Step1($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} places a troop');

        $ret['titleyou'] = clienttranslate('SKULLY: ${you} can draw 2 troops');

                
        
        return $ret;
    }

    public function Troop4_Step1($parg1, $parg2, $varg1, $varg2)
    {
        


        game::$instance->addPending($this->player_id, "VerifBase", $parg1);
    }








}