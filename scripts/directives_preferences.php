<?php
    include_once "classes.php";

    class DirectivesPreferencesSystem {
        public function updateNow($idDirective, $date, $datas) {
            $responses = new Responses();
            try {
                $db = new DBSystem();
                /* ################################################## */
                $consult = $db->Query("SELECT * FROM `directives_preferences` WHERE `id_directive`=$idDirective");
                if ($consult) {
                    if ($consult->num_rows == 0) {
                        $consult2 = $db->Query("INSERT INTO `directives_preferences`(`id`, `id_directive`, `date_update`, `datas`) VALUES (NULL, $idDirective, '$date', '$datas')");
                        return ($consult2)? $responses->good: $responses->error2;
                    }
                    $actualData = $consult->fetch_array();
                    $actualDateTime = $this->convertDate(base64_decode($actualData['date_update']));
                    $updateDateTime = $this->convertDate(base64_decode($date));
                    $responses->writeError("$actualDateTime - $updateDateTime\n".base64_decode($actualData['date_update'])." - ".base64_decode($date))."\n".$actualData;
                    if ($updateDateTime == $actualDateTime) return $responses->good;
                    $compare = ($actualDateTime - $updateDateTime) < 0;
                    if ($compare) {
                        $consult2 = $db->Query("UPDATE `directives_preferences` SET `date_update`='$date', `datas`='$datas' WHERE `id_directive`=$idDirective");
                        return ($consult2)? $responses->good: $responses->error2;
                    }
                    return $responses->errorData("noupdate");
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                $responses->writeError($th);
                return $responses->error1;
            }
        }
        private function convertDate(string $input) {
            // DD/MM/YYYY
            $intent =  DateTime::createFromFormat('d/m/Y H:i', $input);
            if ($intent) return $intent->getTimestamp();
            
            $intent2 = DateTime::createFromFormat('d/m/Y H:i:s', $input);
            if ($intent2) return $intent2->getTimestamp();
            
            // MM/DD/YYYY
            $intent5 = DateTime::createFromFormat('m/d/Y H:i', $input);
            if ($intent5) return $intent5->getTimestamp();

            $intent6 = DateTime::createFromFormat('m/d/Y H:i:s', $input);
            if ($intent6) return $intent6->getTimestamp();
            
            // YYYY/MM/DD
            $intent3 = DateTime::createFromFormat('Y/m/d H:i', $input);
            if ($intent3) return $intent3->getTimestamp();
            
            $intent4 = DateTime::createFromFormat('Y/m/d H:i:s', $input);
            if ($intent4) return $intent4->getTimestamp();
            
            // YYYY/DD/MM
            $intent7 = DateTime::createFromFormat('Y/d/m H:i', $input);
            if ($intent7) return $intent7->getTimestamp();

            $intent7 = DateTime::createFromFormat('Y/d/m H:i:s', $input);
            if ($intent7) return $intent7->getTimestamp();

            // Default
            return strtotime($input);
        }
        public function get($idDirective) {
            $responses = new Responses();
            try {
                $db = new DBSystem();
                $consult = $db->Query("SELECT * FROM `directives_preferences` WHERE `id_directive`=$idDirective");
                if ($consult) {
                    if ($consult->num_rows == 0) return $responses->errorData("empty");
                    $actualData = $consult->fetch_array();
                    $dateSave = strtotime(base64_decode($actualData['date_update']));
                    return $responses->goodData(base64_encode(json_encode(array(
                        'idDirective' => $idDirective,
                        'date' => date('d/m/Y', $dateSave),
                        'time' => date('H:i:s', $dateSave),
                        'curses' => $actualData['datas']
                    ))));
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }
        public function system_delete($idDirective) {
            $db = new DBSystem();
            $consult = $db->Query("DELETE FROM `directives_preferences` WHERE `id_directive`=$idDirective");
            return ($consult)? true: false;
        }
    }

?>