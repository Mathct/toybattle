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

        if ($p['player_color'] == "d1553e") {
            $this->player_deck = "deckred";
        }
        if ($p['player_color'] == "4f66a2") {
            $this->player_deck = "deckblue";
        }

        /// PREFERENCE DE CONFIRMATION

        $this->player_pref_confirm = self::getUniqueValueFromDB("SELECT pgp_value FROM bga_user_preferences WHERE pgp_player='{$this->player_id}' AND pgp_preference_id = 100");
    }

    function argNormalTurn($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} must choose an action');
        $ret['titleyou'] = clienttranslate('${you} must choose an action');


        $counttroop = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='{$this->player_deck}'", true));



        if ($counttroop >= 2) {
            $ret['buttons'][] = 'draw_2';
        }

        if ($counttroop == 1) {
            $ret['buttons'][] = 'draw_1';
        }

        // TESTER SI TROUPE DISPO MAIS AUSSI SI ELLES PEUVENT ETRE PLACEES
        $ret['buttons'][] = 'place_troop';



        return $ret;
    }

    function NormalTurn($parg1, $parg2, $varg1, $varg2)
    {

        if (($varg1 == "draw_1") || ($varg1 == "draw_2")) {
            game::$instance->addPending($this->player_id, "Action1");
        }

        if (($varg1 == "place_troop")) {
            game::$instance->addPending($this->player_id, "ChooseTroop");
        }
    }



    function argChooseTroop($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} places a troop');
        $ret['titleyou'] = clienttranslate('${you} must choose a troop');


        $troops = self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_type_arg ='{$this->player_id}' AND card_location='hand'", true);
        foreach ($troops as $troop_id) {
            $ret["selectable"][] = "troop_" . $troop_id;
        }

        $ret['buttons'][] = 'cancel';


        return $ret;
    }

    function ChooseTroop($parg1, $parg2, $varg1, $varg2)
    {
        if ($varg1 == "cancel") {
            game::$instance->addPending($this->player_id, "NormalTurn");
        } else {
            game::$instance->addPending($this->player_id, "ChooseBase", $varg1);
        }
    }

    function argChooseBase($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} places a troop');
        $ret['titleyou'] = clienttranslate('${you} must choose a base');

        $ret["selected"][] = $parg1;

        $tableau_boards_name = ["castle", "pool", "clouds", "jungle", "cemetery", "carribean", "station", "battlefield"];
        $board_name = $tableau_boards_name[game::$instance->getGameStateValue('board') - 1];

        $tableau_bases = game::$instance->_bases[$board_name];
        foreach ($tableau_bases as $base) {

            $ret["selectable"][] = "base_" . $board_name . "_" . $base['value'];
        }

        $ret['buttons'][] = 'cancel';






        return $ret;
    }

    function ChooseBase($parg1, $parg2, $varg1, $varg2)
    {

        if ($varg1 == "cancel") {
            game::$instance->addPending($this->player_id, "ChooseTroop");
        } else {

            if ($this->player_pref_confirm == 1) {
                game::$instance->addPending($this->player_id, "ConfirmPlace", $parg1, $varg1);
            }

            if ($this->player_pref_confirm == 2) {
                $explode_troop = explode("_", $parg1);
                $explode_base = explode("_", $varg1);

                $compteur_troop_sur_base = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location ='board' AND card_location_arg = '{$explode_base[2]}'", true));
                game::$instance->troop->moveCard($explode_troop[1], 'board', $explode_base[2]);
                self::DbQuery("UPDATE troop set card_ordre = $compteur_troop_sur_base + 1 WHERE card_location ='board' AND card_location_arg = '{$explode_base[2]}'");

                $infos_troop = self::getObjectListFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_ordre ordre FROM troop WHERE card_id = '{$explode_troop[1]}'");

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



                    )
                );
            }
        }
    }

    function argConfirmPlace($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} places a troop');
        $ret['titleyou'] = clienttranslate('${you} must confirm');

        $ret["selected"][] = $parg1;
        $ret["selected"][] = $parg2;


        $ret['buttons'][] = 'yes';
        $ret['buttons'][] = 'no';








        return $ret;
    }

    function ConfirmPlace($parg1, $parg2, $varg1, $varg2)
    {
        if ($varg1 == "yes") {
            $explode_troop = explode("_", $parg1);
            $explode_base = explode("_", $parg2);

            $compteur_troop_sur_base = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location ='board' AND card_location_arg = '{$explode_base[2]}'", true));
            game::$instance->troop->moveCard($explode_troop[1], 'board', $explode_base[2]);
            self::DbQuery("UPDATE troop set card_ordre = $compteur_troop_sur_base + 1 WHERE card_location ='board' AND card_location_arg = '{$explode_base[2]}'");

            $infos_troop = self::getObjectListFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_ordre ordre FROM troop WHERE card_id = '{$explode_troop[1]}'");

            game::$instance->notifyAllPlayers(
                'moveTroop',
                clienttranslate('${player_name} moves troop'),
                array(
                    'mobile' =>  $parg1,
                    'parent' => $parg2,
                    'ordre' => $compteur_troop_sur_base + 1,
                    'player_name' => $this->player_name,
                    'player_id' => $this->player_id,
                    'origine' => "hand",
                    'infos_troop' => $infos_troop,

                )
            );

            game::$instance->addPendingFirst($this->player_id, "NormalTurn");
        }

        if ($varg1 == "no") {
            game::$instance->addPending($this->player_id, "NormalTurn");
        }
    }
}
