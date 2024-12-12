<?php

namespace Bga\Games\toybattle;   // ATTENTION NOM DU JEU
use APP_GameClass; // ATTENTION

require_once 'actions/Troops.php';
require_once 'actions/Bases.php';

class Pending extends APP_GameClass
{
    use TroopsTrait; // ATTENTION
    use BasesTrait; // ATTENTION

    public function __construct($player_id)
    {
        $this->player_id = $player_id;
        $p = self::getObjectFromDB("SELECT * FROM player WHERE player_id = {$player_id}");
        $this->player_no = $p['player_no'];
        $this->player_id = $p['player_id'];
        $this->player_name = $p['player_name'];
        $this->player_score = $p['player_score'];
        $this->player_color = $p['player_color'];

        $tableau_boards_name = ["castle", "pool", "clouds", "jungle", "cemetery", "carribean", "station", "battlefield"];
        $this->board_name = $tableau_boards_name[game::$instance->getGameStateValue('board') - 1];
        

        // COLOR A CHANGER SI MODIFICATION DES COULEURS DE BASE DECLAREES DANS GAMEINFOS

        if ($p['player_color'] == "4f66a2") 
        {
            //DECLARATION DU DECK
            $this->player_deck = "deckblue";

            //DECLARATION DU COULEUR
            $this->player_color_title = "blue";
            
            //DECLARATION DES BASES DE DEPART
            if (($this->board_name =='castle')||($this->board_name =='clouds')||($this->board_name =='jungle')||($this->board_name =='cemetery')||($this->board_name =='carribean')||($this->board_name =='station')||($this->board_name =='battlefield'))
            {
            $this->start_base = [1];
            }
            if (($this->board_name =='pool')||($this->board_name =='carribean'))
            {
            $this->start_base = [1,2];
            }

        }

        if ($p['player_color'] == "d1553e") 
        {
            //DECLARATION DU DECK
            $this->player_deck = "deckred";

            //DECLARATION DU COULEUR
            $this->player_color_title = "red";

            //DECLARATION DES BASES DE DEPART
            if (($this->board_name =='castle')||($this->board_name =='clouds')||($this->board_name =='jungle')||($this->board_name =='cemetery')||($this->board_name =='station')||($this->board_name =='battlefield'))
            {
            $this->start_base = [41];
            }

            if ($this->board_name =='pool')
            {
            $this->start_base = [41,42];
            }
        }
        

        /// PREFERENCE DE CONFIRMATION

        $this->player_pref_confirm = self::getUniqueValueFromDB("SELECT pgp_value FROM bga_user_preferences WHERE pgp_player='{$this->player_id}' AND pgp_preference_id = 100");

        /// PREFERENCE DE DISCARD

        $this->player_pref_discard = self::getUniqueValueFromDB("SELECT pgp_value FROM bga_user_preferences WHERE pgp_player='{$this->player_id}' AND pgp_preference_id = 101");
    }







    function argNormalTurn($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} must choose an action');
        $ret['titleyou'] = clienttranslate('${you} must choose an action');


        $counttroopdeck = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='{$this->player_deck}'", true));
        $counttroophand = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='hand' AND card_type_arg = '{$this->player_id}'", true));



        if (($counttroopdeck >= 2) && ($counttroophand <= 6)) 
        {
            $ret['buttons'][] = 'btn_draw_2';
        }

        if ((($counttroopdeck == 1) && ($counttroophand <= 7)) || (($counttroopdeck >= 1) && ($counttroophand == 7))) 
        {

            $ret['buttons'][] = 'btn_draw_1';
        }



        if ($counttroophand >= 1) 
        {
            $place_ok = 0;
            $list_troop = self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='hand' AND card_type_arg = '{$this->player_id}'", true);
            foreach ($list_troop as $troop)
            {
                $troop_id = 'troop_'.$troop;
                $possible_base = game::$instance->getPossibleBase($this->start_base, $troop_id, $this->player_id);
                if(count($possible_base) >= 1)
                {
                    $place_ok = 1;
                }


            }
            // TESTER SI TROUPE DISPO MAIS AUSSI SI ELLES PEUVENT ETRE PLACEES
            if ($place_ok == 1)
            {
                $ret['buttons'][] = 'btn_place_troop';
            }
            
        }



        return $ret;
    }

    function NormalTurn($parg1, $parg2, $varg1, $varg2)
    {

        if ((($varg1 == "btn_draw_1") || ($varg1 == "btn_draw_2")) && ($this->player_pref_confirm == 1)) 
        {
            game::$instance->addPending($this->player_id, "ConfirmDraw", $varg1);
        }

        if ((($varg1 == "btn_draw_1") || ($varg1 == "btn_draw_2")) && ($this->player_pref_confirm == 2)) 
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

            game::$instance->addPendingFirst($this->player_id, "NormalTurn");
        }

        if (($varg1 == "btn_place_troop")) 
        {
            game::$instance->addPending($this->player_id, "ChooseTroop");
        }

        if ($varg1 == null) 
        {
            game::$instance->addPending($this->player_id, "FinGame");
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



        $list_troop = self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='hand' AND card_type_arg = '{$this->player_id}'", true);
            foreach ($list_troop as $troop)
            {
                $troop_id = 'troop_'.$troop;
                $possible_base = game::$instance->getPossibleBase($this->start_base, $troop_id, $this->player_id);
                if(count($possible_base) >= 1)
                {
                    $ret["selectable"][] = $troop_id;
                }


            }

        

        $ret['buttons'][] = 'btn_cancel';


        return $ret;
    }

    function ChooseTroop($parg1, $parg2, $varg1, $varg2)
    {
        if ($varg1 == "btn_cancel") 
        {
            game::$instance->addPending($this->player_id, "NormalTurn");
        } 
        
        else 
        {
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
        

             

        $ret["selected"][] = $parg1;

                
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

            $ret["selectable"][] = "base_" . $this->board_name . "_" . $base;
        }
        
        
        
        $ret['buttons'][] = 'btn_cancel';


        return $ret;
    }

    function ChooseBase($parg1, $parg2, $varg1, $varg2)
    {

        if ($varg1 == "btn_cancel") 
        {
            game::$instance->addPending($this->player_id, "ChooseTroop");
        } 
        
        else 
        {

            if ($this->player_pref_confirm == 1) 
            {
                game::$instance->addPending($this->player_id, "ConfirmPlace", $parg1, $varg1);
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
                    clienttranslate('${player_name} places troop'),
                    array(
                        'base_id' => $varg1,
                        'ordre' => $compteur_troop_sur_base + 1,
                        'player_name' => $this->player_name,
                        'player_id' => $this->player_id,
                        'origine' => "hand",
                        'infos_troop' => $infos_troop,
                        'nb_troops_hand' => $nb_troops_hand


                    )
                );

                $force_troop = self::getUniqueValueFromDB("SELECT card_type FROM troop WHERE card_id = '{$explode_troop[1]}'") %10;
                $numero_base = $explode_base[2];

                game::$instance->addPending($this->player_id, "VerifTroop", $force_troop, $numero_base);
            }
        }
    }












    //////////////////// ACTION WITH CONFIRM PREF /////////////////////////////

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


        $ret['buttons'][] = 'btn_yes';
        $ret['buttons'][] = 'btn_no';

        return $ret;
    }

    function ConfirmPlace($parg1, $parg2, $varg1, $varg2)
    {
        if ($varg1 == "btn_yes") {
            $explode_troop = explode("_", $parg1);
            $explode_base = explode("_", $parg2);

            $compteur_troop_sur_base = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location ='board' AND card_location_arg = '{$explode_base[2]}'", true));

            $nb_troops_hand = self::getUniqueValueFromDB("SELECT COUNT(card_id) FROM troop WHERE card_location = 'hand' AND card_type_arg = '{$this->player_id}'");

            game::$instance->troop->moveCard($explode_troop[1], 'board', $explode_base[2]);
            self::DbQuery("UPDATE troop set card_ordre = $compteur_troop_sur_base + 1 WHERE card_id = '{$explode_troop[1]}'");

            $infos_troop = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_ordre ordre FROM troop WHERE card_id = '{$explode_troop[1]}'");


            game::$instance->notifyAllPlayers(
                'moveTroop',
                clienttranslate('${player_name} places troop'),
                array(
                    
                    'base_id' => $parg2,
                    'ordre' => $compteur_troop_sur_base + 1,
                    'player_name' => $this->player_name,
                    'player_id' => $this->player_id,
                    'origine' => "hand",
                    'infos_troop' => $infos_troop,
                    'nb_troops_hand' => $nb_troops_hand
                )
            );

            

            $force_troop = self::getUniqueValueFromDB("SELECT card_type FROM troop WHERE card_id = '{$explode_troop[1]}'") %10;
            $numero_base = $explode_base[2];

            game::$instance->addPending($this->player_id, "VerifTroop", $force_troop, $numero_base);
        }

        if ($varg1 == "btn_no") {
            game::$instance->addPending($this->player_id, "NormalTurn");
        }
    }


    function argConfirmDraw($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} draws troops');
        $ret['titleyou'] = clienttranslate('${you} must confirm');


        $ret['buttons'][] = 'btn_yes';
        $ret['buttons'][] = 'btn_no';


        return $ret;
    }

    function ConfirmDraw($parg1, $parg2, $varg1, $varg2)
    {
        if ($varg1 == "btn_yes") {

            $nb_troops_hand = self::getUniqueValueFromDB("SELECT COUNT(card_id) FROM troop WHERE card_location = 'hand' AND card_type_arg = '{$this->player_id}'");
            $old_troops = self::getObjectListFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_ordre ordre FROM troop WHERE card_location = 'hand' AND card_type_arg ='{$this->player_id}'");


            if ($parg1 == 'btn_draw_2') 
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

            if ($parg1 == 'btn_draw_1') 
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


            game::$instance->addPendingFirst($this->player_id, "NormalTurn");
        }

        if ($varg1 == "btn_no") {
            game::$instance->addPending($this->player_id, "NormalTurn");
        }
    }



















    /// FONCTION FIN DE GAME CAR PLUS DE TROOP DANS LE DECK ET PLUS DE TROOP DANS LA MAIN////

    function argFinGame($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('END GAME: ${actplayer} ne peut plus jouer car plus de troupes dans son deck et aucune troupe jouable');
        $ret['titleyou'] = clienttranslate('END GAME: ${you} ne pouvez plus jouer: plus de troupes le deck et plus aucune troupe jouable');


        $ret['buttons'][] = 'btn_pass';



        return $ret;
    }

    function FinGame($parg1, $parg2, $varg1, $varg2)
    {
        if ($varg1 == "btn_pass") {

            game::$instance->addPendingFirst($this->player_id, "NormalTurn");
        }
    }
}
