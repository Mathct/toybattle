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
        $ret['title'] = clienttranslate('${actplayer} pass');
        $ret['titleyou'] = clienttranslate('${you} pass');

        return $ret;
    }

    public function VerifBase($parg1, $parg2, $varg1, $varg2)
    {
        
        // mode + pool et station sans bases speciales
        if((game::$instance->gamestate->table_globals[100] == 1)&&($this->board_name != 'pool')&&($this->board_name != 'station'))

        {
           
            $check = self::getObjectListFromDB("SELECT id id, troop_id troop_id, base base FROM checkbase ORDER BY id ASC LIMIT 1");

            if($check == null)
            {
                game::$instance->addPendingFirst($this->player_id, "NormalTurn");
            }

            else
            {
                $numero_power = game::$instance->_bases[$this->board_name][$check[0]['base']]['power'];
                if($numero_power != 0)
                {
                    self::DbQuery("DELETE FROM checkbase WHERE id = '{$check[0]['id']}'");

                    $troop_id = $check[0]['troop_id'];
                    $base = $check[0]['base'];

                    /////// EN FONCTION DU BOARD //////

                    if($numero_power == 11) // CASTLE
                    {
                        game::$instance->addPending($this->player_id, "Base11_Step1", $troop_id, $base);
                    }

                    elseif($numero_power == 31) // CLOUDS
                    {
                        game::$instance->addPending($this->player_id, "Base31_Step1", $base);
                    }

                    elseif($numero_power == 41) // JUNGLE
                    {
                        game::$instance->addPending($this->player_id, "Base41_Step1", $base);
                    }

                    elseif($numero_power == 51) // CIMETIERE
                    {
                        game::$instance->addPending($this->player_id, "Base51_Step1", $base);
                    }

                    else
                    {
                        game::$instance->addPending($this->player_id, "VerifBase");
                    }
                }

                else
                {
                    self::DbQuery("DELETE FROM checkbase WHERE id = '{$check[0]['id']}'");
                    game::$instance->addPending($this->player_id, "VerifBase");

                }
                

            }

        }

        if((game::$instance->gamestate->table_globals[100] == 2)||($this->board_name == 'pool')||($this->board_name == 'station'))

        {
            
            self::DbQuery("DELETE FROM `checkbase`;");
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
        $ret['titleyou'] = clienttranslate('Special base: ${you} can recover a troop on the board (Be careful! This can trigger an area control for the opponent)');

        $ret["selected"][]= 'base_'.$this->board_name.'_'.$parg2;

        $ret['buttons'][] = 'btn_yes';
        $ret['buttons'][] = 'btn_no';

        return $ret;
    }

    public function Base11_Step1($parg1, $parg2, $varg1, $varg2)
    {
        if($varg1 == "btn_no")
        {
        game::$instance->addPending($this->player_id, "VerifBase");
        }

        if($varg1 == "btn_yes")
        {
            game::$instance->addPending($this->player_id, "Base11_Step2", $parg1, $parg2);
        }


    }

    public function argBase11_Step2($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} activates a special base');
        
        $test = 0;
        $all_bases = game::$instance->_bases[$this->board_name];
        $all_bases_a_checker = array_map('strval', array_keys($all_bases));
        $all_bases_sans_QG = [];
        
        $counttroophand = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='hand' AND card_type_arg = '{$this->player_id}'", true));

        if($counttroophand < 8)
        {
            foreach ($all_bases_a_checker as $allbase){
                if (($allbase >=10)&&($allbase <=40)&&($allbase != $parg2))  // j'enleve aussi la base declanchée pour le moment
                {
                    $all_bases_sans_QG[] = $allbase;
                }
            }

            if(count($all_bases_sans_QG) != 0)
            {

                    foreach ($all_bases_sans_QG as $base_sans_QG)
                    {
                        $count_troop_on_base = count(self::getObjectListFromDB( "SELECT card_id FROM troop WHERE card_location = 'board' AND card_location_arg = '{$base_sans_QG}'", true ));
                        if($count_troop_on_base >=1)
                        {
                            
                            $infos_troopmax = self::getObjectListFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_ordre ordre FROM troop WHERE card_location = 'board' AND card_location_arg = '{$base_sans_QG}' AND card_ordre = (SELECT MAX(card_ordre) FROM troop WHERE card_location = 'board' AND card_location_arg = '{$base_sans_QG}')");

                            if ($infos_troopmax[0]['type_arg'] == $this->player_id) // si elle appartient au joueur actif
                                {
                                    $ret["selectable"][] = 'base_'.$this->board_name.'_'.$base_sans_QG;
                                    $test = 1;
                                }
                        }
                        
                    }
            }
        }

        
        
        if($test == 1)
        {
            $ret['titleyou'] = clienttranslate('Special base: ${you} must choose a troop to recover');
            $ret['buttons'][] = 'btn_cancel';
        }

        if($test == 0)
        {
            $ret['titleyou'] = clienttranslate('Special base: ${you} cannot recover troops');
            $ret['buttons'][] = 'btn_continue';
        }
        
        

        

        return $ret;
    }

    public function Base11_Step2($parg1, $parg2, $varg1, $varg2)
    {
        if($varg1 == "btn_cancel")
        {
            game::$instance->addPending($this->player_id, "Base11_Step1", $parg1, $parg2);
        }

        elseif($varg1 == "btn_continue")
        {
            game::$instance->addPending($this->player_id, "VerifBase");
        }

        else
        {
              

            if ($this->player_pref_confirm == 1)
            {
                $duo_check = $parg1.'_'.$parg2;
                game::$instance->addPending($this->player_id, "Base11_Confirm", $varg1, $duo_check);
            }

            if ($this->player_pref_confirm == 2)
            {
                

                $explode = explode("_", $varg1);

                $nb_troops_hand = self::getUniqueValueFromDB("SELECT COUNT(card_id) FROM troop WHERE card_location = 'hand' AND card_type_arg = '{$this->player_id}'");

                $infos_troopmax = self::getObjectListFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_ordre ordre FROM troop WHERE card_location = 'board' AND card_location_arg = '{$explode[2]}' AND card_ordre = (SELECT MAX(card_ordre) FROM troop WHERE card_location = 'board' AND card_location_arg = '{$explode[2]}')");

                game::$instance->troop->moveCard($infos_troopmax[0]['id'], 'hand', 0);
                self::DbQuery( "UPDATE troop set card_ordre = 1 WHERE card_id = '{$infos_troopmax[0]['id']}'" );

                
                game::$instance->notifyAllPlayers(
                    'recoverTroopFromBoard',
                    clienttranslate('${player_name} recovers a troop'),
                    array(
                        
                        'player_name' => $this->player_name,
                        'infos_troop' => $infos_troopmax[0],
                        'nb_troops_hand' => $nb_troops_hand,
                        
                    )
                );

                
                ///// ATTENTION SI LE JOUEUR ENLEVE UNE TROOP SUR UNE BASE 
                game::$instance->testZoneAndStar($infos_troopmax[0]['location_arg'], $this->board_name);

                game::$instance->addPending($this->player_id, "VerifBase");
            }
            
        }

       

    }

    function argBase11_Confirm($parg1, $parg2)
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

    function Base11_Confirm($parg1, $parg2, $varg1, $varg2)
    {
        if ($varg1 == "btn_yes") {
            
            


            $explode = explode("_", $parg1);

            $nb_troops_hand = self::getUniqueValueFromDB("SELECT COUNT(card_id) FROM troop WHERE card_location = 'hand' AND card_type_arg = '{$this->player_id}'");
            
            $infos_troopmax = self::getObjectListFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_ordre ordre FROM troop WHERE card_location = 'board' AND card_location_arg = '{$explode[2]}' AND card_ordre = (SELECT MAX(card_ordre) FROM troop WHERE card_location = 'board' AND card_location_arg = '{$explode[2]}')");

            game::$instance->troop->moveCard($infos_troopmax[0]['id'], 'hand', 0);
            self::DbQuery( "UPDATE troop set card_ordre = 1 WHERE card_id = '{$infos_troopmax[0]['id']}'" );

            
            game::$instance->notifyAllPlayers(
                'recoverTroopFromBoard',
                clienttranslate('${player_name} recovers a troop'),
                array(
                    
                    'player_name' => $this->player_name,
                    'infos_troop' => $infos_troopmax[0],
                    'nb_troops_hand' => $nb_troops_hand,
                    
                )
            );

            ///// ATTENTION SI LE JOUEUR ENLEVE UNE TROOP SUR UNE BASE 
            game::$instance->testZoneAndStar($infos_troopmax[0]['location_arg'], $this->board_name);

            game::$instance->addPending($this->player_id, "VerifBase");

            
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
        

        $ret["selected"][]= 'base_'.$this->board_name.'_'.$parg1;

        $counttroopdeck = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='{$this->player_deck}'", true));
        $counttroophand = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='hand' AND card_type_arg = '{$this->player_id}'", true));

       
        if (($counttroopdeck >= 1) && ($counttroophand <= 7))
        {
            $ret['titleyou'] = clienttranslate('Special base: ${you} can draw 1 troop');
            $ret['buttons'][] = 'btn_draw_1';
            $ret['buttons'][] = 'btn_no';
        }

        
        if (($counttroopdeck == 0)||($counttroophand == 8))
        {
            $ret['titleyou'] = clienttranslate('Special base: ${you} cannot draw troops');
            $ret['buttons'][] = 'btn_continue';
        }

        
        return $ret;
    }

    public function Base31_Step1($parg1, $parg2, $varg1, $varg2)
    {
        
        if($varg1 == "btn_draw_1")
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
        

        $ret["selected"][]= 'base_'.$this->board_name.'_'.$parg1;

        $test = 0;

        $bases_adjacentes = game::$instance->_bases[$this->board_name][$parg1]['adjacents'];
        foreach ($bases_adjacentes as $base_adjacente) 
        {
            $nb_troop_on_base = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location = 'board' AND card_location_arg = '{$base_adjacente}'", true));
            if ($nb_troop_on_base >= 1)
            {
                $infos_troopmax = self::getObjectListFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_ordre ordre FROM troop WHERE card_location = 'board' AND card_location_arg = '{$base_adjacente}' AND card_ordre = (SELECT MAX(card_ordre) FROM troop WHERE card_location = 'board' AND card_location_arg = '{$base_adjacente}')");
                if ($infos_troopmax[0]['type_arg'] != $this->player_id) // si elle appartient au joueur adverse 
                {
                    $test = 1;

                }
            }
        }

        if($test == 1)
        {
            $ret['titleyou'] = clienttranslate('Special base: ${you} can choose an opposing troop adjacent to this base and move it anywhere you want on a base adjacent to the opponent\'s starting base');
            $ret['buttons'][] = 'btn_yes';
            $ret['buttons'][] = 'btn_no';

        }

        if($test == 0)
        {

            $ret['titleyou'] = clienttranslate('Special base: There are no opposing troops adjacent to this base');
            $ret['buttons'][] = 'btn_continue';

        }

        

        

        return $ret;
    }

    public function Base41_Step1($parg1, $parg2, $varg1, $varg2)
    {
        if(($varg1 == "btn_no")||($varg1 == "btn_continue"))
        {
        game::$instance->addPending($this->player_id, "VerifBase");
        }

        if($varg1 == "btn_yes")
        {
            game::$instance->addPending($this->player_id, "Base41_Step2", $parg1);
        }


    }

    public function argBase41_Step2($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} activates a special base');
        $ret['titleyou'] = clienttranslate('Special base: ${you} must choose a troop to move');

        $bases_adjacentes = game::$instance->_bases[$this->board_name][$parg1]['adjacents'];
        foreach ($bases_adjacentes as $base_adjacente) 
        {
            $nb_troop_on_base = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location = 'board' AND card_location_arg = '{$base_adjacente}'", true));
            if ($nb_troop_on_base >= 1)
            {
                $infos_troopmax = self::getObjectListFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location_arg location_arg, card_ordre ordre FROM troop WHERE card_location = 'board' AND card_location_arg = '{$base_adjacente}' AND card_ordre = (SELECT MAX(card_ordre) FROM troop WHERE card_location = 'board' AND card_location_arg = '{$base_adjacente}')");
                if ($infos_troopmax[0]['type_arg'] != $this->player_id) // si elle appartient au joueur adverse 
                {
                    $ret["selectable"][] = 'base_'.$this->board_name.'_'.$infos_troopmax[0]['location_arg'];

                }
            }
        }

        $ret['buttons'][] = 'btn_cancel';
        

        return $ret;
    }

    public function Base41_Step2($parg1, $parg2, $varg1, $varg2)
    {
        if($varg1 == "btn_cancel")
        {
            game::$instance->addPending($this->player_id, "Base41_Step1", $parg1);
        }

        else
        {

            game::$instance->addPending($this->player_id, "Base41_Step3", $varg1, $parg1);
        }


    }

    public function argBase41_Step3($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} activates a special base');
        $ret['titleyou'] = clienttranslate('Special base: ${you} must choose the destination base');

        $ret["selected"][]= $parg1;

        $bases_adjacentes_opponent_start = game::$instance->_bases[$this->board_name][$this->opponent_start_base[0]]['adjacents'];

        foreach ($bases_adjacentes_opponent_start as $base)
        {
            $ret["selectable"][] = 'base_'.$this->board_name.'_'.$base;
        }
       

        $ret['buttons'][] = 'btn_cancel';
        

        return $ret;
    }

    public function Base41_Step3($parg1, $parg2, $varg1, $varg2)
    {
        if($varg1 == "btn_cancel")
        {
            game::$instance->addPending($this->player_id, "Base41_Step2", $parg2);
        }

        else
        {
            if($this->player_pref_confirm == 1)
            {

                $couple_base = $parg1.'_'.$varg1;
                game::$instance->addPending($this->player_id, "Base41_Confirm", $couple_base, $parg2);
            }

            if($this->player_pref_confirm == 2)
            {
                $explode1 = explode("_", $parg1);
                $explode2 = explode("_", $varg1);

                $infos_troopmax = self::getObjectListFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_ordre ordre FROM troop WHERE card_location = 'board' AND card_location_arg = '{$explode1[2]}' AND card_ordre = (SELECT MAX(card_ordre) FROM troop WHERE card_location = 'board' AND card_location_arg = '{$explode1[2]}')");
                $compteur_troop_sur_base = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location ='board' AND card_location_arg = '{$explode2[2]}'", true));
    
                game::$instance->troop->moveCard($infos_troopmax[0]['id'], 'board', $explode2[2]);
                self::DbQuery("UPDATE troop set card_ordre = $compteur_troop_sur_base + 1 WHERE card_id = '{$infos_troopmax[0]['id']}'");
    
                game::$instance->notifyAllPlayers(
                    'moveTroopBoardToBoard',
                    clienttranslate('${player_name} moves an opposing troop'),
                    array(
                        'player_name' => $this->player_name,
                        'infos_troop' => $infos_troopmax[0],
                        'base_id' => $explode2[2],
                        'ordre' => $compteur_troop_sur_base + 1,
                        
                    )
                );

                
                game::$instance->addPending($this->player_id, "VerifBase");

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
        $ret['titleyou'] = clienttranslate('Special base: ${you} must confirm');

        $explode = explode("_", $parg1);

        $ret["selected"][] = 'base_'.$this->board_name.'_'.$explode[2];
        $ret["selected"][] = 'base_'.$this->board_name.'_'.$explode[5];


        $ret['buttons'][] = 'btn_yes';
        $ret['buttons'][] = 'btn_no';

               

        return $ret;
    }

    public function Base41_Confirm($parg1, $parg2, $varg1, $varg2)
    {
        if($varg1 == "btn_no")
        {
            game::$instance->addPending($this->player_id, "Base41_Step1", $parg2);
        }

        if($varg1 == "btn_yes")
        {
            $explode = explode("_", $parg1);

            $infos_troopmax = self::getObjectListFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_ordre ordre FROM troop WHERE card_location = 'board' AND card_location_arg = '{$explode[2]}' AND card_ordre = (SELECT MAX(card_ordre) FROM troop WHERE card_location = 'board' AND card_location_arg = '{$explode[2]}')");
            $compteur_troop_sur_base = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location ='board' AND card_location_arg = '{$explode[5]}'", true));

            game::$instance->troop->moveCard($infos_troopmax[0]['id'], 'board', $explode[5]);
            self::DbQuery("UPDATE troop set card_ordre = $compteur_troop_sur_base + 1 WHERE card_id = '{$infos_troopmax[0]['id']}'");

            game::$instance->notifyAllPlayers(
                'moveTroopBoardToBoard',
                clienttranslate('${player_name} moves an opposing troop'),
                array(
                    'player_name' => $this->player_name,
                    'infos_troop' => $infos_troopmax[0],
                    'base_id' => $explode[5],
                    'ordre' => $compteur_troop_sur_base + 1,
                    
                )
            );
            game::$instance->addPending($this->player_id, "VerifBase");
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
         
 
         $ret["selected"][]= 'base_'.$this->board_name.'_'.$parg1;

         $nb_troops_on_discard = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='discard' AND card_type_arg = '{$this->player_id}'", true));
         $counttroophand = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='hand' AND card_type_arg = '{$this->player_id}'", true));

         if(($nb_troops_on_discard >= 1)&&($counttroophand <= 7))
         {
            $ret['titleyou'] = clienttranslate('Special base: ${you} can recover a discarded troop');

            $ret['buttons'][] = 'btn_yes';
            $ret['buttons'][] = 'btn_no';

         }

         else
         {
            $ret['titleyou'] = clienttranslate('Special base: ${you} cannot recover a troop');
            $ret['buttons'][] = 'btn_continue';
         }
 
         
 
         return $ret;
     }
 
     public function Base51_Step1($parg1, $parg2, $varg1, $varg2)
     {
         if(($varg1 == "btn_no")||($varg1 == "btn_continue"))
         {
            game::$instance->addPending($this->player_id, "VerifBase");
         }
 
         if($varg1 == "btn_yes")
         {
            game::$instance->addPending($this->player_id, "Base51_Step2", $parg1);
            
         }
 
 
     }

     public function argBase51_Step2($parg1, $parg2)
     {
         $ret = array();
         $ret["selectable"] = array();
         $ret["selected"] = array();
         $ret['buttons'] = array();
         $ret['title'] = clienttranslate('${actplayer} activates a special base');

         $ret['titleyou'] = clienttranslate('Special base: ${you} must choose a troop');
         
         $troops_discard = self::getObjectListFromDB( "SELECT card_id FROM troop WHERE card_location='discard' AND card_type_arg = '{$this->player_id}'", true );
         foreach ($troops_discard as $troop_discard)
         {
            $ret["selectable"][] = 'troop_'.$troop_discard;
         }

         $ret['buttons'][] = 'btn_cancel';
 
         
 
         return $ret;
     }
 
     public function Base51_Step2($parg1, $parg2, $varg1, $varg2)
     {
         if($varg1 == "btn_cancel")
        {
            game::$instance->addPending($this->player_id, "Base51_Step1", $parg1);
        }
 
         else
        {
            if($this->player_pref_confirm == 1)
            {

                game::$instance->addPending($this->player_id, "Base51_Confirm", $varg1, $parg1);
            }

            if($this->player_pref_confirm == 2)
            {
                $explode_troop = explode("_", $varg1);

                $infos_troop = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_ordre ordre FROM troop WHERE card_id = '{$explode_troop[1]}'");

                game::$instance->troop->moveCard($explode_troop[1],'hand');
                self::DbQuery("UPDATE troop set card_ordre = 1 WHERE card_id = '{$explode_troop[1]}'");

                game::$instance->notifyAllPlayers(
                    'recoverTroopFromDiscard',
                    clienttranslate('${player_name} recovers a discarded troop'),
                    array(
                        'player_name' => $this->player_name,
                        'infos_troop' => $infos_troop,
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
        $ret['titleyou'] = clienttranslate('Special base: ${you} must confirm');

        $ret["selected"][]= $parg1;


        $ret['buttons'][] = 'btn_yes';
        $ret['buttons'][] = 'btn_no';

               

        return $ret;
    }

    public function Base51_Confirm($parg1, $parg2, $varg1, $varg2)
    {
        if($varg1 == "btn_no")
        {
            game::$instance->addPending($this->player_id, "Base51_Step1", $parg2);
        }

        if($varg1 == "btn_yes")
        {
            
            $explode_troop = explode("_", $parg1);

            $infos_troop = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_ordre ordre FROM troop WHERE card_id = '{$explode_troop[1]}'");

            game::$instance->troop->moveCard($explode_troop[1],'hand');
            self::DbQuery("UPDATE troop set card_ordre = 1 WHERE card_id = '{$explode_troop[1]}'");

            game::$instance->notifyAllPlayers(
                'recoverTroopFromDiscard',
                clienttranslate('${player_name} recovers a discarded troop'),
                array(
                    'player_name' => $this->player_name,
                    'infos_troop' => $infos_troop,
                )
            );

            game::$instance->addPending($this->player_id, "VerifBase");

        
        }


    }

    



























}
