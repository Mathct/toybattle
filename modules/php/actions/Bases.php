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
          
       
        if (($this->player_name == "Backstar0") || ($this->player_name == "Backstar1")) 
        {
                                   
            $explode_check_bases = explode("_", $parg1);
            
                        
            if ($explode_check_bases[0] != null)
            {
                $numero_power = game::$instance->_bases[$this->board_name][$explode_check_bases[0]]['power'];

                if($numero_power != 0)
                {
                    $base_with_power = 'base_'.$this->board_name.'_'.$explode_check_bases[0];



                    // Supprimer le premier élément
                    array_shift($explode_check_bases);
                    // Réindexer le tableau
                    $check_bases_restantes = array_values($explode_check_bases);
                  
                    $bases_restantes_a_checker = game::$instance->joinValues($check_bases_restantes);

                    
                    ///////////////////////////////////////////////////
                    /// !!!!!!!!!!!!!!! ENVOYER VERS POWER BASE avec $bases_restantes_a_checker
                    ///////////////////////////////////////////////////

                    if (($numero_power >=10)&&($numero_power <=19))
                    {
                        game::$instance->addPending($this->player_id, "Base11_Step1", $base_with_power, $bases_restantes_a_checker);
                    }

                    if ($numero_power >= 20)
                    {
                        game::$instance->addPending($this->player_id, "VerifBase", $bases_restantes_a_checker);
                    }
                    
                }
                else
                {
                    
                    // Supprimer le premier élément
                    array_shift($explode_check_bases);
                    // Réindexer le tableau
                    $check_bases_restantes = array_values($explode_check_bases);

                    $bases_restantes_a_checker = game::$instance->joinValues($check_bases_restantes);

                    game::$instance->addPending($this->player_id, "VerifBase", $bases_restantes_a_checker);
                    
                }

                
                
            }

            else
            {
                game::$instance->addPendingFirst($this->player_id, "NormalTurn");
                
            }

                    
        }

        
        else {
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

        $ret["selected"][]= $parg1;

        $ret['buttons'][] = 'btn_yes';
        $ret['buttons'][] = 'btn_no';

        return $ret;
    }

    public function Base11_Step1($parg1, $parg2, $varg1, $varg2)
    {
        if($varg1 == "btn_no")
        {
        game::$instance->addPending($this->player_id, "VerifBase", $parg2);
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
        $ret['titleyou'] = clienttranslate('Special base: ${you} must choose a troop to recover');

        $all_bases = game::$instance->_bases[$this->board_name];
        $all_bases_a_checker = array_map('strval', array_keys($all_bases));
        $all_bases_sans_QG = [];

        foreach ($all_bases_a_checker as $allbase){
            if (($allbase >=10)&&($allbase <=40))
            {
                $all_bases_sans_QG[] = $allbase;
            }
        }
        foreach ($all_bases_sans_QG as $base_sans_QG)
        {
            $count_troop_on_base = count(self::getObjectListFromDB( "SELECT card_id FROM troop WHERE card_location = 'board' AND card_location_arg = '{$base_sans_QG}'", true ));
            if($count_troop_on_base >=1)
            {
                $infos_troopmax = self::getObjectListFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_ordre ordre FROM troop WHERE card_location = 'board' AND card_location_arg = '{$base_sans_QG}' AND card_ordre = (SELECT MAX(card_ordre) FROM troop WHERE card_location = 'board' AND card_location_arg = '{$base_sans_QG}')");

                if ($infos_troopmax[0]['type_arg'] == $this->player_id) // si elle appartient au joueur actif
                    {
                        $ret["selectable"][] = 'base_'.$this->board_name.'_'.$base_sans_QG;
                    }
            }
        }

        $ret['buttons'][] = 'btn_cancel';

        return $ret;
    }

    public function Base11_Step2($parg1, $parg2, $varg1, $varg2)
    {
        if($varg1 == "btn_cancel")
        {
        game::$instance->addPending($this->player_id, "Base11_Step1", $parg1, $parg2);
        }
        else
        {
             ///// ATTENTION SI LE JOUEUR ENLEVE UNE TROOP SUR UNE BASE QUI DOIT ETRE DECLECHEE APRES ($parg2)
            game::$instance->addPending($this->player_id, "VerifBase", $parg2);
        }

       

    }



























}
