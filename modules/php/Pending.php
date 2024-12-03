<?php

namespace Bga\Games\toybattle;   // ATTENTION NOM DU JEU
use APP_GameClass; // ATTENTION

require_once 'troupes/ActionsTroupes.php'; // Inclure le fichier contenant les fonctions

class Pending extends APP_GameClass
{
    use ActionsTroupesTrait; // ATTENTION

    public function __construct($player_id)
    {
        $this->player_id = $player_id;
        $p = self::getObjectFromDB("SELECT * FROM player WHERE player_id = {$player_id}");        
        $this->player_no = $p['player_no'];
        $this->player_id = $p['player_id'];
        $this->player_name = $p['player_name'];
        $this->player_score = $p['player_score'];
        //$this->player_color = $p['player_color'];

        // COLOR A CHANGER SI MODIFICATION DES COULEURS DE BASE DECLAREES DANS GAMEINFOS

        if ($p['player_color'] == "ff0000")
        {
            $this->player_color = "red";
        }
        if ($p['player_color'] == "0080ff")
        {
            $this->player_color = "blue";
        }
    }
    
    function argNormalTurn($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} blabla2');
        $ret['titleyou'] = clienttranslate('${you} blabla1');


        $ret["selectable"][]='carreid1';
        
        $ret['buttons'][]='cancel';
        $ret['buttons'][]='pass';
        
        return $ret;
    }

    function NormalTurn($parg1, $parg2, $varg1, $varg2)
    {
        if($varg1 == "cancel")
        {
            game::$instance->addPending($this->player_id, "NormalTurn");  
        }
        if(($varg1 == "pass")||($varg1 == "carreid1"))
        {
            game::$instance->addPending($this->player_id, "Action1"); 
        }
    }
}