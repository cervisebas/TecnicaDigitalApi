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
        public $errorDataPost = array('ok' => false, 'cause' => 'Los datos ingresados no eran los esperados.');

        public $errorUpdate = array('ok' => false, 'cause' => 'La versión de la aplicación que está ejecutando es antigua.', 'datas' => 'alert');
        public $errorIncompatible = array('ok' => false, 'cause' => 'Función incompatible o no implementada.', 'datas' => 'alert');
        public $errorNotData = array('ok' => false, 'cause' => 'Faltan datos a ingresar, por favor revise los datos ingresados.');
        
        public $errorScheduleRepeat = array('ok' => false, 'cause' => 'Ya se estableció un horario para este curso.');
        public $errorScheduleNotAvailable = array('ok' => false, 'cause' => 'No disponible.');
        
        public function goodData($data) {
            return array('ok' => true, 'cause' => '', 'datas' => $data);
        }
        public function errorData($data) {
            return array('ok' => false, 'cause' => $data);
        }
        public function error1Data($data) {
            return array('ok' => false, 'cause' => 'Ocurrio un error de parte del servidor.', 'datas' => $data);
        }

        public function writeError($error) {
            $fp = fopen('errores.txt', 'w');
            fwrite($fp, $error);
            fclose($fp);
        }
    }
?>