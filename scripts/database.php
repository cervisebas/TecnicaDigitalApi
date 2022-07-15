<?php
    class DBSystem {
        private string $user = "root";
        private string $password = "";
        private string $database = "tecnica_digital";
        public function Query(string $sql) {
            $conexion = new mysqli("localhost", $this->user, $this->password, $this->database) or die ("No se pudo conectar");
            $exec = $conexion->query($sql);
            $conexion->close();
            return $exec;
        }
        public function QueryAndConect(string $sql) {
            $conexion = new mysqli("localhost", $this->user, $this->password, $this->database) or die ("No se pudo conectar");
            $exec = $conexion->query($sql);
            return array(
                'exec' => $exec,
                'connection' => $conexion
            );
        }
    }
?>