<?php

namespace Bga\Games\toybattle; // ATTENTION

trait ActionsTrait  // ATTENTION
{
    public function argAction1($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} pass');
        $ret['titleyou'] = clienttranslate('${you} pass');

        $ret['buttons'][] = 'pass';

        return $ret;
    }

    public function Action1($parg1, $parg2, $varg1, $varg2)
    {
        game::$instance->addPendingFirst($this->player_id, "NormalTurn");
    }
}