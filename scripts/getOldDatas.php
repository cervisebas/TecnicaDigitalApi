<?php
    include_once "classes.php";

    class GetOldDatas {
        public function getAll($idDirective) {
            $responses = new Responses();
            try {
                $permission = new DirectivesPermissionSystem();
                /* ################################################## */
                $verify = $permission->verify($idDirective, 1);
                if (is_object($verify)) return $verify;
                if (!$verify) return $responses->errorPermission;
                /* ################################################## */

                // Search JSON's files
                $files = array();
                $scan = scandir("./olds");
                foreach ($scan as $file) {
                    if (strpos($file, '.json') !== false) array_push($files, $file);
                }

                // Get data of the JSON's
                $result = array();
                foreach ($files as $file) {
                    $read = file_get_contents("./olds/$file");
                    if ($read !== false) {
                        $convert = json_decode($read, true);
                        if ($convert !== false) array_push($result, array(
                            "age" => str_replace(".json", "", str_replace("index_", "", $file)),
                            "data" => $convert
                        ));
                    }
                }

                return $responses->goodData($result);
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }
        public function get($idDirective, string $age) {
            $responses = new Responses();
            try {
                $permission = new DirectivesPermissionSystem();
                /* ################################################## */
                $verify = $permission->verify($idDirective, 1);
                if (is_object($verify)) return $verify;
                if (!$verify) return $responses->errorPermission;
                /* ################################################## */

                $uri = "./olds/index_$age.json";
                $read = file_get_contents($uri);
                if ($read !== false) {
                    $convert = json_decode($read, true);
                    if ($convert !== false) return $responses->goodData(null);
                    return $responses->errorNoDecode;
                }
                return $responses->errorNoFile;
            } catch (\Throwable $th) {
                return $responses->error1;
            }
        }
    }
    
?>