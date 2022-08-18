<?php
    include_once 'SecurityKeyCodes.php';
    include_once 'res.php';

    class VerifyData {
        private $headerAdmin = 'Zr4u7x!A%D*G-KaNdRgUkXp2s5v8y/B?E(H+MbQeShVmYq3t6w9z$C&F)J@NcRfU';
        private $headerFamily = 'k3Ra4Q3HAL9MR7SAEPSNGY3mQNWsvWY2pLdLcu5LesH8rx6g2EFsrFAuCxsShbV7';

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
    }
    
?>