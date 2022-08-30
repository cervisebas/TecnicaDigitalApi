<?php
    include_once 'classes.php';

    class StudentSystem {
        public function create($idDirective, string $name, string $dni, string $curse, string $tel, string $date) {
            $responses = new Responses();
            try {
                $db = new DBSystem();
                $verifyData = new VerifyData();
                $fileSystem = new FileSystem();
                $directive = new DirectiveSystem();
                $records = new RecordSystem();
                $permission = new DirectivesPermissionSystem();
                /* ################################################## */
                $verify = $permission->verify($idDirective, 2);
                if (is_object($verify)) return $verify;
                if (!$verify) return $responses->errorPermission;
                /* ################################################## */
                if (base64_decode($curse) == "Profesor/a") return $responses->errorIncompatible;
                /* ################################################## */
                $image = base64_encode('default.png');
                if ($verifyData->issetFilePost('image')) $image = base64_encode($fileSystem->createStudentImage2($_FILES['image']));
                $email = '';
                if ($verifyData->issetDataPost(array('email'))) $email = $_POST['email'];
                $consult = $db->Query("INSERT INTO `students`(`id`, `name`, `dni`, `curse`, `tel`, `email`, `date`, `picture`) VALUES (NULL, '$name', '$dni', '$curse', '$tel', '$email', '$date', '$image')");
                if ($consult) {
                    $usernameDirective = base64_decode($directive->getData_system($idDirective)['datas']['username']);
                    $records->create($idDirective, "El directivo @$usernameDirective añadió un nuevo estudiante.", 2, "Añadir estudiante", "Estudiantes");
                    return $responses->good;
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }
        public function delete($idDirective, string $idStudent) {
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
                $consult = $db->Query("DELETE FROM `students` WHERE `id`=$idStudent");
                if ($consult) {
                    $usernameDirective = base64_decode($directive->getData_system($idDirective)['datas']['username']);
                    $records->create($idDirective, "El directivo @$usernameDirective elimino el estudiante #$idStudent.", 1, "Borrar estudiante", "Estudiantes");
                    return $responses->good;
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }
        public function modify($idDirective, $idStudent, string $name, string $dni, string $curse, string $tel, string $date, string $email) {
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
                $verifyData = new VerifyData();
                $fileSystem = new FileSystem();
                /* ################################################## */
                if (!$verifyData->is_empty($curse)) if (base64_decode($curse) == "Profesor/a") return $responses->errorIncompatible;
                /* ################################################## */
                $edit = "";
                (!$verifyData->is_empty($name)) && $edit = $edit."`name`='$name'";
                (!$verifyData->is_empty($dni)) && $edit = $edit.((strlen($edit) != 0)? ",": "")."`dni`='$dni'";
                (!$verifyData->is_empty($curse)) && $edit = $edit.((strlen($edit) != 0)? ",": "")."`curse`='$curse'";
                (!$verifyData->is_empty($tel)) && $edit = $edit.((strlen($edit) != 0)? ",": "")."`tel`='$tel'";
                (!$verifyData->is_empty($date)) && $edit = $edit.((strlen($edit) != 0)? ",": "")."`date`='$date'";
                (!$verifyData->is_empty($email)) && $edit = $edit.((strlen($edit) != 0)? ",": "")."`email`='$email'";
                if ($verifyData->issetFilePost('image')) {
                    $image = base64_encode($fileSystem->createStudentImage2($_FILES['image']));
                    $edit = $edit.((strlen($edit) != 0)? ",": "")."`picture`='$image'";
                }
                if (!$verifyData->issetFilePost('image') && $verifyData->issetPosts(array('isRemoveImage'))) {
                    $image = base64_encode('default.png');
                    $edit = $edit.((strlen($edit) != 0)? ",": "")."`picture`='$image'";
                }
                $consult = $db->Query("UPDATE `students` SET $edit WHERE `id`=$idStudent");
                if ($consult) {
                    $usernameDirective = base64_decode($directive->getData_system($idDirective)['datas']['username']);
                    $records->create($idDirective, "El directivo @$usernameDirective edito la información del estudiante #$idStudent.", 1, "Editar estudiante", "Estudiantes");
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
                /* ################################################## */
                $verify = $permission->verify($idDirective, 2);
                if (is_object($verify)) return $verify;
                if (!$verify) return $responses->errorPermission;
                /* ################################################## */
                function orderStudents($data1, $data2) {
                    return base64_decode($data1['name']) > base64_decode($data2['name']);
                }
                /* ################################################## */
                $consult = $db->Query("SELECT * FROM `students`");
                if ($consult) {
                    $data = array();
                    while ($student = $consult->fetch_array()) {
                        array_push($data, array(
                            'id' => $student['id'],
                            'name' => $student['name'],
                            'dni' => $student['dni'],
                            'curse' => $student['curse'],
                            'tel' => $student['tel'],
                            'email' => $student['email'],
                            'date' => $student['date'],
                            'picture' => $student['picture']
                        ));
                    }
                    usort($data, "orderStudents");
                    return $responses->goodData($data);
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }
        public function system_getAllForCurse(string $curse) {
            $responses = new Responses();
            try {
                $db = new DBSystem();
                $consult = $db->Query("SELECT * FROM `students` WHERE `curse`='$curse'");
                if ($consult) {
                    $data = array();
                    while ($student = $consult->fetch_array()) {
                        array_push($data, array(
                            'id' => $student['id'],
                            'name' => $student['name'],
                            'dni' => $student['dni'],
                            'curse' => $student['curse'],
                            'tel' => $student['tel'],
                            'email' => $student['email'],
                            'date' => $student['date'],
                            'picture' => $student['picture']
                        ));
                    }
                    $data = array_sort($data, "name", SORT_ASC);
                    return $responses->goodData($data);
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }
        public function get_system($idStudent) {
            $responses = new Responses();
            try {
                $db = new DBSystem();
                $consult = $db->Query("SELECT * FROM `students` WHERE `id`=$idStudent");
                if ($consult) {
                    if ($consult->num_rows == 0) return $responses->error2;
                    $data = $consult->fetch_array();
                    return $responses->goodData(array(
                        'id' => $data['id'],
                        'name' => $data['name'],
                        'dni' => $data['dni'],
                        'curse' => $data['curse'],
                        'tel' => $data['tel'],
                        'email' => $data['email'],
                        'date' => $data['date'],
                        'picture' => $data['picture']
                    ));
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }

        public function get_console() {
            $responses = new Responses();
            try {
                $db = new DBSystem();
                $consult = $db->Query("SELECT * FROM `students`");
                if ($consult) {
                    $data = array();
                    while ($student = $consult->fetch_array()) {
                        array_push($data, array(
                            'id' => $student['id'],
                            'dni' => $student['dni'],
                            'curse' => $student['curse']
                        ));
                    }
                    return $responses->goodData($data);
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }

        // Family
        public function family_getStudentId(string $dni) {
            $responses = new Responses();
            try {
                $db = new DBSystem();
                $consult = $db->Query("SELECT * FROM `students` WHERE `dni`='$dni'");
                if ($consult) {
                    if ($consult->num_rows == 0) return $responses->error2;
                    $data = $consult->fetch_array();
                    return $data['id'];
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }
        public function family_logIn(string $dni) {
            $responses = new Responses();
            try {
                $db = new DBSystem();
                $consult = $db->Query("SELECT * FROM `students` WHERE `dni`='$dni'");
                if ($consult) {
                    if ($consult->num_rows == 0) return $responses->errorData("No se encontró el alumno.");
                    $data = $consult->fetch_array();
                    if ($data['curse'] == base64_encode('Archivados')) return $responses->errorData("No se encontró el alumno.");
                    return $responses->goodData($data['id']);
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }
        public function family_getData(string $idStudent) {
            $responses = new Responses();
            try {
                return $this->get_system($idStudent);
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }
    }
    
?>