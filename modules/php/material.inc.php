<?php


$this->_bases = [
    "castle" => [
        1  => ["value" => 1,  "adjacents" => [11, 12],         "power" => 0],
        11 => ["value" => 11, "adjacents" => [1, 13],          "power" => 0],
        12 => ["value" => 12, "adjacents" => [1, 14],          "power" => 0],
        13 => ["value" => 13, "adjacents" => [11, 15, 17, 18], "power" => 0],
        14 => ["value" => 14, "adjacents" => [12, 16, 18, 19], "power" => 0],
        15 => ["value" => 15, "adjacents" => [13, 17],         "power" => 11],
        16 => ["value" => 16, "adjacents" => [14, 19],         "power" => 11],
        17 => ["value" => 17, "adjacents" => [13, 15, 20, 22], "power" => 0],
        18 => ["value" => 18, "adjacents" => [13, 14, 22, 23], "power" => 0],
        19 => ["value" => 19, "adjacents" => [14, 16, 21, 23], "power" => 0],
        20 => ["value" => 20, "adjacents" => [17, 22],         "power" => 11],
        21 => ["value" => 21, "adjacents" => [19, 23],         "power" => 11],
        22 => ["value" => 22, "adjacents" => [17, 18, 20, 24], "power" => 0],
        23 => ["value" => 23, "adjacents" => [18, 19, 21, 25], "power" => 0],
        24 => ["value" => 24, "adjacents" => [22, 41],         "power" => 0],
        25 => ["value" => 25, "adjacents" => [23, 41],         "power" => 0],
        41 => ["value" => 41, "adjacents" => [24, 25],         "power" => 0],
    ],


];

$this->_zones = [
    "castle" => [
        1 => ["value" => 1, "bases" => [1, 11, 12, 13, 14, 18],  "medals" => 3],
        2 => ["value" => 2, "bases" => [13, 15, 17],             "medals" => 1],
        3 => ["value" => 3, "bases" => [14, 16, 19],             "medals" => 1],
        4 => ["value" => 4, "bases" => [13, 17, 18, 22],         "medals" => 2],
        5 => ["value" => 5, "bases" => [14, 18, 19, 23],         "medals" => 2],
        6 => ["value" => 6, "bases" => [17, 20, 22],             "medals" => 1],
        7 => ["value" => 7, "bases" => [19, 21, 23],             "medals" => 1],
        8 => ["value" => 8, "bases" => [18, 22, 23, 24, 25, 41], "medals" => 3],
    ],


];

$this->_powers = [[], [11], [21, 23, 26], [31],  [41], [51], [], [], [81]];

$this->_medals_to_win = [-1, 7, 6, 8, 7, 7, 5, 7, 8];
