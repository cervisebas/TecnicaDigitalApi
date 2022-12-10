<?php
    include_once 'SecurityKeyCodes.php';
    include_once 'res.php';

    class VerifyData {
        private $headerAdmin = 'Zr4u7x!A%D*G-KaNdRgUkXp2s5v8y/B?E(H+MbQeShVmYq3t6w9z$C&F)J@NcRfU';
        private $headerFamily = 'k3Ra4Q3HAL9MR7SAEPSNGY3mQNWsvWY2pLdLcu5LesH8rx6g2EFsrFAuCxsShbV7';
        private $AppVersionAccept = 44;

        public function issetDataPost($array) {
            $final = true;
            for ($i=0; $i < count($array); $i++) {
                if (isset($_POST[$array[$i]])) {
                    if (empty($_POST[$array[$i]])) {
                        $final = false;
                    }
                } else {
                    $final = false;
                }
            }
            return $final;
        }
        public function issetPosts($array) {
            $final = true;
            for ($i=0; $i < count($array); $i++) {
                if (!isset($_POST[$array[$i]])) $final = false;
            }
            return $final;
        }
        public function issetFilePost(string $file) {
            if (isset($_FILES[$file])) {
                if ($_FILES[$file]['name'] !== null) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
        public function is_empty($value) {
            return ($value == NULL || $value == "");
        }
        public function verifyHeaders() {
            try {
                $responses = new Responses();
                $securityKeyCodes = new SecurityKeyCodes();
                $autorization = base64_decode((isset($headers['Authorization']))? $headers['Authorization']: ((isset($headers['authorization']))? $headers['authorization']: $_SERVER['HTTP_AUTHORIZATION']));
                
                if (!$this->verifyAppVersion()) return $responses->errorUpdate;
                
                /*if ($autorization == $this->headerAdmin) return array('ok' => true, 'admin' => true);
                if ($autorization == $this->headerFamily) return array('ok' => true, 'admin' => false);*/
                if ($autorization == $this->headerAdmin) return $responses->errorUpdate;
                if ($autorization == $this->headerFamily) return $responses->errorUpdate;
                /*if ($autorization == $securityKeyCodes->keyCodeAdmin) return $responses->errorUpdate;
                if ($autorization == $securityKeyCodes->keyCodeFamily) return $responses->errorUpdate;*/
                if ($autorization == $securityKeyCodes->keyCodeAdmin) return array('ok' => true, 'admin' => true);
                if ($autorization == $securityKeyCodes->keyCodeConsole) return array('ok' => true, 'admin' => true);
                if ($autorization == $securityKeyCodes->keyCodeFamily) return array('ok' => true, 'admin' => false);
                return false;
            } catch (\Throwable $th) {
                return false;
            }
        }
        public function verifyAppVersion() {
            //$responses = new Responses();
            try {
                // If is Console
                $console = new ConsoleSystem();
                if ($this->issetDataPost(array('keyAccess', 'dateAccess'))) {
                    $verify = $console->verify($_POST['keyAccess'], $_POST['dateAccess']);
                    if (is_object($verify)) return false;
                    return true;
                }

                // If is App
                $AppVersion = (isset($headers['AppVersion']))? $headers['AppVersion']: ((isset($headers['appversion']))? $headers['appversion']: $_SERVER['HTTP_APPVERSION']);
                $Codes = explode('.', $AppVersion);
                if (count($Codes) >= 2) {
                    $VersionNumber = intval($Codes[0].$Codes[1]);
                    //$responses->writeError("App: $VersionNumber - Server: $this->AppVersionAccept");
                    if ($this->AppVersionAccept <= $VersionNumber) return true;
                }
                return false;
            } catch (\Throwable $th) {
                return false;
            }            
        }
        public function getAppVersion() {
            try {
                $AppVersion = (isset($headers['AppVersion']))? $headers['AppVersion']: ((isset($headers['appversion']))? $headers['appversion']: $_SERVER['HTTP_APPVERSION']);
                $Codes = explode('.', $AppVersion);
                $VersionNumber = intval($Codes[0].$Codes[1]);
                return $VersionNumber;
            } catch (\Throwable $th) {
                return 0;
            }            
        }
        
        // Check types data
        public function checkDataTypes(...$datas) {
            $responses = new Responses();
            $result = $this->checkDataTypesResult($datas);
            if (!$result) {
                echo json_encode($responses->errorDataPost);
                exit();
            }
        }
        private function checkDataTypesResult($datas) {
            try {
                $order = $this->orderData($datas);
                $result = true;
                foreach ($order as $value) {
                    if (!$this->checkType($value[0], $value[1])) {
                        $result = false;
                    }
                }
                return $result;
            } catch (\Throwable $th) {
                return false;
            }
        }
        private function orderData($datas) {
            $result = array();
            $used = array();
            for ($i=0; $i < count($datas); $i++) {
                if (!in_array($i, $used)) {
                    array_push($result, array(
                        $datas[$i],
                        $datas[$i + 1]
                    ));
                    array_push($used, $i);
                    array_push($used, $i + 1);
                }
            }
            return $result;
        }
        private function checkType($data, string $type) {
            $result = false;
            switch ($type) {
                case 'string':
                    $result = is_string($data);
                    break;
                case 'int':
                    $result = is_int($data);
                    break;
                case 'float':
                    $result = is_float($data);
                    break;
                case 'number':
                    $result = is_numeric($data);
                    break;
                case 'bool':
                    $result = is_bool($data);
                    break;
                case 'base64':
                    $result = $this->is_base64($data);
                    break;
                case 'string-base64':
                    $result = is_string($data) && $this->is_base64($data);
                    break;
                case 'cboolean':
                    $result = ($data == '0' || $data == '1')? true: false;
                    break;
                case 'file':
                    $result = is_file($data);
                    break;
            }
            return $result;
        }
        private function is_base64(string $data) {
            try {
                if (base64_encode(base64_decode($data, true)) === $data) return true;
                return false;
            } catch (\Throwable $th) {
                return false;
            }
        }

        // Utils
        public function checkHourBetween(string $start, string $end, string $check) {
            $currentTime = strtotime($check);
            $startTime = strtotime($start);
            $endTime = strtotime($end);
            return (($startTime < $endTime && $currentTime >= $startTime && $currentTime <= $endTime) || ($startTime > $endTime && ($currentTime >= $startTime || $currentTime <= $endTime)));
        }
    }
    
?>