<?php
    include_once '../database.php';
    
    function BackupFiles() {
        // Get real path for our folder
        $rootPath = realpath('../../image');
        // Initialize archive object
        $zip = new ZipArchive();
        $age = intval(date("Y")) - 1;
        $zip->open("../../olds/$age/backup_files.zip", ZipArchive::CREATE | ZipArchive::OVERWRITE);
        // Create recursive directory iterator
        /** @var SplFileInfo[] $files */
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootPath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($files as $name => $file) {
            // Skip directories (they would be added automatically)
            if (!$file->isDir()) {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);
                // Add current file to archive
                $zip->addFile($filePath, $relativePath);
            }
        }
        // Zip archive will be created only after closing object
        $zip->close();
    }
    function BackupDatabases() {
        try {
            $db = new DBSystem();
            $tables = "*";
            $db = new mysqli("localhost", $db->user, $db->password, $db->database); 
            if($tables == '*') { 
                $tables = array();
                $result = $db->query("SHOW TABLES");
                while($row = $result->fetch_row()) { 
                    $tables[] = $row[0];
                }
            } else { 
                $tables = is_array($tables)?$tables:explode(',',$tables);
            }
            $return = '';
            foreach($tables as $table){
                $result = $db->query("SELECT * FROM $table");
                $numColumns = $result->field_count;
                /* $return .= "DROP TABLE $table;"; */
                $result2 = $db->query("SHOW CREATE TABLE $table");
                $row2 = $result2->fetch_row();
                $return .= "\n\n".$row2[1].";\n\n";
                for($i = 0; $i < $numColumns; $i++) { 
                    while($row = $result->fetch_row()) { 
                        $return .= "INSERT INTO $table VALUES(";
                        for($j=0; $j < $numColumns; $j++) { 
                            $row[$j] = addslashes($row[$j]);
                            $row[$j] = $row[$j];
                            if (isset($row[$j])) { 
                                $return .= '"'.$row[$j].'"' ;
                            } else { 
                                $return .= '""';
                            }
                            if ($j < ($numColumns-1)) {
                                $return.= ',';
                            }
                        }
                        $return .= ");\n";
                    }
                }
                $return .= "\n\n\n";
            }
            $age = intval(date("Y")) - 1;
            $fileName = "../../olds/$age/databases.sql";
            $handle = fopen($fileName, "w+");
            fwrite($handle, $return);
            fclose($handle);
            return;
        } catch (\Throwable $th) {
            echo $th;
            return;
        }
    }
    function BackupRegist() {
        try {
            $db = new DBSystem();
            $consult = $db->Query("SELECT * FROM `records`");
            if ($consult) {
                $data = array();
                while ($record = $consult->fetch_array()) {
                    array_push($data, array(
                        'id' => $record['id'],
                        'movent' => $record['movent'],
                        'date' => $record['date'],
                        'hour' => $record['hour'],
                        'importance' => $record['importance'],
                        'idAdmin' => $record['idAdmin'],
                        'type' => $record['type'],
                        'section' => $record['section']
                    ));
                }
                $age = intval(date("Y")) - 1;
                $fileName = "../../olds/$age/records.json";
                $handle = fopen($fileName, "w+");
                fwrite($handle, json_encode($data));
                fclose($handle);
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
    function ClearDataOfDatabases() {
        try {
            $db = new DBSystem();
            $db->Query("DELETE FROM `assist`");
            $db->Query("DELETE FROM `curses_groups`");
            $db->Query("DELETE FROM `groups`");
        } catch (\Throwable $th) {
            return;
        }
    }

    function ClearOldData() {
        BackupDatabases();
        BackupRegist();
        BackupFiles();
        ClearDataOfDatabases();
    }
    //ClearOldData();
?>