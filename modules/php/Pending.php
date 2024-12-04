<?php

namespace Bga\Games\toybattle;   // ATTENTION NOM DU JEU
use APP_GameClass; // ATTENTION

require_once 'actions/Actions.php'; 

class Pending extends APP_GameClass
{
    use ActionsTrait; // ATTENTION

    public function __construct($player_id)
    {
        $this->player_id = $player_id;
        $p = self::getObjectFromDB("SELECT * FROM player WHERE player_id = {$player_id}");        
        $this->player_no = $p['player_no'];
        $this->player_id = $p['player_id'];
        $this->player_name = $p['player_name'];
        $this->player_score = $p['player_score'];
        $this->player_color = $p['player_color'];

        // COLOR A CHANGER SI MODIFICATION DES COULEURS DE BASE DECLAREES DANS GAMEINFOS

        if ($p['player_color'] == "d1553e")
        {
            $this->player_deck = "deckred";
        }
        if ($p['player_color'] == "4f66a2")
        {
            $this->player_deck = "deckblue";
        }
    }
    
    function argNormalTurn($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} blabla2');
        $ret['titleyou'] = clienttranslate('${you} blabla1');
        

        $counttroop = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='{$this->player_deck}'", true));

                       
        if ($counttroop >= 2)
        {
            $ret['buttons'][]='draw_2';
        }

        if ($counttroop == 1)
        {
            $ret['buttons'][]='draw_1';
        }

        // TESTER SI TROUPE DISPO MAIS AUSSI SI ELLES PEUVENT ETRE PLACEES
        $ret['buttons'][]='place_troop';

    
                
        return $ret;
    }

    function NormalTurn($parg1, $parg2, $varg1, $varg2)
    {
       
        if(($varg1 == "draw_1")||($varg1 == "draw_2")||($varg1 == "place_troop"))
        {
            game::$instance->addPending($this->player_id, "Action1"); 
        }
    }
}