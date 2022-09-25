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
                return $responses->error1;
            }
        }
        private function convertDate(string $input) {
            return DateTime::createFromFormat('d/m/Y H:i', $input)->getTimestamp();
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