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
        if(($this->player_name = "Backstar0")||($this->player_name = "Backstar1")) //etc en fonction du nombre de joueurs
        {
            var_dump('base_a_activer',$parg1);
        }
        
        game::$instance->addPendingFirst($this->player_id, "NormalTurn");
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