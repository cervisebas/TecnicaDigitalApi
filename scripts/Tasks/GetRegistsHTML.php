<?php
    function getHTML($curse, $date, $data) {
        setlocale(LC_ALL, "es_ES");
        // Date
        $month = $date[0];
        $nameMonth = DateTime::createFromFormat('!m', $month)->format('F');
        $age = $date[1];
        
        // Elements
        $getRows = getRowsTable($data);
        $getDays = getElementsDays($month, $age);

        return "<!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta http-equiv='X-UA-Compatible' content='IE=edge'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <style>
                * {
                    font-family: Arial, Helvetica, sans-serif;
                }
                table:not(.ignore) {
                    width: 100%;
                    page-break-inside: auto;
                }
                tr {
                    page-break-inside:avoid;
                    page-break-after:auto; 
                }
                div.header {
                    width: 100%;
                    display: flex;
                    justify-content: center;
                }
                div.header div.title {
                    width: max-content;
                    height: min-content;
                    border: 2px solid #000000;
                    padding-top: 4px;
                    padding-bottom: 4px;
                    padding-left: 10px;
                    padding-right: 10px;
                }
                div.header div.title h1 {
                    margin: 0;
                    padding: 0;
                    font-size: 26px;
                }
                div.content-props {
                    width: 100%;
                    display: flex;
                    justify-content: center;
                }
                div.content-props div.props {
                    width: 90%;
                    display: flex;
                    flex-direction: row;
                    align-items: center;
                }
                div.content-props div.props div.prop {
                    width: 100%;
                    margin-top: 16px;
                    margin-bottom: 12px;
                    display: flex;
                    flex-direction: row;
                    align-items: center;
                    justify-content: center;
                }
                div.content-props div.props div.prop p {
                    margin: 0;
                    padding: 0;
                    font-weight: bold;
                    font-size: 12px;
                }
                div.content-props div.props div.prop p.value {
                    margin-left: 2px;
                    font-weight: normal;
                }
                table, th, td {
                    border: 1.5px solid black;
                    border-collapse: collapse;
                }
                table td.center {
                    text-align: center !important;
                    width: 28px;
                }
                table td.center p {
                    margin-left: 0 !important;
                }
                table td p {
                    margin-top: 6px;
                    margin-bottom: 6px;
                    margin-left: 6px;
                    margin-right: 0;
                }
                table tr.bold p {
                    font-weight: 500;
                }
                table td.padding {
                    padding-left: 4px;
                    padding-right: 12px;
                }
                table.ignore {
                    margin-top: 16px;
                }
                table td.padding2 {
                    padding-left: 8px;
                    padding-right: 8px;
                }
            </style>
            <title>Registro $curse</title>
        </head>
        <body>
            <div class='header'>
                <div class='title'>
                    <h1>PLANILLA DE ASISTENCIA</h1>
                </div>
            </div>
            <div class='content-props'>
                <div class='props'>
                    <div class='prop'><p>CURSO:</p><p class='value'>$curse</p></div>
                    <div class='prop'><p>MES:</p><p class='value'>$nameMonth</p></div>
                    <div class='prop'><p>AÑO:</p><p class='value'>$age</p></div>
                </div>
            </div>
            <table>
                <tr class='bold'>
                    <td class='center'><p>N°</p></td>
                    <td><p>ALUMNO/A</p></td>
                    $getDays
                </tr>
                $getRows
            </table>
        </body>
        </html>";
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
    function getStadistics($data) {
        // Orden de datos
        /*var assist = 0;
        var notAssist = 0;
        var total = 0;
        data.forEach((day)=>{
            day.data.forEach((value)=>{
                if (value.status) assist += 1; else notAssist += 1;
                total += 1;
            });
        });*/

        // Calculos de la asistencia
        /*const percent = ((assist * 100)/total).toFixed(2);
        const media = (total / getBusinessDatesCount(month, year)).toFixed(2);*/
        
        $assist = 0;
        $notAssist = "";
        $total = 0;

        foreach ($data as $index => $value) {
            foreach ($value['list'] as $assist) {

            }
        }

        $percent = 0;
        $media = 0;

        return `<table class="ignore">
            <tr class="bold">
                <td class="padding"><p>Asistencia:</p></td>
                <td class="center padding2"><p>$assist</p></td>
            </tr>
            <tr class="bold">
                <td class="padding"><p>Inasistencia:</p></td>
                <td class="center padding2"><p>$notAssist</p></td>
            </tr>
            <tr class="bold">
                <td class="padding"><p>Porcentaje:</p></td>
                <td class="center padding2"><p>$percent%</p></td>
            </tr>
            <tr class="bold">
                <td class="padding"><p>Media:</p></td>
                <td class="center padding2"><p>$media</p></td>
            </tr>
        </table>`;
    }
?>