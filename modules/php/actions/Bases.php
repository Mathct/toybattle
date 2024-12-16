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
                    if($numero_power == 11)
                    {
                        game::$instance->addPending($this->player_id, "Base11_Step1", $troop_id, $base);
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
                game::$instance->addPending($this->player_id, "ConfirmBase11", $varg1, $duo_check);
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

    function argConfirmBase11($parg1, $parg2)
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

    function ConfirmBase11($parg1, $parg2, $varg1, $varg2)
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



























}
