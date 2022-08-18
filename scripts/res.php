<?php
    class Responses {
        public $good = array('ok' => true, 'cause' => '');

        public $errorTypical = array('ok' => false, 'cause' => 'Ocurrio un error de parte del servidor.'); 

        public $error1 = array('ok' => false, 'cause' => 'Ocurrio un error de parte del servidor.');
        public $error2 = array('ok' => false, 'cause' => 'Ocurrio un error al acceder a la base de datos.');
        public $error3 = array('ok' => false, 'cause' => 'Ocurrio un error desconocido.');
        
        public $userError1 = array('ok' => false, 'cause' => 'No se encontro el nombre de usuario.');
        public $userError2 = array('ok' => false, 'cause' => 'La contraseña ingresada es incorrecta.');
        
        public $errorPermission = array('ok' => false, 'cause' => 'No posee los permisos suficientes.');
        public $errorHeader = array('ok' => false, 'cause' => 'LLave de seguridad incorrecta.');
        public $errorNoPost = array('ok' => false, 'cause' => 'No se detecto ningun dato.');

        public $errorUpdate = array('ok' => false, 'cause' => 'La versión de la aplicación que está ejecutando es antigua.', 'datas' => 'alert');
        
        public function goodData($data) {
            return array('ok' => true, 'cause' => '', 'datas' => $data);
        }
        public function errorData($data) {
            return array('ok' => false, 'cause' => $data);
        }
        public function error1Data($data) {
            return array('ok' => false, 'cause' => 'Ocurrio un error de parte del servidor.', 'datas' => $data);
        }
    }
?>