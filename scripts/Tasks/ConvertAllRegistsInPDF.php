<?php
    error_reporting(E_ERROR | E_PARSE);
    set_time_limit(18000);

    require_once("../../libs/HTML2PDF/Html2Pdf.php");
    //require_once "../../libs/dompdf/autoload.inc.php";

    include_once "../classes.php";
    include_once "GetRegistsHTML.php";

    // Functions ASort
    function orderListForName($a, $b) {
        $nameA = strtolower($a['student']);
        $nameB = strtolower($b['student']);
        return strcmp($nameA, $nameB);
    }
    function orderListForDay($a, $b) {
        $iA = intval($a['day']);
        $iB = intval($b['day']);
        return $iA - $iB;
    }


    function getAllRegists() {
        $db = new DBSystem();
        $assist = new AssistSystem();

        $curses = array('1°1', '1°2', '1°3', '2°1', '2°2', '2°3', '3°1', '3°2', '3°3', '4°1', '4°2', '4°3', '5°1', '5°2', '5°3', '6°1', '6°2', '6°3', '7°1', '7°2', '7°3', 'docentes');
        $totalData = array();
        $age = "";

        foreach ($curses as $curseName) {
            $arrayData = array();
            $curseGet = base64_encode(utf8_decode($curseName));
            $consult = $db->Query("SELECT * FROM `groups` WHERE `status`='1' AND `curse`='$curseGet'");
            if ($consult) {
                // # Recoleccion de datos
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
                $processCurse = str_replace("°", "-", $curseName);
                $file = "$dir/data/data_$processCurse.json";
                if (!is_dir($dir)) mkdir($dir);
                if (!is_dir("$dir/data")) mkdir("$dir/data");
                if (!file_exists($file)) {
                    $filejson = fopen($file, "w");
                    fwrite($filejson, json_encode($arrayData));
                }

                // # Procesar los datos
                $months = array(); // { month: string; datas: any; }

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
                            'age' => $p_date[2],
                            'status' => array(
                                'assist' => 0,
                                'notAssist' => 0
                            ),
                            'list' => array()
                        ));
                        $findMonth = array_search($p_date[1], array_column($list, 'month'));
                    }

                    // Recorrer datos de los meses
                    foreach ($eMonth['datas'] as $value) {
                        $status = ($value['status'])? 'P': 'A';
                        if ($value['status']) $list[$findMonth]['status']['assist'] += 1; else $list[$findMonth]['status']['notAssist'] += 1;
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
                    $daysInMonth = cal_days_in_month(CAL_EASTER_DEFAULT, intval($mth['month']), intval($mth['age']));
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
                for ($i=0; $i < count($list); $i++) {
                    usort($list[$i]['list'], 'orderListForName');
                }
                
                // Ordenar listas por dia
                for ($i=0; $i < count($list); $i++) {
                    for ($e=0; $e < count($list[$i]['list']); $e++) { 
                        $tmp_list = $list[$i]['list'][$e]['list'];
                        usort($tmp_list, 'orderListForDay');
                        $list[$i]['list'][$e]['list'] = $tmp_list;
                    }
                }

                // # Guardar datos procesados
                if (!is_dir("$dir/data_process")) mkdir("$dir/data_process");
                $file2 = "$dir/data_process/data_process_$processCurse.json";
                if (!file_exists($file2)) {
                    $filejson = fopen($file2, "w");
                    fwrite($filejson, json_encode($list));
                }

                // # Añadir listado a la variable total
                array_push($totalData, array(
                    'curse' => $curseName,
                    'data' => $list
                ));
            }
        }
        return $totalData;
    }

    function array_push_autoinc(array &$array, $item) {
        $next = sizeof($array);
        $array[$next] = $item;
        return $next;
    }

    function ConvertAllRegistsInPDF() {
        $lists = getAllRegists();
        $json_final = array();
        $age = "";
        foreach ($lists as $value) {
            $iArray = array_push_autoinc($json_final, array(
                'curse' => $value['curse'],
                'files' => array()
            ));
            foreach ($value['data'] as $value2) {
                if ($age == "") $age = $value2['age'];
                $getHTML = getHTML(
                    $value['curse'],
                    array(
                        intval($value2['month']),
                        intval($value2['age'])
                    ),
                    $value2['list'],
                    $value2['status']
                );

                // Save in PDF
                $pDir = "../../olds/".$value2['age']."/pdf";
                $outputFile = str_replace('°', '', $value['curse']).$value2['month'].$value2['age'].".pdf";
                if (!is_dir($pDir)) mkdir($pDir);
                $saveFile = "$pDir/$outputFile";
                $html2pdf = new \Spipu\Html2Pdf\Html2Pdf('L', 'A3', 'es');
                $html2pdf->setDefaultFont('Arial');
                $html2pdf->writeHTML(str_replace(PHP_EOL, "", $getHTML));
                $pdf = $html2pdf->output('', 's');
                file_put_contents($saveFile, print_r($pdf, true));

                // Push data in array
                setlocale(LC_ALL,"es_ES@euro","es_ES","esp");
                $nameMonth = ucfirst(strftime("%B", mktime(0, 0, 0, intval($value2['month']), 10)));
                
                $processCurse = str_replace("°", "-", $value['curse']);
                $file = "data_$processCurse.json";
                $file2 = "data_process_$processCurse.json";

                array_push($json_final[$iArray]['files'], array(
                    'age' => $value2['age'],
                    'data' => $file,
                    'month' => $nameMonth,
                    'pdf' => $outputFile,
                    'process_data' => $file2
                ));
            }
        }

        $file3 = "../../olds/index_$age.json";
        if (!file_exists($file3)) {
            $filejson = fopen($file3, "w");
            fwrite($filejson, json_encode($json_final));
        }
    }
    ConvertAllRegistsInPDF();
?>