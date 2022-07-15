<?php
    include_once 'classes.php';

    class DirectivesPermissionSystem {
        public function create($idDirective, string $level) {
            $db = new DBSystem();
            $responses = new Responses();
            $consult = $db->Query("INSERT INTO `directives_permissions`(`id`, `id_directives`, `level`) VALUES (NULL, $idDirective, $level)");
            if ($consult) return $responses->good;
            return $responses->error2;
        }
        public function edit($idDirective, string $level) {
            $db = new DBSystem();
            $responses = new Responses();
            $consult = $db->Query("UPDATE `directives_permissions` SET `level`='$level' WHERE `id_directives`=$idDirective");
            if ($consult) return $responses->good;
            return $responses->error2;
        }
        public function delete($idDirective) {
            $db = new DBSystem();
            $responses = new Responses();
            $consult = $db->Query("DELETE FROM `directives_permissions` WHERE `id_directives`=$idDirective");
            if ($consult) return $responses->good;
            return $responses->error2;
        }
        public function getPermission($idDirective) {
            $db = new DBSystem();
            $responses = new Responses();
            $consult = $db->Query("SELECT * FROM `directives_permissions` WHERE `id_directives`=$idDirective");
            if ($consult) {
                $dataDirective = $consult->fetch_array();
                return $responses->goodData($dataDirective['level']);
            }
            return $responses->error2;
        }
        public function compare($idPrincipal, $idSecondary) {
            $responses = new Responses();
            $P1 = $this->getPermission($idPrincipal)['datas'];
            $S2 = $this->getPermission($idSecondary)['datas'];
            if ((int) $P1 > (int) $S2 && (int) $P1 !== (int) $S2) return true;
            if ((int) $P1 == (int) $S2) {
                if ((int) $idPrincipal < (int) $idSecondary) return true;
                return $responses->errorPermission;
            }
            return $responses->errorPermission;
        }
        public function verify($idDirective, $permission) {
            $responses = new Responses();
            try {
                $db = new DBSystem();
                $consult = $db->Query("SELECT * FROM `directives_permissions` WHERE `id_directives`=$idDirective");
                if ($consult) {
                    $arrayData = $consult->fetch_array();
                    return ((int) $arrayData['level'] >= $permission);
                }
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }
    }
    
?>