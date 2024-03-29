<?php
    include_once 'classes.php';

    class DirectiveSystem {
        public function create($idDirective, string $name, string $position, string $dni, string $username, string $password, string $permissionLevel) {
            $responses = new Responses();
            try {
                $db = new DBSystem();
                $records = new RecordSystem();
                $permissions = new DirectivesPermissionSystem();
                $verifyData = new VerifyData();
                $fileSystem = new FileSystem();
                /* ################################################## */
                $verify = $permissions->verify($idDirective, 3);
                if (is_object($verify)) return $verify;
                if (!$verify) return $responses->errorPermission;
                /* ################################################## */
                $verify = $permissions->verify($idDirective, (int) $permissionLevel);
                if (is_object($verify)) return $verify;
                if (!$verify) return $responses->errorPermission;
                /* ################################################## */
                $image = base64_encode('default-admin.png');
                if ($verifyData->issetFilePost('image')) $image = base64_encode($fileSystem->createStudentImage($_FILES['image']));
                $encrypt = password_hash($password, PASSWORD_DEFAULT);
                $consult = $db->QueryAndConect("INSERT INTO `directives`(`id`, `name`, `position`, `dni`, `picture`, `username`, `password`) VALUES (NULL, '$name', '$position', '$dni', '$image', '$username', '$encrypt')");
                if ($consult['exec']) {
                    $newId = $consult['connection']->insert_id;
                    $permissions->create($newId, $permissionLevel);
                    $usernameDirective = base64_decode($this->getData_system($idDirective)['datas']['username']);
                    $records->create($idDirective, "El directivo @$usernameDirective añadió un nuevo directivo (#$newId).", 2, "Añadir directivo", "Directivos");
                    return $responses->good;
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }
        public function modify($idDirective, $idModify, $name, $position, $dni, $username, $password, $permissionLevel) {
            $responses = new Responses();
            try {
                $db = new DBSystem();
                $records = new RecordSystem();
                $permission = new DirectivesPermissionSystem();
                /* ################################################## */
                $verify = $permission->verify($idDirective, 3);
                if (is_object($verify)) return $verify;
                if (!$verify) return $responses->errorPermission;
                /* ################################################## */
                $verify2 = $permission->compare($idDirective, $idModify);
                if (is_object($verify2)) return $verify2;
                /* ################################################## */
                $verifyData = new VerifyData();
                $edit = "";
                function verifyCont($edit1) { return (strlen($edit1) != 0)? ",": ""; }
                (!$verifyData->is_empty($name)) && $edit = $edit."`name`='$name'";
                (!$verifyData->is_empty($position)) && $edit = $edit.verifyCont($edit)."`position`='$position'";
                (!$verifyData->is_empty($dni)) && $edit = $edit.verifyCont($edit)."`dni`='$dni'";
                (!$verifyData->is_empty($username)) && $edit = $edit.verifyCont($edit)."`username`='$username'";
                if (!$verifyData->is_empty($password)) {
                    $newPassword = password_hash($password, PASSWORD_DEFAULT);
                    $edit = $edit.verifyCont($edit)."`password`='$newPassword'";
                }
                if (!$verifyData->is_empty($permissionLevel)) {
                    $permission = $permission->edit($idModify, $permissionLevel);
                    if (is_object($permission)) return $permission;
                    if ($permission) {
                        $usernameDirective = base64_decode($this->getData_system($idDirective)['datas']['username']);
                        $records->create($idDirective, "El directivo @$usernameDirective cambio los permisos del directivo #$idModify.", 1, "Cambiar permisos", "Directivos");
                        return $responses->good;
                    }
                }
                $consult = $db->Query("UPDATE `directives` SET $edit WHERE `id`=$idModify");
                if ($consult) {
                    $usernameDirective = base64_decode($this->getData_system($idDirective)['datas']['username']);
                    $records->create($idDirective, "El directivo @$usernameDirective edito la información del directivo #$idModify.", 1, "Editar directivo", "Directivos");
                    return $responses->good;
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->error1Data(array($idDirective, $idModify, $name, $position, $dni, $username, $password, $permissionLevel));
            }
        }
        public function modifyImage($idDirective, $idModify) {
            $responses = new Responses();
            try {
                $db = new DBSystem();
                $records = new RecordSystem();
                $permission = new DirectivesPermissionSystem();
                /* ################################################## */
                $verify = $permission->verify($idDirective, 3);
                if (is_object($verify)) return $verify;
                if (!$verify) return $responses->errorPermission;
                /* ################################################## */
                $verify2 = $permission->compare($idDirective, $idModify);
                if (is_object($verify2)) return $verify2;
                /* ################################################## */
                $fileSystem = new FileSystem();
                $verifyData = new VerifyData();
                /* ################################################## */
                $image = "";
                if (!$verifyData->issetFilePost('image') && $verifyData->issetPosts(array('removeImage'))) {
                    $image = base64_encode('default-admin.png');
                } else {
                    $image = base64_encode($fileSystem->createDirectiveImage($_FILES['image']));
                }
                $consult = $db->Query("UPDATE `directives` SET `picture`='$image' WHERE `id`=$idModify");
                if ($consult) {
                    $usernameDirective = base64_decode($this->getData_system($idDirective)['datas']['username']);
                    $records->create($idDirective, "El directivo @$usernameDirective edito la imagen de perfil del directivo #$idModify.", 1, "Editar directivo", "Directivos");
                    return $responses->good;
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->error1Data($th);
            }
        }
        public function open(string $username, string $password) {
            $responses = new Responses();
            try {
                $db = new DBSystem();
                $record = new RecordSystem();
                $consult = $db->Query("SELECT * FROM `directives` WHERE `username`='$username'");
                if ($consult) {
                    if ($consult->num_rows !== 0) {
                        $data = $consult->fetch_array();
                        if (password_verify($password, $data['password'])) {
                            $date = date('d/m/Y');
                            $newUsername = base64_decode($data['username']);
                            $record->create($data['id'], "Inicio de sesión detectado: @$newUsername.", 3, "Inicio de sesión", "Directivo");
                            return $responses->goodData(array(
                                'id' => $data['id'],
                                'picture' => $data['picture'],
                                'username' => $data['username'],
                                'name' => $data['name'],
                                'password' => $password,
                                'date' => $date
                            ));
                        }
                        return $responses->userError2;
                    }
                    return $responses->userError1;
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }
        public function getDirectiveId(string $username, string $password) {
            $responses = new Responses();
            try {
                $db = new DBSystem();
                $consult = $db->Query("SELECT * FROM `directives` WHERE `username`='$username'");
                if ($consult) {
                    if ($consult->num_rows !== 0) {
                        $data = $consult->fetch_array();
                        if (password_verify($password, $data['password'])) return $data['id'];
                        return $responses->userError2;
                    }
                    return $responses->userError1;
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }
        public function delete($idDirective, $idDelete) {
            $responses = new Responses();
            $delimiters = array(1, 5, 10, 14, 23);
            try {
                $db = new DBSystem();
                $permission = new DirectivesPermissionSystem();
                $records = new RecordSystem();
                /* ################################################## */
                if (!!array_search((int) $idDelete, $delimiters)) return $responses->errorData("No puedes borrar a un creador.");
                /* ################################################## */
                if ((int) $idDirective == (int) $idDelete) return $responses->errorData("No te puedes eliminar a ti mismo.");
                /* ################################################## */
                $verify = $permission->verify($idDirective, 3);
                if (is_object($verify)) return $verify;
                if (!$verify) return $responses->errorPermission;
                /* ################################################## */
                $compare = $permission->compare($idDirective, $idDelete);
                if (is_object($compare)) return $compare;
                /* ################################################## */
                $consult = $db->Query("DELETE FROM `directives` WHERE `id`=$idDelete");
                if ($consult) {
                    $permission->delete($idDelete);
                    $usernameDirective = base64_decode($this->getData_system($idDirective)['datas']['username']);
                    $records->create($idDirective, "El directivo @$usernameDirective elimino al directivo #$idDelete.", 1, "Borrar directivo", "Directivos");
                    return $responses->good;
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }
        public function getAllDirectives($idDirective) {
            $responses = new Responses();
            try {
                $db = new DBSystem();
                $permission = new DirectivesPermissionSystem();
                /* ################################################## */
                $verify = $permission->verify($idDirective, 2);
                if (is_object($verify)) return $verify;
                if (!$verify) return $responses->errorPermission;
                /* ################################################## */
                function orderDirectives($data1, $data2) {
                    return base64_decode($data1['name']) > base64_decode($data2['name']);
                }
                /* ################################################## */
                $consult = $db->Query("SELECT * FROM `directives`");
                if ($consult) {
                    $result = array();
                    while ($directive = $consult->fetch_array()) {
                        $getPermission = $permission->getPermission($directive['id']);
                        if (!$getPermission['ok']) return $permission;
                        array_push($result, array(
                            'id' => $directive['id'],
                            'name' => $directive['name'],
                            'position' => $directive['position'],
                            'dni' => $directive['dni'],
                            'picture' => $directive['picture'],
                            'username' => $directive['username'],
                            'permission' => $getPermission['datas']
                        ));
                    }
                    usort($result, "orderDirectives");
                    $admins = array(1, 5, 10, 14, 23);
                    foreach ($admins as $key => $value) {
                        $this->repositionArrayElement($result, $value, $key);
                    }
                    return $responses->goodData($result);
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }

        private function repositionArrayElement(array &$array, $key, int $order) {
            if(($a = array_search($key, array_column($array, 'id'))) === false) return;
            $p1 = array_splice($array, $a, 1);
            $p2 = array_splice($array, 0, $order);
            $array = array_merge($p2, $p1, $array);
        }


        public function getData_system($idDirective) {
            $responses = new Responses();
            try {
                if ($idDirective == -69 || $idDirective == '-69') return $responses->goodData(array(
                    'id' => 'TC',
                    'name' => base64_encode('Técnica Consola'),
                    'position' => base64_encode('Consola'),
                    'username' => base64_encode('console-tecnica'),
                    'picture' => base64_encode('console.png')
                ));
                if ($idDirective == -2 || $idDirective == '-2') return $responses->goodData(array(
                    'id' => 'SV',
                    'name' => base64_encode('Técnica Server'),
                    'position' => base64_encode('Servidor'),
                    'username' => base64_encode('server-tecnica'),
                    'picture' => base64_encode('server.png')
                ));
                $db = new DBSystem();
                $default = array(
                    'id' => '-1',
                    'name' => base64_encode('Usuario perdido'),
                    'position' => base64_encode('Ninguna'),
                    'username' => base64_encode('ninguna'),
                    'picture' => base64_encode('default-admin-bad.png')
                );
                $consult = $db->Query("SELECT * FROM `directives` WHERE `id`=$idDirective");
                if ($consult) {
                    if ($consult->num_rows == 0) return $responses->goodData($default);
                    $dataUser = $consult->fetch_array();
                    return $responses->goodData(array(
                        'id' => $dataUser['id'],
                        'name' => $dataUser['name'],
                        'position' => $dataUser['position'],
                        'username' => $dataUser['username'],
                        'picture' => $dataUser['picture']
                    ));
                }
                return $responses->goodData($default);
            } catch (\Throwable $th) {
                return $responses->goodData($default);
            }
        }
    }
    
?>