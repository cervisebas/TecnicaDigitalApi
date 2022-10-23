<?php
    include_once 'classes.php';

    class MatterSheduleSystem {
        public function create($idDirective, $idTeacher, string $name) {
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
                if ($this->checkExist($idTeacher, $name)) return $responses->errorMatterRepeat;
                /* ################################################## */
                $consult = $db->Query("INSERT INTO `matters`(`id`, `id_teacher`, `name`) VALUES (NULL, $idTeacher, '$name')");
                if ($consult) {
                    $usernameDirective = base64_decode($directive->getData_system($idDirective)['datas']['username']);
                    $records->create($idDirective, "El directivo @$usernameDirective añadio una nueva materia.", 2, "Añadir materia", "Materias");
                    return $responses->good;
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }
        private function checkExist($idTeacher, string $name) {
            $db = new DBSystem();
            $consult = $db->Query("SELECT * FROM `matters` WHERE `id_teacher`=$idTeacher AND `name`='$name'");
            if ($consult) {
                if ($consult->num_rows == 0)
                    return false;
                else
                    return true;
            }
            return true;
        }
        public function delete($idDirective, string $idMatter) {
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
                $consult = $db->Query("DELETE FROM `matters` WHERE `id`=$idMatter");
                if ($consult) {
                    $usernameDirective = base64_decode($directive->getData_system($idDirective)['datas']['username']);
                    $records->create($idDirective, "El directivo @$usernameDirective elimino la materia #$idMatter.", 1, "Borrar materia", "Materias");
                    return $responses->good;
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }
        public function modify($idDirective, $idMatter, $idTeacher, string $name) {
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
                $consult = $db->Query("UPDATE `matters` SET `id_teacher`=$idTeacher, `name`='$name' WHERE `id`=$idMatter");
                if ($consult) {
                    $usernameDirective = base64_decode($directive->getData_system($idDirective)['datas']['username']);
                    $records->create($idDirective, "El directivo @$usernameDirective edito la materia #$idMatter.", 1, "Editar materia", "Materias");
                    return $responses->good;
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }
        public function get_system($idMatter) {
            $responses = new Responses();
            try {
                $db = new DBSystem();
                $students = new StudentSystem();
                /* ################################################## */
                $consult = $db->Query("SELECT * FROM `matters` WHERE `id`=$idMatter");
                if ($consult) {
                    $schedule = $consult->fetch_array();
                    $teacher = array(
                        'id' => -1,
                        'name' => base64_encode("Desconocido"),
                        'dni' => base64_encode("00000000"),
                        'curse' => base64_encode("Docente"),
                        'tel' => base64_encode("0000000000"),
                        'email' => base64_encode("desconocido@email.com"),
                        'date' => base64_encode("00/00/0000"),
                        'picture' => base64_encode("default.php"),
                    );
                    $dataStudent = $students->get_system($schedule['id_teacher']);
                    if ($dataStudent['ok']) $teacher = $dataStudent['datas'];
                    return $responses->goodData(array(
                        'id' => $schedule['id'],
                        'teacher' => $teacher,
                        'name' => $schedule['name']
                    ));
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
                $students = new StudentSystem();
                /* ################################################## */
                $verify = $permission->verify($idDirective, 1);
                if (is_object($verify)) return $verify;
                if (!$verify) return $responses->errorPermission;
                /* ################################################## */
                function orderMatters($data1, $data2) {
                    return base64_decode($data1['name']) > base64_decode($data2['name']);
                }
                /* ################################################## */
                $consult = $db->Query("SELECT * FROM `matters`");
                if ($consult) {
                    $data = array();
                    while ($schedule = $consult->fetch_array()) {
                        $teacher = array(
                            'id' => -1,
                            'name' => base64_encode("Desconocido"),
                            'dni' => base64_encode("00000000"),
                            'curse' => base64_encode("Docente"),
                            'tel' => base64_encode("0000000000"),
                            'email' => base64_encode("desconocido@email.com"),
                            'date' => base64_encode("00/00/0000"),
                            'picture' => base64_encode("default.php"),
                        );
                        $dataStudent = $students->get_system($schedule['id_teacher']);
                        if ($dataStudent['ok']) $teacher = $dataStudent['datas'];
                        array_push($data, array(
                            'id' => $schedule['id'],
                            'teacher' => $teacher,
                            'name' => $schedule['name']
                        ));
                    }
                    usort($data, "orderMatters");
                    return $responses->goodData($data);
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }
    }
    
?>