<?php
    include_once 'classes.php';

    class CursesGroupSystem {
        public function create($idDirective, string $curse, string $group, string $students) {
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
                $consult = $db->Query("INSERT INTO `curses_groups`(`id`, `curse`, `name_group`, `students`) VALUES (NULL, '$curse', '$group', '$students')");
                if ($consult) {
                    $usernameDirective = base64_decode($directive->getData_system($idDirective)['datas']['username']);
                    $records->create($idDirective, "El directivo @$usernameDirective creo un nuevo grupo.", 2, "Añadir grupo", "Grupos");
                    return $responses->good;
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }
        public function delete($idDirective, string $idGroup) {
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
                $consult = $db->Query("DELETE FROM `curses_groups` WHERE `id`=$idGroup");
                if ($consult) {
                    $usernameDirective = base64_decode($directive->getData_system($idDirective)['datas']['username']);
                    $records->create($idDirective, "El directivo @$usernameDirective elimino el grupo #$idGroup.", 1, "Borrar grupo", "Grupos");
                    return $responses->good;
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }
        public function modify($idDirective, $idGroup, string $curse, string $group, string $students) {
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
                $edit = "";
                (!$verifyData->is_empty($curse)) && $edit = $edit.((strlen($edit) != 0)? ",": "")."`curse`='$curse'";
                (!$verifyData->is_empty($group)) && $edit = $edit.((strlen($edit) != 0)? ",": "")."`name_group`='$group'";
                (!$verifyData->is_empty($students)) && $edit = $edit.((strlen($edit) != 0)? ",": "")."`students`='$students'";
                $consult = $db->Query("UPDATE `curses_groups` SET $edit WHERE `id`=$idGroup");
                if ($consult) {
                    $usernameDirective = base64_decode($directive->getData_system($idDirective)['datas']['username']);
                    $records->create($idDirective, "El directivo @$usernameDirective edito el grupo #$idGroup.", 1, "Editar grupo", "Grupos");
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
                $verify = $permission->verify($idDirective, 1);
                if (is_object($verify)) return $verify;
                if (!$verify) return $responses->errorPermission;
                /* ################################################## */
                $consult = $db->Query("SELECT * FROM `curses_groups`");
                if ($consult) {
                    $data = array();
                    while ($student = $consult->fetch_array()) {
                        array_push($data, array(
                            'id' => $student['id'],
                            'curse' => $student['curse'],
                            'name_group' => $student['name_group'],
                            'students' => $student['students']
                        ));
                    }
                    return $responses->goodData($data);
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }
    }
    
?>