<?php
    include_once 'classes.php';

    class ConsoleSystem {
        private $codeKeyAccess = "UGLWMuP70e6cNoLLTHf6pN5O8ACdRYGpeV3Is2vMq9BdhzqmbI";
        public function verify(string $code, string $now) {
            $responses = new Responses();
            try {
                if ($code == $this->codeKeyAccess) {
                    $strdate = date("d/m/Y");
                    if (base64_decode($now) == $strdate) return true;
                }
                return $responses->errorPermission;
            } catch (\Throwable $th) {
                return $responses->errorPermission;
            }
        }
    }
    
?>