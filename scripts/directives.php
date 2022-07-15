<?php
    include_once 'classes.php';

    class DirectiveSystem {
        public function create($idDirective, string $name, string $position, string $dni, string $username, string $password, string $permissionLevel) {
            $responses = new Responses();
            try {
                $db = new DBSystem();
                $permissions = new DirectivesPermissionSystem();
                $verifyData = new VerifyData();
                $fileSystem = new FileSystem();
                /* ################################################## */
                $verify = $permissions->verify($idDirective, 4);
                if (is_object($verify)) return $verify;
                if (!$verify) return $responses->errorPermission;
                /* ################################################## */
                $image = base64_encode('default-admin.png');
                if ($verifyData->issetFilePost('image')) $image = base64_encode($fileSystem->createStudentImage($_FILES['image']));
                $encrypt = password_hash($password, PASSWORD_DEFAULT);
                $consult = $db->QueryAndConect("INSERT INTO `directives`(`id`, `name`, `position`, `dni`, `picture`, `username`, `password`) VALUES (NULL, '$name', '$position', '$dni', '$image', '$username', '$encrypt')");
                if ($consult['exec']) {
                    $permissions->create($consult['connection']->insert_id, $permissionLevel);
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
                $permission = new DirectivesPermissionSystem();
                /* ################################################## */
                $verify = $permission->verify($idDirective, 4);
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
                    if ($permission) return $responses->good;
                }
                $consult = $db->Query("UPDATE `directives` SET $edit WHERE `id`=$idModify");
                if ($consult) {
                    return $responses->good;
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->error1Data(array($idDirective, $idModify, $name, $position, $dni, $username, $password, $permissionLevel));
            }
        }
        public function open(string $username, string $password) {
            $responses = new Responses();
            try {
                $db = new DBSystem();
                $consult = $db->Query("SELECT * FROM `directives` WHERE `username`='$username'");
                if ($consult) {
                    if ($consult->num_rows !== 0) {
                        $data = $consult->fetch_array();
                        if (password_verify($password, $data['password'])) {
                            $date = date('d/m/Y');
                            return $responses->goodData(array(
                                'id' => $data['id'],
                                'picture' => $data['picture'],
                                'username' => $data['username'],
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
            try {
                $db = new DBSystem();
                $permission = new DirectivesPermissionSystem();
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
                $verify = $permission->verify($idDirective, 4);
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
                    return $responses->goodData($result);
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }


        public function getData_system($idDirective) {
            $responses = new Responses();
            try {
                $db = new DBSystem();
                $default = array(
                    'id' => '-1',
                    'name' => base64_encode('Usuario perdido'),
                    'position' => base64_encode('Ninguna'),
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