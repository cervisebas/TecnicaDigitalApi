<?php
    error_reporting(E_ERROR | E_PARSE);
    set_time_limit(0);

    include_once "./ConvertAllRegistsInPDF.php";
    include_once "./ClearOldData.php";
    include_once "./ProcessByAge.php";

    function ProcessNewYear() {
        try {
            $start = microtime(true);
            ConvertAllRegistsInPDF();
            ClearOldData();
            upgradeAllStudentsForCurse();
            echo "Finish in: ".(microtime(true) - $start);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
    ProcessNewYear();
?>