<?php
    error_reporting(E_ERROR | E_PARSE);
    set_time_limit(3600);

    include_once "../../libs/HTML2PDF/Html2Pdf.php";
    include_once "../classes.php";

    function getAllRegists() {
        $db = new DBSystem();
        $assist = new AssistSystem();

        $curses = array('1°1', '1°2', '1°3', '2°1', '2°2', '2°3', '3°1', '3°2', '3°3', '4°1', '4°2', '4°3', '5°1', '5°2', '5°3', '6°1', '6°2', '6°3', '7°1', '7°2', '7°3');
        $totalData = array();

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
            /*foreach ($arrayData as $data) {
                $dGroup = explode('/', base64_decode($data['group']['date']));
                $find = array_search($dGroup[1], array_column($months, "month"));
                if ($find !== false) {
                    array_push($months[$find]['datas'], $data['assist']);
                } else {
                    array_push($months, array(
                        "date" => base64_decode($data['group']['date']),
                        "hour" => base64_decode($data['group']['hour']),
                        "datas" => $data['assist']
                    ));
                }
            }*/

            // Separar los datos por fecha
            foreach ($arrayData as $data) {
                array_push($months, array(
                    "date" => base64_decode($data['group']['date']),
                    "hour" => base64_decode($data['group']['hour']),
                    "datas" => $data['assist']
                ));
            }
            
            // Recorrer los meses
            $list = array();
            foreach ($months as $eMonth) {
                $p_date = explode('/', $eMonth['date']);
                $dayNumber = $p_date[0];

                // Verificar si exitiste el mes o crearlo.
                $findMonth = array_search($p_date[1], array_column($list, 'month'));
                if ($findMonth === false) {
                    array_push($list, array(
                        'month' => $p_date[1],
                        'list' => array()
                    ));
                    $findMonth = array_search($p_date[1], array_column($list, 'month'));
                }

                // Recorrer datos de los meses
                foreach ($eMonth['datas'] as $value) {
                    $status = ($value['status'])? 'P': 'A';
                    $nameDecode = utf8_encode(base64_decode($value['name']));
                    // Buscar al alumno en la lista
                    $findStd = array_search($value['id'], array_column($list[$findMonth]['list'], 'id'));
                    if ($findStd === false) {
                        array_push($list[$findMonth]['list'], array(
                            'id' => $value['id'],
                            'student' => $nameDecode,
                            'list' => array(array(
                                'day' => $dayNumber,
                                'text' => $status
                            ))
                        ));
                    } else {
                        // Buscardo si el dia se repite en la lista del estudiante
                        $findDay = array_search($dayNumber, array_column($list[$findMonth]['list'][$findStd]['list'], 'day'));
                        if ($findDay !== false) {
                            $tmp_text = $list[$findMonth]['list'][$findStd]['list'][$findDay]['text'];
                            $list[$findMonth]['list'][$findStd]['list'][$findDay]['text'] = "$tmp_text/$status";
                        } else {
                            array_push($list[$findMonth]['list'][$findStd]['list'], array(
                                'day' => $dayNumber,
                                'text' => $status
                            ));
                        }
                    }
                }
            }

            // Completar dias faltantes
            foreach ($list as $index0 => $mth) {
                foreach ($mth['list'] as $index => $st) {
                    for ($i=0; $i < $daysInMonth; $i++) {
                        $dayNumber = strval($i + 1);
                        if (strlen($dayNumber) == 1) $dayNumber = "0$dayNumber";
                        if (array_search($dayNumber, array_column($st['list'], 'day')) === false) {
                            array_push($list[$index0]['list'][$index]['list'], array(
                                'day' => $dayNumber,
                                'text' => '~'
                            ));
                        }
                    }
                }
            }
            
            // Ordenar lista por nombre de alumnos
            function orderListForName($a, $b) {
                $nameA = strtolower($a['student']);
                $nameB = strtolower($b['student']);
                return strcmp($nameA, $nameB);
            }
            for ($i=0; $i < count($list); $i++) {
                usort($list[$i]['list'], 'orderListForName');
            }
            
            // Ordenar listas por dia
            function orderListForDay($a, $b) {
                $iA = intval($a['day']);
                $iB = intval($b['day']);
                return $iA - $iB;
            }
            for ($i=0; $i < count($list); $i++) {
                for ($e=0; $e < count($list[$i]['list']); $e++) { 
                    $tmp_list = $list[$i]['list'][$e]['list'];
                    usort($tmp_list, 'orderListForDay');
                    $list[$i]['list'][$e]['list'] = $tmp_list;
                }
            }

            return $list;
        }
    }

    function ConvertAllRegistsInPDF() {
        $lists = getAllRegists();
        
    }
    ConvertAllRegistsInPDF();
?>