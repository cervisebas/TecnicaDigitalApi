<?php
    include_once 'classes.php';

    class ScheduleSystem {
        public function create($idDirective, string $curse, string $data) {
            $responses = new Responses();
            try {
                $db = new DBSystem();
                $directive = new DirectiveSystem();
                $records = new RecordSystem();
                $permission = new DirectivesPermissionSystem();
                /* ################################################## */
                $verify = $permission->verify($idDirective, 2);
                if (is_object($verify)) return $verify;
                if (!$verify) return $responses->errorPermission;
                /* ################################################## */
                $isExist = $this->isExist($curse);
                if ($isExist) return $responses->errorScheduleRepeat;
                /* ################################################## */
                $consult = $db->Query("INSERT INTO `schedule`(`id`, `curse`, `data`) VALUES (NULL, '$curse', '$data')");
                if ($consult) {
                    $usernameDirective = base64_decode($directive->getData_system($idDirective)['datas']['username']);
                    $records->create($idDirective, "El directivo @$usernameDirective creo un nuevo horario.", 2, "Añadir horario", "Horarios");
                    return $responses->good;
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }
        public function delete($idDirective, string $idSchedule) {
            $responses = new Responses();
            try {
                $db = new DBSystem();
                $directive = new DirectiveSystem();
                $records = new RecordSystem();
                $permission = new DirectivesPermissionSystem();
                /* ################################################## */
                $verify = $permission->verify($idDirective, 2);
                if (is_object($verify)) return $verify;
                if (!$verify) return $responses->errorPermission;
                /* ################################################## */
                $consult = $db->Query("DELETE FROM `schedule` WHERE `id`=$idSchedule");
                if ($consult) {
                    $usernameDirective = base64_decode($directive->getData_system($idDirective)['datas']['username']);
                    $records->create($idDirective, "El directivo @$usernameDirective elimino el horario #$idSchedule.", 1, "Borrar horario", "Horarios");
                    return $responses->good;
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }
        public function modify($idDirective, $idSchedule, string $data) {
            $responses = new Responses();
            try {
                $db = new DBSystem();
                $directive = new DirectiveSystem();
                $records = new RecordSystem();
                $permission = new DirectivesPermissionSystem();
                /* ################################################## */
                $verify = $permission->verify($idDirective, 2);
                if (is_object($verify)) return $verify;
                if (!$verify) return $responses->errorPermission;
                /* ################################################## */
                $consult = $db->Query("UPDATE `schedule` SET `data`='$data' WHERE `id`=$idSchedule");
                if ($consult) {
                    $usernameDirective = base64_decode($directive->getData_system($idDirective)['datas']['username']);
                    $records->create($idDirective, "El directivo @$usernameDirective edito el horario #$idSchedule.", 1, "Editar horario", "Horarios");
                    return $responses->good;
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }
        private function isExist(string $curse): bool {
            try {
                $db = new DBSystem();
                /* ################################################## */
                $consult = $db->Query("SELECT * FROM `schedule` WHERE `curse`='$curse'");
                if ($consult) return $consult->num_rows !== 0;
                return false;
            } catch (\Throwable $th) {
                return false;
            }
        }
        public function getAll($idDirective) {
            $responses = new Responses();
            try {
                $db = new DBSystem();
                $permission = new DirectivesPermissionSystem();
                /* ################################################## */
                $verify = $permission->verify($idDirective, 1);
                if (is_object($verify)) return $verify;
                if (!$verify) return $responses->errorPermission;
                /* ################################################## */
                function orderShedule($data1, $data2) {
                    return base64_decode($data1['curse']) > base64_decode($data2['curse']);
                }
                /* ################################################## */
                $consult = $db->Query("SELECT * FROM `schedule`");
                if ($consult) {
                    $data = array();
                    while ($schedule = $consult->fetch_array()) {
                        array_push($data, array(
                            'id' => $schedule['id'],
                            'curse' => $schedule['curse'],
                            'data' => $this->processData($schedule['data'])
                        ));
                    }
                    usort($data, "orderShedule");
                    return $responses->goodData($data);
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                $responses->writeError($th);
                return $responses->error1;
            }
        }
        public function get($curse) {
            $responses = new Responses();
            try {
                $db = new DBSystem();
                /* ################################################## */
                $consult = $db->Query("SELECT * FROM `schedule` WHERE `curse`='$curse'");
                if ($consult) {
                    if ($consult->num_rows == 0) return $responses->errorScheduleNotAvailable;
                    $schedule = $consult->fetch_array();
                    return $responses->goodData(array(
                        'id' => $schedule['id'],
                        'curse' => $schedule['curse'],
                        'data' => $this->processData($schedule['data'])
                    ));
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }
        public function processData(string $data) {
            $matters = new MatterSheduleSystem();
            $process_data = json_decode(base64_decode($data), true);
            $result = array();
            foreach ($process_data as $value) {
                array_push($result, array(
                    'day' => $value['day'],
                    'hour' => $value['hour'],
                    'group' => $value['group'],
                    'matter' => ($value['matter'] == 'none')? 'none': $matters->get_system($value['matter'])['datas']
                ));
            }
            return $result;
        }
    }
    
?>