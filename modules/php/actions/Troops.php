<?php

namespace Bga\Games\toybattle; // ATTENTION

trait TroopsTrait  // ATTENTION
{
    
    public function argVerifTroop($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} places a troop');
        $ret['titleyou'] = clienttranslate('${you} place a troop');

        return $ret;
    }

    public function VerifTroop($parg1, $parg2, $varg1, $varg2)
    {
        
        
        if($parg1 == 1)
        {
            game::$instance->addPending($this->player_id, "Troop1_Step1", $parg2);
        }

        else
        {
            game::$instance->addPending($this->player_id, "VerifBase", $parg2);
        }
    }
    
    public function argTroop1_Step1($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} places a troop');

        $counttroopdeck = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='{$this->player_deck}'", true));
        $counttroophand = count(self::getObjectListFromDB("SELECT card_id FROM troop WHERE card_location='hand' AND card_type_arg = '{$this->player_id}'", true));

        if (($counttroopdeck >= 2) && ($counttroophand <= 6)) 
        {
            $ret['titleyou'] = clienttranslate('SKULLY: ${you} can draw 2 troops');
            $ret['buttons'][] = 'btn_draw_2';
            $ret['buttons'][] = 'btn_no';
            
        }

        if ((($counttroopdeck == 1) && ($counttroophand <= 7))||(($counttroopdeck >= 1) && ($counttroophand == 7)))
        {
            $ret['titleyou'] = clienttranslate('SKULLY: ${you} can draw 1 troop');
            $ret['buttons'][] = 'btn_draw_1';
            $ret['buttons'][] = 'btn_no';
        }

        
        if ($counttroopdeck == 0)
        {
            $ret['titleyou'] = clienttranslate('SKULLY: ${you} cannot draw troops');
            $ret['buttons'][] = 'btn_continue';
        }
        
        
        return $ret;
    }

    public function Troop1_Step1($parg1, $parg2, $varg1, $varg2)
    {
        if ((($varg1 == "btn_draw_1") || ($varg1 == "btn_draw_2"))) 
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

            
        }


        game::$instance->addPending($this->player_id, "VerifBase", $parg1);
    }


    public function argTroop2_Step1($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} places a troop');

        $ret['titleyou'] = clienttranslate('CAP\'TAINE: ${you} can draw 2 troops');

            
        
        return $ret;
    }

    public function Troop2_Step1($parg1, $parg2, $varg1, $varg2)
    {
        
        game::$instance->addPending($this->player_id, "VerifBase", $parg1);
    }




    public function argTroop3_Step1($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} places a troop');

        $ret['titleyou'] = clienttranslate('Skully: ${you} can draw 2 troops');

                
        
        return $ret;
    }

    public function Troop3_Step1($parg1, $parg2, $varg1, $varg2)
    {
        


        game::$instance->addPending($this->player_id, "VerifBase", $parg1);
    }








}