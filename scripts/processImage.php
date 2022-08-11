<?php
    include_once 'libs/WideImage/WideImage.php';

    class ProcessImageSystem {
        public function process3($file, $w, $h) {
            $tmpName = './tmp_files/tmp_'.random_int(11111111, 99999999).'.png';
            WideImage::load($file)->resize($w, $h, 'outside', 'down')->saveToFile($tmpName);
            $image = imagecreatefrompng($tmpName);
            $this->image_fix_orientation($image, $file);
            unlink($tmpName);
            imagepng($image, $tmpName);
            WideImage::load($tmpName)->crop('center', 'center', $w, $h)->saveToFile($tmpName);
            $image = imagecreatefrompng($tmpName);
            unlink($tmpName);
            return $image;
        }
        public function process2($file, $w, $h, $crop = false) {
            list($width, $height) = getimagesize($file);
            $r = $width / $height;
            if ($crop) {
                if ($width > $height) {
                    $width = ceil($width-($width*abs($r-$w/$h)));
                } else {
                    $height = ceil($height-($height*abs($r-$w/$h)));
                }
                $newwidth = $w;
                $newheight = $h;
            } else {
                if ($w/$h > $r) {
                    $newwidth = $h*$r;
                    $newheight = $h;
                } else {
                    $newheight = $w/$r;
                    $newwidth = $w;
                }
            }
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            switch ($ext) {
                case 'png':
                    $src = imagecreatefrompng($file);
                    break;
                case 'jpg' || 'jpeg':
                    $src = imagecreatefromjpeg($file);
                    break;
                case 'webp':
                    $src = imagecreatefromwebp($file);
                    break;
            }

            $dst = imagecreatetruecolor($newwidth, $newheight);
            $this->image_fix_orientation($src, $file);
            imagecopyresized($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
            return $dst;
        }
        function image_fix_orientation(&$image, $filename) {
            $image = imagerotate($image, array_values([0, 0, 0, 180, 0, 0, -90, 0, 90])[@exif_read_data($filename)['Orientation'] ?: 0], 0);
        }
        public function process($file, $fileName, $w, $h, $crop=FALSE) {
            list($width, $height) = getimagesize($file);
            $r = $width / $height;
            if ($crop) {
                if ($width > $height) {
                    $width = ceil($width-($width*abs($r-$w/$h)));
                } else {
                    $height = ceil($height-($height*abs($r-$w/$h)));
                }
                $newwidth = $w;
                $newheight = $h;
            } else {
                if ($w/$h > $r) {
                    $newwidth = $h*$r;
                    $newheight = $h;
                } else {
                    $newheight = $w/$r;
                    $newwidth = $w;
                }
            }
            $ext = pathinfo($fileName, PATHINFO_EXTENSION);
            switch ($ext) {
                case 'png':
                    $src = imagecreatefrompng($file);
                    break;
                case 'jpg' || 'jpeg':
                    $src = imagecreatefromjpeg($file);
                    break;
                case 'webp':
                    $src = imagecreatefromwebp($file);
                    break;
            }
            if ($this->isVertical($width, $height)) {
                $dst = imagecreatetruecolor($newwidth, $newheight);
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
                $dst2 = imagerotate($dst, -90, 0);
                return $dst2;
            } else {
                $dst = imagecreatetruecolor($newwidth, $newheight);
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
                return $dst;
            }
        }
        private function isVertical($w, $h) {
            $width = ($w < 0)? ($w * -1): $w;
            $height = ($h < 0)? ($h * -1): $h;
            return ($width < $height);
        }
    }
?>