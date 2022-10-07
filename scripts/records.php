<?php
    include 'classes.php';

    class RecordSystem {
        public function create($idUser, string $movent, int $importance, string $type, string $section) {
            try {
                $db = new DBSystem();
                $date = base64_encode(date("d/m/Y"));
                $hour = base64_encode(date("G:i A"));
                $newMovement = base64_encode($movent);
                $newType = base64_encode($type);
                $newSection = base64_encode($section);
                $consult = $db->Query("INSERT INTO `records`(`id`, `movent`, `date`, `hour`, `importance`, `idAdmin`, `type`, `section`) VALUES (NULL, '$newMovement', '$date', '$hour', $importance, $idUser, '$newType', '$newSection')");
                return !!$consult;
            } catch (\Throwable $th) {
                return false;
            }
        }
        public function get($idRecord) {
            try {
                $db = new DBSystem();
                $directives = new DirectiveSystem();
                $consult = $db->Query("SELECT * FROM `records` WHERE `id`=$idRecord");
                if ($consult) {
                    $data = $consult->fetch_array();
                    return array(
                        'id' => $data['id'],
                        'movent' => $data['movent'],
                        'date' => $data['date'],
                        'hour' => $data['hour'],
                        'importance' => $data['importance'],
                        'admin' => $directives->getData_system($data['idAdmin']),
                        'type' => $data['type'],
                        'section' => $data['section']
                    );
                }
                return false;
            } catch (\Throwable $th) {
                return false;
            }
        }
        public function getAll($idDirective) {
            $responses = new Responses();
            try {
                $db = new DBSystem();
                $directives = new DirectiveSystem();
                $permission = new DirectivesPermissionSystem();
                /* ################################################## */
                $verify = $permission->verify($idDirective, 2);
                if (is_object($verify)) return $verify;
                if (!$verify) return $responses->errorPermission;
                /* ################################################## */
                $consult = $db->Query("SELECT * FROM `records`");
                if ($consult) {
                    $datas = array();
                    while ($data = $consult->fetch_array()) {
                        array_push($datas, array(
                            'id' => $data['id'],
                            'movent' => $data['movent'],
                            'date' => $data['date'],
                            'hour' => $data['hour'],
                            'importance' => $data['importance'],
                            'admin' => $directives->getData_system($data['idAdmin']),
                            'type' => $data['type'],
                            'section' => $data['section']
                        ));
                    }
                    $this->filterGetAll($datas);
                    return $responses->goodData(array_reverse($datas));
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->errorTypical;
            }
        }
        public function filterGetAll(&$datas) {
            $newResult = array();
            foreach ($datas as $value) {
                $dateMValue = $this->getMonthToDate($value['date']);
                $dateMNow = date("m");
                $dateYValue = $this->getYearToDate($value['date']);
                $dateYNow = date("Y");
                if ($dateMValue == $dateMNow && $dateYValue == $dateYNow) array_push($newResult, $value);
            }
            return $datas = $newResult;
        }
        private function getMonthToDate(string $input) {
            $intent =  DateTime::createFromFormat('d/m/Y', base64_decode($input));
            return $intent->format("m");
        }
        private function getYearToDate(string $input) {
            $intent =  DateTime::createFromFormat('d/m/Y', base64_decode($input));
            return $intent->format("Y");
        }
    }
    
?>