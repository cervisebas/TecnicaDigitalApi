<?php
    include_once 'classes.php';

    class AssistSystem {
        public function createGroup($idDirective, string $course, string $date, string $hour) {
            $responses = new Responses();
            try {
                $db = new DBSystem();
                $permission = new DirectivesPermissionSystem();
                $directive = new DirectiveSystem();
                $records = new RecordSystem();
                /* ################################################## */
                $verify = $permission->verify($idDirective, 2);
                if (is_object($verify)) return $verify;
                if (!$verify) return $responses->errorPermission;
                /* ################################################## */
                $newHour = $hour;
                if (base64_decode($course) == "docentes") $newHour = base64_encode($this->getTimeForRegistAnyTeachers($hour));
                /* ###############   Verify   ####################### */
                $consult = $db->Query("SELECT * FROM `groups` WHERE `curse`='$course' AND `date`='$date' AND `hour`='$newHour'");
                if ($consult) if (!$consult->num_rows == 0) return $responses->errorData("Ya existe un registro similar.");
                /* ################################################## */
                $status = "0";
                if (base64_decode($course) == "docentes") $status = "1";
                $consult = $db->QueryAndConect("INSERT INTO `groups`(`id`, `curse`, `date`, `hour`, `status`) VALUES (NULL, '$course', '$date', '$newHour', '$status')");
                if ($consult['exec']) {
                    $usernameDirective = base64_decode($directive->getData_system($idDirective)['datas']['username']);
                    $newCurse = base64_decode($course);
                    if ($newCurse == "docentes") $newCurse = "los docentes";
                    $records->create($idDirective, "El directivo @$usernameDirective creo un nuevo registro para $newCurse", 2, "Creación de registro", "Asistencia");
                    $new_id = $consult['connection']->insert_id;
                    $consult['connection']->close();
                    //if (base64_decode($course) == "docentes") $this->finishRegistAssistTeachers($new_id);
                    return $responses->goodData($new_id);
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }
        
        /*private function finishRegistAssistTeachers($idGroup) {
            $responses = new Responses();
            try {
                $db = new DBSystem();
                $students = new StudentSystem();
                $teachers = $students->system_getTeachers();
                if ($teachers) {
                    $lines = "";
                    foreach ($teachers as $value) {
                        $c = (strlen($lines) == 0)? "": ", ";
                        $time = base64_encode(date("H:i"));
                        $idStudent = $value['id'];
                        $lines = $lines.$c."(NULL, $idStudent, $idGroup, '$time', '0', '0')";
                    }
                    $responses->writeError($lines);
                    $consult2 = $db->Query("INSERT INTO `assists`(`id`, `id_student`, `id_group`, `hour`, `status`, `credential`) VALUES $lines ON DUPLICATE KEY UPDATE `hour`=CASE WHEN status='1' THEN hour ELSE VALUES(hour) END, `status`=VALUES(status)");
                    if ($consult2) return true;
                    return false;
                }
                return false;
            } catch (\Throwable $th) {
                $responses->writeError($th);
                return false;
            }
        }*/
        public function addTeacherAssist($idDirective, $idGroup, $idTeacher) {
            $responses = new Responses();
            try {
                $db = new DBSystem();
                $permission = new DirectivesPermissionSystem();
                $directive = new DirectiveSystem();
                $records = new RecordSystem();
                /* ################################################## */
                $verify = $permission->verify($idDirective, 2);
                if (is_object($verify)) return $verify;
                if (!$verify) return $responses->errorPermission;
                /* ################################################## */
                $time = base64_encode(date("H:i"));
                $consult = $db->QueryAndConect("INSERT INTO `assists`(`id`, `id_student`, `id_group`, `hour`, `status`, `credential`) VALUES (NULL, $idTeacher, $idGroup, '$time', '0', '0')");
                if ($consult['exec']) {
                    $usernameDirective = base64_decode($directive->getData_system($idDirective)['datas']['username']);
                    $records->create($idDirective, "El directivo @$usernameDirective añadió un docente al registro #$idGroup.", 1, "Edicion de registro", "Asistencia");
                    $actualId = $consult['connection']->insert_id;
                    $consult['connection']->close();
                    return $responses->goodData($actualId);
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }
        public function modifyTeacherAssist($idDirective, $idGroup, $idTeacher, string $status) {
            $responses = new Responses();
            try {
                $db = new DBSystem();
                $permission = new DirectivesPermissionSystem();
                /* ################################################## */
                $verify = $permission->verify($idDirective, 2);
                if (is_object($verify)) return $verify;
                if (!$verify) return $responses->errorPermission;
                /* ################################################## */
                $time = base64_encode(date("H:i"));
                $consult = $db->Query("UPDATE `assists` SET `hour`='$time', `status`='$status' WHERE `id_student`=$idTeacher AND `id_group`=$idGroup");
                if ($consult) return $responses->good;
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }
        public function removeTeacherAssist($idDirective, $idGroup, $idTeacher) {
            $responses = new Responses();
            try {
                $db = new DBSystem();
                $permission = new DirectivesPermissionSystem();
                $directive = new DirectiveSystem();
                $records = new RecordSystem();
                /* ################################################## */
                $verify = $permission->verify($idDirective, 2);
                if (is_object($verify)) return $verify;
                if (!$verify) return $responses->errorPermission;
                /* ################################################## */
                $consult = $db->Query("DELETE FROM `assists` WHERE `id_student`=$idTeacher AND `id_group`=$idGroup");
                if ($consult) {
                    $usernameDirective = base64_decode($directive->getData_system($idDirective)['datas']['username']);
                    $records->create($idDirective, "El directivo @$usernameDirective removió un docente en el registro #$idGroup.", 1, "Edicion de registro", "Asistencia");
                    return $responses->good;
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }


        public function confirmAssist($idDirective, $idGroup, $datas) {
            $responses = new Responses();
            try {
                $db = new DBSystem();
                $permission = new DirectivesPermissionSystem();
                $directive = new DirectiveSystem();
                $records = new RecordSystem();
                /* ################################################## */
                $verify = $permission->verify($idDirective, 2);
                if (is_object($verify)) return $verify;
                if (!$verify) return $responses->errorPermission;
                /* ################################################## */
                $notify = true;
                $isFilter = false;
                if (isset($_POST['notify'])) $notify = ($_POST['notify'] == '1');
                if (isset($_POST['isFilter'])) $isFilter = ($_POST['isFilter'] == '1');
                if ($isFilter) {
                    $getActualData = $db->Query("SELECT * FROM `groups` WHERE `id`=$idGroup");
                    $getActualData = $getActualData->fetch_array();
                    if ($getActualData['status'] == "1")  {
                        $consult = $db->Query("SELECT * FROM `assists` WHERE `id_group`=$idGroup");
                        $idsRemove = "";
                        while ($datas_assit = $consult->fetch_array()) {
                            $find = false;
                            foreach ($datas as &$value) {
                                $find = ($value['idStudent'] == $datas_assit['id_student']);
                            }
                            if (!$find) $idsRemove = $idsRemove.((strlen($idsRemove) !== 0)? ", ": "").$datas_assit['id'];
                        }
                        $db->Query("DELETE FROM `assists` WHERE `id` IN ($idsRemove)");
                    }
                }
                $lines = "";
                foreach ($datas as &$value) {
                    $c = (strlen($lines) == 0)? "": ", ";
                    $id = (strval($value['idAssist']) !== '-1')? $value['idAssist']: "NULL";
                    $time = base64_encode(date("H:i"));
                    $idStudent = $value['idStudent'];
                    $status = strval($value['check']);
                    $lines = $lines.$c."($id, $idStudent, $idGroup, '$time', '$status', '0')";
                }
                $consult = $db->Query("INSERT INTO `assists`(`id`, `id_student`, `id_group`, `hour`, `status`, `credential`) VALUES $lines ON DUPLICATE KEY UPDATE `hour`=CASE WHEN status='1' THEN hour ELSE VALUES(hour) END, `status`=VALUES(status)");
                $getActualData = $db->Query("SELECT * FROM `groups` WHERE `id`=$idGroup");
                $consult2 = $db->Query("UPDATE `groups` SET `status`='1' WHERE `id`=$idGroup");
                if ($consult && $consult2) {
                    if ($notify) {
                        $dataNotify = array();
                        foreach ($datas as &$value) {
                            $hashId = "";
                            for ($i=0; $i < 5 - strlen($value['idStudent']); $i++) {  $hashId = $hashId."0"; }
                            $hashId = $hashId.$value['idStudent'];
                            $title = "Se registro la asistencia del alumno #$hashId";
                            $date = date("d/m/Y");
                            $body = (strval($value['check']) == "1")? "El alumno estuvo presente el día $date.": "El alumno estuvo ausente el día $date.";
                            array_push($dataNotify, array('to' => $value['idStudent'], 'title' => $title, 'body' => $body));
                        }
                        $dataNotify = base64_encode(serialize($dataNotify));
                        $db->Query("INSERT INTO `notifications`(`id`, `datas`) VALUES (NULL, '$dataNotify')");
                    }
                    $usernameDirective = base64_decode($directive->getData_system($idDirective)['datas']['username']);
                    $getActualData = $getActualData->fetch_array();
                    if ($getActualData['status'] == "1") $records->create($idDirective, "El directivo @$usernameDirective edito el registro #$idGroup.", 1, "Edicion de registro", "Asistencia"); else $records->create($idDirective, "El directivo @$usernameDirective confirmo el registro #$idGroup.", 1, "Confirmación de registro", "Asistencia");
                    return $responses->good;
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }
        public function getAll($idDirective) {
            $responses = new Responses();
            try {
                $db = new DBSystem();
                $permission = new DirectivesPermissionSystem();
                $annotations = new AnnotationSystem();
                /* ################################################## */
                $verify = $permission->verify($idDirective, 1);
                if (is_object($verify)) return $verify;
                if (!$verify) return $responses->errorPermission;
                /* ################################################## */
                $consult = $db->Query("SELECT * FROM `groups` WHERE NOT `curse`='ZG9jZW50ZXM='");
                if ($consult) {
                    $result = array();
                    while ($group = $consult->fetch_array()) {
                        $countAnnotations = $annotations->get_system_count($group['id']);
                        array_push($result, array(
                            'id' => $group['id'],
                            'curse' => $group['curse'],
                            'date' => $group['date'],
                            'hour' => $group['hour'],
                            'status' => $group['status'],
                            'annotations' => $countAnnotations
                        ));
                    }
                    usort($result, function($a, $b) {
                        $e1 = explode("/", base64_decode($a["date"]));
                        $e2 = explode("/", base64_decode($b["date"]));
                        $date1_2 = $e1[0]."-".$e1[1]."-".$e1[2]." ".base64_decode($a['hour']);
                        $date2_2 = $e2[0]."-".$e2[1]."-".$e2[2]." ".base64_decode($b['hour']);
                        $time1 = strtotime($date1_2);
                        $time2 = strtotime($date2_2);
                        return $time2 - $time1;
                    });
                    return $responses->goodData($result);
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }
        public function getAllTeachers($idDirective) {
            $responses = new Responses();
            try {
                $db = new DBSystem();
                $permission = new DirectivesPermissionSystem();
                $annotations = new AnnotationSystem();
                /* ################################################## */
                $verify = $permission->verify($idDirective, 1);
                if (is_object($verify)) return $verify;
                if (!$verify) return $responses->errorPermission;
                /* ################################################## */
                $consult = $db->Query("SELECT * FROM `groups` WHERE `curse`='ZG9jZW50ZXM='");
                if ($consult) {
                    $result = array();
                    while ($group = $consult->fetch_array()) {
                        $countAnnotations = $annotations->get_system_count($group['id']);
                        array_push($result, array(
                            'id' => $group['id'],
                            'curse' => $group['curse'],
                            'date' => $group['date'],
                            'hour' => $group['hour'],
                            'status' => $group['status'],
                            'annotations' => $countAnnotations
                        ));
                    }
                    usort($result, function($a, $b) {
                        $e1 = explode("/", base64_decode($a["date"]));
                        $e2 = explode("/", base64_decode($b["date"]));
                        $date1_2 = $e1[0]."-".$e1[1]."-".$e1[2]." ".base64_decode($a['hour']);
                        $date2_2 = $e2[0]."-".$e2[1]."-".$e2[2]." ".base64_decode($b['hour']);
                        $time1 = strtotime($date1_2);
                        $time2 = strtotime($date2_2);
                        return $time2 - $time1;
                    });
                    return $responses->goodData($result);
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }
        public function get($idDirective, $idGroup) {
            $responses = new Responses();
            $permission = new DirectivesPermissionSystem();
            /* ################################################## */
            $verify = $permission->verify($idDirective, 1);
            if (is_object($verify)) return $verify;
            if (!$verify) return $responses->errorPermission;
            /* ################################################## */
            return $this->get2_system($idGroup);
        }
        public function delete($idDirective, $idGroup) {
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
                $consult = $db->Query("DELETE FROM `assists` WHERE `id_group`=$idGroup");
                if ($consult) {
                    $consult2 = $db->Query("DELETE FROM `groups` WHERE `id`=$idGroup");
                    if ($consult2) {
                        $usernameDirective = base64_decode($directive->getData_system($idDirective)['datas']['username']);
                        $records->create($idDirective, "El directivo @$usernameDirective borro el registro #$idGroup", 1, "Borrar registro", "Asistencia");
                        return $responses->good;
                    }
                    return $responses->error2;
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }
        public function getIndividual($idDirective, $idStudent) {
            $responses = new Responses();
            try {
                $db = new DBSystem();
                $permission = new DirectivesPermissionSystem();
                /* ################################################## */
                $verify = $permission->verify($idDirective, 1);
                if (is_object($verify)) return $verify;
                if (!$verify) return $responses->errorPermission;
                /* ################################################## */
                $consult = $db->Query("SELECT * FROM `assists` WHERE `id_student`=$idStudent");
                if ($consult) {
                    $result = array();
                    while ($assist = $consult->fetch_array()) {
                        $date = $this->system_getdate($assist['id_group']);
                        array_push($result, array(
                            'id' => $assist['id'],
                            'date' => $date,
                            'hour' => $assist['hour'],
                            'status' => ($assist['status'] == '1')? true: false,
                            'credential' => ($assist['credential'] == '1')? true: false
                        ));
                    }
                    usort($result, function($a, $b) {
                        $e1 = explode("/", base64_decode($a["date"]));
                        $e2 = explode("/", base64_decode($b["date"]));
                        $date1_2 = $e1[0]."-".$e1[1]."-".$e1[2]." ".base64_decode($a['hour']);
                        $date2_2 = $e2[0]."-".$e2[1]."-".$e2[2]." ".base64_decode($b['hour']);
                        $time1 = strtotime($date1_2);
                        $time2 = strtotime($date2_2);
                        return $time2 - $time1;
                    });
                    return $responses->goodData($result);
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }


        public function system_getdate($idGroup) {
            $db = new DBSystem();
            $consult = $db->Query("SELECT * FROM `groups` WHERE `id`=$idGroup");
            $datas = $consult->fetch_array();
            return $datas['date'];
        }
        public function system_getstatus($idGroup) {
            $db = new DBSystem();
            $consult = $db->Query("SELECT * FROM `groups` WHERE `id`=$idGroup");
            $datas = $consult->fetch_array();
            return $datas['status'];
        }
        public function system_findStudent($idStudent, $idGroup) {
            $error = array('ok' => false, 'id_assist' => -1, 'hour' => base64_encode('No disponible'), 'credential' => false);
            try {
                $db = new DBSystem();
                $consult = $db->Query("SELECT * FROM `assists` WHERE `id_group`=$idGroup AND `id_student`=$idStudent");
                if ($consult) {
                    if ($consult->num_rows == 0) return $error;
                    $datas = $consult->fetch_array();
                    return array(
                        'ok' => ($datas['status'] == '1')? true: false,
                        'id_assist' => $datas['id'],
                        'hour' => $datas['hour'],
                        'credential' => ($datas['credential'] == '1')
                    );
                }
                return $error;
            } catch (\Throwable $th) {
                return $error;
            }
        }
        public function get3_system($idGroup) {
            $responses = new Responses();
            try {
                $db = new DBSystem();
                $consult = $db->Query("SELECT * FROM `groups` WHERE `id`=$idGroup");
                if ($consult) {
                    $dataGroup = $consult->fetch_array();
                    return $responses->goodData(array(
                        'id' => $dataGroup['id'],
                        'curse' => $dataGroup['curse'],
                        'date' => $dataGroup['date'],
                        'hour' => $dataGroup['hour']
                    ));
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }
        public function get2_system($idGroup) {
            $responses = new Responses();
            try {
                $db = new DBSystem();
                $students = new StudentSystem();
                /* ################################################## */
                function orderStudents($data1, $data2) {
                    $name1 = normalizeChars(base64_decode($data1['name']));
                    $name2 = normalizeChars(base64_decode($data2['name']));
                    return $name1 > $name2;
                }
                /* ################################################## */
                $consult = $db->Query("SELECT * FROM `groups` WHERE `id`=$idGroup");
                if ($consult) {
                    $dataGroup = $consult->fetch_array();
                    $curse = $dataGroup['curse'];
                    if ($curse == base64_encode('docentes')) $curse = base64_encode('Docente');
                    $getStudents = $students->system_getAllForCurse($curse);
                    if (!$getStudents['ok']) return $getStudents;
                    $result = array();
                    foreach ($getStudents['datas'] as &$student) {
                        $status = $this->system_findStudent($student['id'], $idGroup);
                        array_push($result, array(
                            'id' => $student['id'],
                            'name' => $student['name'],
                            'picture' => $student['picture'],
                            'status' => $status['ok'],
                            'idAssist' => $status['id_assist'],
                            'exist' => $status['credential'],
                            'existRow' => ($status['id_assist'] !== -1),
                            'time' => $status['hour']
                        ));
                    }
                    usort($result, "orderStudents");
                    return $responses->goodData($result);
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }

        // Family
        public function family_getData($idStudent) {
            $responses = new Responses();
            try {
                $db = new DBSystem();
                $consult = $db->Query("SELECT * FROM `assists` WHERE `id_student`=$idStudent");
                if ($consult) {
                    $result = array();
                    while ($assist = $consult->fetch_array()) {
                        $date = $this->system_getdate($assist['id_group']);
                        $nowYear = date("Y");
                        $statusGroup = $this->system_getstatus($assist['id_group']);
                        if ($statusGroup == "1") {
                            if (strpos(base64_decode($date), $nowYear) !== false) array_push($result, array(
                                'id' => $assist['id'],
                                'date' => $date,
                                'hour' => $assist['hour'],
                                'status' => ($assist['status'] == '1'),
                                'credential' => ($assist['credential'] == "1")
                            ));
                        }
                    }
                    usort($result, function($a, $b) {
                        $e1 = explode("/", base64_decode($a["date"]));
                        $e2 = explode("/", base64_decode($b["date"]));
                        $date1_2 = $e1[0]."-".$e1[1]."-".$e1[2]." ".base64_decode($a['hour']);
                        $date2_2 = $e2[0]."-".$e2[1]."-".$e2[2]." ".base64_decode($b['hour']);
                        $time1 = strtotime($date1_2);
                        $time2 = strtotime($date2_2);
                        return $time2 - $time1;
                    });
                    return $responses->goodData($result);
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }

        // Console
        public function setDataFromConsole($data_string) {
            $responses = new Responses();
            try {
                $db = new DBSystem();
                $datas = json_decode(base64_decode($data_string), true);
                $records = new RecordSystem();
                $notify = new NotificationSystem();
                $return = 0;
                foreach ($datas as &$value) {
                    $data_curse = $value["curse"];
                    $data_hour = $value["hour"];
                    $data_date = $value["date"];
                    $verify = $this->test_verifyGroup($value['list'], $data_curse, $data_date, $data_hour);
                    if ($verify) {
                        $consult = $db->Query("SELECT * FROM `groups` WHERE `curse`='$data_curse' AND `date`='$data_date' AND `hour`='$data_hour'");
                        if ($consult) {
                            if ($consult->num_rows == 0) {
                                $consult2 = $db->QueryAndConect("INSERT INTO `groups`(`id`, `curse`, `date`, `hour`, `status`) VALUES (NULL, '$data_curse', '$data_date', '$data_hour', '0')");
                                if ($consult2['exec']) {
                                    $idGroup = $consult2['connection']->insert_id;
                                    $lines = "";
                                    foreach ($value['list'] as &$value2) {
                                        $c = (strlen($lines) == 0)? "": ", ";
                                        $time = $value2['hour'];
                                        $idStudent = $value2['id'];
                                        $lines = $lines.$c."(NULL, $idStudent, $idGroup, '$time', '1', '1')";
                                    }
                                    $consult3 = $db->Query("INSERT INTO `assists`(`id`, `id_student`, `id_group`, `hour`, `status`, `credential`) VALUES $lines");
                                    if ($consult3) $return += 1;
                                }
                            } else {
                                $return += 1;
                            }
                        }
                    }
                }
                if ($return !== 0) {
                    $count = count($datas);
                    $tag = ($count == 1)? "curso": "cursos";
                    $records->create("-69", "La consola sincronizo la asistencia de $count $tag.", 2, "Sincronización de la consola.", "Asistencia");
                    $l1 = ($count == 1)? "registro": "registros";
                    $l2 = ($count == 1)? "listo": "listos";
                    $notify->send("directives", "Ya están listos los registros de asistencia.", "La consola ya envió $count $l1 de asistencia $l2 para confirmar.", "");
                    return $responses->good;
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->errorTypical;
            }
        }
        private function test_verifyGroup($students, $curse, $date, $time) {
            $responses = new Responses();
            try {
                return $this->verifyGroup($students, $curse, $date, $time);
            } catch (\Throwable $th) {
                $responses->writeError($th);
                return true;
            }
        }
        private function getFromCurseAndDate(string $curse, string $date) {
            $db = new DBSystem();
            $consult = $db->Query("SELECT * FROM `groups` WHERE `curse`='$curse' AND `date`='$date'");
            if ($consult) {
                $array = $consult->fetch_array();
                $result = array();
                foreach ($array as $key => $group) {
                    $group = $this->system_getIndividual($group['id']);
                    array_push($result, array(
                        'id' => $group['id'],
                        'curse' => $group['curse'],
                        'date' => $group['date'],
                        'hour' => $group['hour'],
                        'status' => $group['status'],
                        'students' => $group
                    ));
                }
                return $result;
            }
        }
        private function verifyGroup($students, $curse, $date, $time) {
            $getNewTimes = $this->filterTime(base64_decode($time));
            $groups = $this->getFromCurseAndDate($curse, $date);
            $groupsByTime = array();
            foreach ($groups as $key => $group) {
                foreach ($getNewTimes as $key => $time) {
                    if (base64_decode($group['hour']) == $time)
                        array_push($push, $groupsByTime);
                }
            }
            $groupsByStudents = array();
            foreach ($groupsByTime as $key => $group) {
                foreach ($students as $key => $student) {
                    $find = array_search($student['id'], array_column($group['students'], 'id'));
                    if ($find !== false) array_push($groupsByStudents);
                }
            }
            return (count($groupsByStudents) == 0);
        }
        private function system_getIndividual(string $idGroup) {
            $db = new DBSystem();
            $consult = $db->Query("SELECT * FROM `assists` WHERE `id_group`=$idGroup");
            if ($consult) {
                $result = array();
                while ($assist = $consult->fetch_array()) {
                    $date = $this->system_getdate($assist['id_group']);
                    array_push($result, array(
                        'id' => $assist['id'],
                        'date' => $date,
                        'hour' => $assist['hour'],
                        'status' => ($assist['status'] == '1')? true: false,
                        'credential' => ($assist['credential'] == '1')? true: false
                    ));
                }
                return $result;
            }
        }
        private function filterTime($time) {
            if ($time == "7:15" || $time == "8:40" || $time == "9:50" || $time == "11:00") 
                return array("7:15", "8:40", "9:50", "11:00");
            if ($time == "13:15" || $time == "14:25" || $time == "15:35" || $time == "16:45") 
                return array("13:15", "14:25", "15:35", "16:45");
        }
        public function setTeacherConsole($idTeacher, $time) {
            $responses = new Responses();
            try {
                // Requires
                $db = new DBSystem();
                
                // Variables
                $dateNow = base64_encode(date("d/m/Y"));
                $existRegist = false;
                $registId = '-1';

                // Check Time
                $getTime = $this->getTimeForRegist($time);
                if (is_object($getTime)) return $getTime;
                $getTime = base64_encode($getTime);

                // Check Exist
                $consult = $db->Query("SELECT * FROM `groups` WHERE `curse`='ZG9jZW50ZXM=' AND `date`='$dateNow' AND `hour`='$getTime'");
                if ($consult) {
                    if ($consult->num_rows == 0) {
                        $existRegist = false;
                    } else {
                        $datas = $consult->fetch_array();
                        $existRegist = true;
                        $registId = $datas['id'];
                    }
                } else return $responses->error2Data('Paso 1');
            
                // If exist
                if ($existRegist) {
                    // Check Exist Row
                    $consult3 = $db->Query("SELECT * FROM `assists` WHERE `id_student`=$idTeacher AND `id_group`=$registId");
                    if ($consult3) {
                        if ($consult3->num_rows !== 0) return $responses->errorData("exist-row");
                    } else return $responses->error2Data('Paso 2');

                    // Create Row
                    $consult2 = $db->Query("INSERT INTO `assists`(`id`, `id_student`, `id_group`, `hour`, `status`, `credential`) VALUES (NULL, $idTeacher, $registId, '$time', '1', '1')");
                    if ($consult2) return $responses->good;
                    return $responses->error2Data('Paso 3');
                }

                // If Not Exist
                $consult2 = $db->QueryAndConect("INSERT INTO `groups`(`id`, `curse`, `date`, `hour`, `status`) VALUES (NULL, 'ZG9jZW50ZXM=', '$dateNow', '$getTime', '1')");
                if ($consult2['exec']) {
                    $new_id = $consult2['connection']->insert_id;
                    $consult3 = $db->Query("INSERT INTO `assists`(`id`, `id_student`, `id_group`, `hour`, `status`, `credential`) VALUES (NULL, $idTeacher, $new_id, '$time', '1', '1')");
                    if ($consult3) return $responses->good;
                    return $responses->error2Data('Paso 4');
                }
                return $responses->error2Data('Paso 5');
            } catch (\Throwable $th) {
                return $responses->errorTypical;
            }
        }
        private function getTimeForRegist(string $time) {
            $verify = new VerifyData();
            $responses = new Responses();
            try {
                if ($verify->checkHourBetween('7:00', '12:00', base64_decode($time))) return '7:15';
                if ($verify->checkHourBetween('12:50', '18:00', base64_decode($time))) return '13:15';
                //return '7:15';
                return $responses->errorOutTime;
            } catch (\Throwable $th) {
                return $responses->errorOutTime;
            }            
        }
        private function getTimeForRegistAnyTeachers(string $time) {
            $verify = new VerifyData();
            if ($verify->checkHourBetween('00:00', '12:00', base64_decode($time))) return '7:15';
            if ($verify->checkHourBetween('12:01', '23:59', base64_decode($time))) return '13:15';
            return '7:15';
        }
    }
?>