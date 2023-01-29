<?php
    include_once "./ConvertAllRegistsInPDF.php";
    include_once "./ClearOldData.php";
    include_once "./ProcessByAge.php";

    function ProcessNewYear() {
        try {
            ConvertAllRegistsInPDF();
            ClearOldData();
            upgradeAllStudentsForCurse();
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
    ProcessNewYear();
?>