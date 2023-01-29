<?php
    error_reporting(E_ERROR | E_PARSE);
    include_once "../classes.php";

    function getNextCurse(string $curse) {
        $year = date('Y');
        $curses = array(
            '1°1' => base64_encode(utf8_decode('2°1')),
            '1°2' => base64_encode(utf8_decode('2°2')),
            '1°3' => base64_encode(utf8_decode('2°3')),
            '2°1' => base64_encode(utf8_decode('3°1')),
            '2°2' => base64_encode(utf8_decode('3°2')),
            '2°3' => base64_encode(utf8_decode('3°3')),
            '3°1' => base64_encode(utf8_decode('4°1')),
            '3°2' => base64_encode(utf8_decode('4°2')),
            '3°3' => base64_encode(utf8_decode('4°3')),
            '4°1' => base64_encode(utf8_decode('5°1')),
            '4°2' => base64_encode(utf8_decode('5°2')),
            '4°3' => base64_encode(utf8_decode('5°3')),
            '5°1' => base64_encode(utf8_decode('6°1')),
            '5°2' => base64_encode(utf8_decode('6°2')),
            '5°3' => base64_encode(utf8_decode('6°3')),
            '6°1' => base64_encode(utf8_decode('7°1')),
            '6°2' => base64_encode(utf8_decode('7°2')),
            '6°3' => base64_encode(utf8_decode('7°3')),
            '7°1' => base64_encode("Egresados $year"),
            '7°2' => base64_encode("Egresados $year"),
            '7°3' => base64_encode("Egresados $year")
        );
        return $curses[$curse];
    }

    function upgradeAllStudentsForCurse() {
        $db = new DBSystem();
        $curses = array(base64_encode('1°1'), base64_encode('1°2'), base64_encode('1°3'), base64_encode('2°1'), base64_encode('2°2'), base64_encode('2°3'), base64_encode('3°1'), base64_encode('3°2'), base64_encode('3°3'), base64_encode('4°1'), base64_encode('4°2'), base64_encode('4°3'), base64_encode('5°1'), base64_encode('5°2'), base64_encode('5°3'), base64_encode('6°1'), base64_encode('6°2'), base64_encode('6°3'), base64_encode('7°1'), base64_encode('7°2'), base64_encode('7°3'));
        $curses = array_reverse($curses);
        foreach ($curses as $curse) {
            $next = getNextCurse(base64_decode($curse));
            $current = base64_encode(utf8_decode(base64_decode($curse)));
            $db->Query("UPDATE `students` SET `curse`='$next' WHERE `curse`='$current'");
            //echo base64_decode($curse)." to ".utf8_encode(base64_decode($next)).": ".(($consult)? "true": "false")."<br>";
        }
    }
?>