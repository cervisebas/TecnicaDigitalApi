<?php
    function getHTML($curse, $date, $data, $status) {
        setlocale(LC_ALL,"es_ES@euro","es_ES","esp");
        // Date
        $month = $date[0];
        //$nameMonth = DateTime::createFromFormat('!m', $month)->format('F');
        $nameMonth = ucfirst(strftime("%B", mktime(0, 0, 0, $month, 10)));
        $age = $date[1];
        
        // Elements
        $getRows = getRowsTable($data);
        $getDays = getElementsDays($month, $age);
        $getStadistics = getStadistics($status, $date);

        return "<page class='content'>
            <div class='header'>
                <div class='title'>
                    <h1>PLANILLA DE ASISTENCIA</h1>
                </div>
            </div>
            <div class='content-props'>
                <div class='props'>
                    <div class='prop'><p>CURSO: <span class='value'>$curse</span></p></div>
                    <div class='prop'><p>MES: <span class='value'>$nameMonth</span></p></div>
                    <div class='prop'><p>AÑO: <span class='value'>$age</span></p></div>
                </div>
            </div>
            <table width='100%'>
                <tr class='bold'>
                    <td class='center'><p>N°</p></td>
                    <td><p>ALUMNO/A</p></td>
                    $getDays
                </tr>
                $getRows
            </table>
            $getStadistics
            <style>
                .header {
                    width: 1190.551px;
                }
                .title {
                    width: max-content;
                    height: min-content;
                    border: 2px solid #000000;
                    padding-top: 4px;
                    padding-bottom: 4px;
                    padding-left: 10px;
                    padding-right: 10px;
                    margin-left: 595px;
                }
                .title h1 {
                    margin: 0;
                    padding: 0;
                    font-size: 26px;
                }
                .content-props {
                    width: 100%;
                }
                .props {
                    width: 90%;
                    display: flex;
                    flex-direction: row;
                    align-items: center;
                }
                .prop {
                    width: 100%;
                    margin-top: 16px;
                    margin-bottom: 12px;
                    display: flex;
                    flex-direction: row;
                    align-items: center;
                    justify-content: center;
                }
                .prop p {
                    margin: 0;
                    padding: 0;
                    font-weight: bold;
                    font-size: 12px;
                }
                .prop .value {
                    font-weight: normal;
                }
                table, th, td {
                    border: 1.5px solid black;
                    border-collapse: collapse;
                }
                table .center {
                    text-align: center !important;
                    width: 28px;
                }
                .center p {
                    margin-left: 0 !important;
                }
                table td p {
                    margin-top: 6px;
                    margin-bottom: 6px;
                    margin-left: 6px;
                    margin-right: 0px;
                }
                table .bold p {
                    font-weight: 500;
                }
                table .padding {
                    padding-left: 4px;
                    padding-right: 12px;
                }
                table.ignore {
                    margin-top: 16px;
                }
                table .padding2 {
                    padding-left: 8px;
                    padding-right: 8px;
                }
            </style>
        </page>";
        //{getStadistics(realMonth, ageNumber, data)}
    }

    function getRowsTable($data) {
        $resultHTML = "";
        foreach ($data as $index => $value) {
            $listOfAssist = "";
            foreach ($value['list'] as $assist) {
                $tmp_var = $assist['text'];
                $listOfAssist = "$listOfAssist<td class='center'><p>$tmp_var</p></td>";
            }
            $num = $index + 1;
            $student = $value['student'];
            $resultHTML = "$resultHTML<tr class='student'>
                <td class='center'><p>$num</p></td>
                <td><p>$student</p></td>
                $listOfAssist
            </tr>";
        }
        return $resultHTML;
    }
    function getElementsDays($month, $age) {
        $elms = "";
        $daysInMonth = cal_days_in_month(CAL_EASTER_DEFAULT, $month, $age);
        for ($i=0; $i < $daysInMonth; $i++) {
            $day = $i+1;
            $elms = "$elms<td class='center'><p>$day</p></td>";
        }
        return $elms;
    }
    function getStadistics($status, $date) {
        $assist = $status['assist'];
        $notAssist = $status['notAssist'];
        $total = $assist + $notAssist;
        $daysInMonth = cal_days_in_month(CAL_EASTER_DEFAULT, $date[0], $date[1]);

        $percent = number_format(($assist * 100)/$total, 2, '.', '');
        $media = number_format($total/$daysInMonth, 2, '.', '');

        return "<table class='ignore'>
            <tr class='bold'>
                <td class='padding'><p>Asistencia:</p></td>
                <td class='center padding2'><p>$assist</p></td>
            </tr>
            <tr class='bold'>
                <td class='padding'><p>Inasistencia:</p></td>
                <td class='center padding2'><p>$notAssist</p></td>
            </tr>
            <tr class='bold'>
                <td class='padding'><p>Porcentaje:</p></td>
                <td class='center padding2'><p>$percent%</p></td>
            </tr>
            <tr class='bold'>
                <td class='padding'><p>Media:</p></td>
                <td class='center padding2'><p>$media</p></td>
            </tr>
        </table>";
    }
?>