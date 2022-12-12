<?php
    error_reporting(E_ERROR | E_PARSE);
    set_time_limit(3600);

    include_once "../../libs/HTML2PDF/Html2Pdf.php";
    include_once "../classes.php";

    function getAllRegists() {
        $db = new DBSystem();
        $assist = new AssistSystem();

        $curses = array('1°1', '1°2', '1°3', '2°1', '2°2', '2°3', '3°1', '3°2', '3°3', '4°1', '4°2', '4°3', '5°1', '5°2', '5°3', '6°1', '6°2', '6°3', '7°1', '7°2', '7°3');
        $arrayData = array();

        $curseGet = base64_encode(utf8_decode($curses[0]));
        $consult = $db->Query("SELECT * FROM `groups` WHERE `status`='1' AND `curse`='$curseGet'");
        if ($consult) {
            // # Recoleccion de datos
            $age = "";
            while ($data = $consult->fetch_array()) {
                if ($age == "") $age = explode('/', base64_decode($data['date']))[2];
                $datasGroup = $assist->get2_system($data['id']);
                if ($datasGroup["ok"]) array_push($arrayData, array(
                    "group" => $data,
                    "assist" => $datasGroup["datas"]
                ));
            }

            // # Guardar en JSON los datos
            $dir = "../../olds/$age";
            $file = "$dir/data.json";
            if (!is_dir($dir)) mkdir($dir);
            if (!file_exists($file)) {
                $filejson = fopen($file, "w");
                fwrite($filejson, json_encode($arrayData));
            }

            // # Convertir en PDF
            $daysInMonth = cal_days_in_month(CAL_EASTER_DEFAULT, 12, 2022);
            $months = array(); // { month: string; datas: any; }

            // Separar los datos por mes
            foreach ($arrayData as $data) {
                $dMonth = explode('/', base64_decode($data['group']['date']))[1];
                $find = array_search($dMonth, array_column($months, "month"));
                if ($find !== false) {
                    array_push($months[$find]['datas'], $data);
                } else {
                    array_push($months, array(
                        "month" => $dMonth,
                        "datas" => array($data)
                    ));
                }
            }
            


        }
    }

    function ConvertAllRegistsInPDF() {
        
    }
    getAllRegists();
?>