<?php
    error_reporting(E_ERROR | E_PARSE);
    include_once "../classes.php";

    function getUsedImages() {
        $db = new DBSystem();
        $UsedFiles = array();
        $consult = $db->Query("SELECT `picture` FROM `students`");
        while ($file = $consult->fetch_array()) {
            $nameFile = base64_decode($file[0]);
            if ($nameFile !== 'default.png') array_push($UsedFiles, $nameFile);
        }
        $consult2 = $db->Query("SELECT `picture` FROM `directives`");
        while ($file = $consult2->fetch_array()) {
            $nameFile = base64_decode($file[0]);
            if ($nameFile !== 'default-admin.png') array_push($UsedFiles, $nameFile);
        }
        return $UsedFiles;
    }
    function getServerImages() {
        $server = scandir("../../image");
        $files = array();
        foreach ($server as $file) {
            if (
                $file != "console.png" &&
                $file != "default.png" &&
                $file != "server.png" &&
                $file != "default-admin.png" &&
                $file != "default-admin-bad.png" &&
                $file != "get.php" &&
                $file != "." &&
                $file != ".."
            )
                array_push($files, $file);
        }
        return $files;
    }

    function VerifyFiles() {
        $accounts = getUsedImages();
        $server = getServerImages();
        $withOutUsed = array();

        foreach ($server as $local) {
            $find = array_search($local, $accounts);
            if ($find == false) array_push($withOutUsed, $local);
        }

        // Variables dir's
        $dirImages = "../../image";
        $dirRemoves = "../../removes";

        foreach ($withOutUsed as $path) {
            $oldDir = "$dirImages/$path";
            $newDir = "$dirRemoves/$path";
            rename($oldDir, $newDir);
        }

        $numberFiles = count($withOutUsed);
        if ($numberFiles !== 0) {
            $records = new RecordSystem();
            $word = ($numberFiles == 1)? "archivo": "archivos";
            $records->create(-2, "El servidor removió $numberFiles $word en desuso", 1, "Tarea programada", "Servidor");
        }
    }

    VerifyFiles();
?>