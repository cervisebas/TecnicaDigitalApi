<?php
    include_once 'classes.php';

    class FileSystem {
        public function createStudentImage($file) {
            $ImgProcess = new ProcessImageSystem();
            $newName = 'student_'.random_int(11111111, 99999999).'.webp';
            $process = $ImgProcess->process($file['tmp_name'], $file['name'], 512, 512, true);
            $copy = imagewebp($process, './image/'.$newName, 70);
            return ($copy)? $newName: 'default.png';
        }
        public function createStudentImage2($file) {
            $ImgProcess = new ProcessImageSystem();
            $newName = 'student_'.random_int(11111111, 99999999).'.webp';
            $process = $ImgProcess->process3($file['tmp_name'], 512, 512);
            $copy = imagewebp($process, './image/'.$newName, 70);
            return ($copy)? $newName: 'default.png';
        }
    }
    
?>