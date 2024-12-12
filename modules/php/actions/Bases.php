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
            var_dump('debut de vefif', $parg1);
                        
            if ($explode_check_bases[0] != null)
            {
                var_dump('check', $explode_check_bases[0]);
                $numero_power = game::$instance->_bases[$this->board_name][$explode_check_bases[0]]['power'];

                if($numero_power != 0)
                {
                    // Supprimer le premier élément
                    array_shift($explode_check_bases);
                    // Réindexer le tableau
                    $check_bases_restantes = array_values($explode_check_bases);

                   
                    $parg1 = game::$instance->joinValues($check_bases_restantes);

                    
                                    
                    //ALLER SUR BASE SPECIALE avec $bases_restantes
                    var_dump('aller sur base speciale power:', $numero_power);
                    var_dump('base restantes:', $parg1);
                    

                    game::$instance->addPending($this->player_id, "VerifBase", $parg1);
                }
                else
                {
                    // Supprimer le premier élément
                    array_shift($explode_check_bases);
                    // Réindexer le tableau
                    $check_bases_restantes = array_values($explode_check_bases);

                    

                    $parg1 = game::$instance->joinValues($check_bases_restantes);

                    game::$instance->addPending($this->player_id, "VerifBase", $parg1);
                    
                }

                
                
            }

            else
            {
                game::$instance->addPending($this->player_id, "NormalTurn");
            }

                    
        }

        
        else {
            game::$instance->addPendingFirst($this->player_id, "NormalTurn");
        }

        
    }

    public function argBase1($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} pass');
        $ret['titleyou'] = clienttranslate('${you} pass');

        $ret['buttons'][] = 'btn_pass';

        return $ret;
    }

    public function Base1($parg1, $parg2, $varg1, $varg2)
    {
        game::$instance->addPendingFirst($this->player_id, "NormalTurn");
    }
}
