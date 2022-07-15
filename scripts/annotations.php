<?php
    include 'classes.php';

    class AnnotationSystem {
        public function set($idDirective, string $idGroup, string $note) {
            $responses = new Responses();
            try {
                $db = new DBSystem();
                $permission = new DirectivesPermissionSystem();
                /* ################################################## */
                $verify = $permission->verify($idDirective, 1);
                if (is_object($verify)) return $verify;
                if (!$verify) return $responses->errorPermission;
                /* ################################################## */
                $date = date("d/m/Y");
                $hour = date("H:i");
                $consult = $db->Query("INSERT INTO `annotations`(`id`, `id_group`, `id_directive`, `date`, `hour`, `note`) VALUES (NULL, $idGroup, $idDirective, '$date', '$hour', '$note')");
                if ($consult) return $responses->good;
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }
        public function delete($idDirective, $idAnnotation) {
            $responses = new Responses();
            try {
                $db = new DBSystem();
                $permission = new DirectivesPermissionSystem();
                /* ################################################## */
                $verify = $permission->verify($idDirective, 1);
                if (is_object($verify)) return $verify;
                if (!$verify) return $responses->errorPermission;
                /* ################################################## */
                $consult = $db->Query("DELETE FROM `annotations` WHERE `id`=$idAnnotation");
                if ($consult) return $responses->good;
                return $responses->error2;
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }
        public function getAll($idDirective, $idGroup) {
            $responses = new Responses();
            try {
                $db = new DBSystem();
                $permission = new DirectivesPermissionSystem();
                $directives = new DirectiveSystem();
                $assist = new AssistSystem();
                /* ################################################## */
                $verify = $permission->verify($idDirective, 1);
                if (is_object($verify)) return $verify;
                if (!$verify) return $responses->errorPermission;
                /* ################################################## */
                $consult = $db->Query("SELECT * FROM `annotations` WHERE `id_group`=$idGroup");
                if ($consult) {
                    $result = array();
                    while ($annotation = $consult->fetch_array()) {
                        $groupData = $assist->get3_system($annotation['id_group'])['datas'];
                        $directiveData = $directives->getData_system($annotation['id_directive'])['datas'];
                        array_push($result, array(
                            'id' => $annotation['id'],
                            'group' => $groupData,
                            'directive' => $directiveData,
                            'date' => $annotation['date'],
                            'hour' => $annotation['hour'],
                            'note' => $annotation['note']
                        ));
                    }
                    return $responses->goodData($result);
                }
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }

        public function get_system_count($idGroup) {
            $db = new DBSystem();
            $consult = $db->Query("SELECT * FROM `annotations` WHERE `id_group`=$idGroup");
            if ($consult) return $consult->num_rows;
            return 0;
        }
    }
    
?>