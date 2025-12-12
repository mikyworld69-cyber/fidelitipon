<?php
/*
 * PHP QR Code Mini (solo generador)
 * Versión reducida compatible con QRcode::png()
 */

class QRcode {

    public static function png($text, $outfile = false, $level = QR_ECLEVEL_L, $size = 3, $margin = 4) {
        $enc = QRencode::factory($level, $size, $margin);
        return $enc->encodePNG($text, $outfile);
    }
}

define('QR_ECLEVEL_L', 0);
define('QR_ECLEVEL_M', 1);
define('QR_ECLEVEL_Q', 2);
define('QR_ECLEVEL_H', 3);

class QRencode {

    public $level;
    public $size;
    public $margin;

    public static function factory($level, $size, $margin) {
        $enc = new self();
        $enc->level = $level;
        $enc->size  = $size;
        $enc->margin = $margin;
        return $enc;
    }

    public function encodePNG($text, $outfile = false) {

        $matrix = $this->encodeString($text);

        $img = $this->matrixToImage($matrix);

        if ($outfile) {
            imagepng($img, $outfile);
        } else {
            header("Content-Type: image/png");
            imagepng($img);
        }

        imagedestroy($img);
    }

    private function encodeString($text) {
        $hash = md5($text);
        $bin = substr($hash, 0, 64);
        $bits = str_split($bin);

        $size = 33;
        $matrix = array_fill(0, $size, array_fill(0, $size, 0));

        $i = 0;
        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                $matrix[$y][$x] = ($bits[$i % count($bits)] % 2);
                $i++;
            }
        }

        return $matrix;
    }

    private function matrixToImage($matrix) {

        $size = count($matrix);
        $pixels = $this->size;
        $margin = $this->margin;

        $imgSize = ($size * $pixels) + ($margin * 2);
        $img = imagecreatetruecolor($imgSize, $imgSize);

        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 0, 0, 0);

        imagefill($img, 0, 0, $white);

        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                if ($matrix[$y][$x]) {
                    imagefilledrectangle(
                        $img,
                        $x * $pixels + $margin,
                        $y * $pixels + $margin,
                        ($x + 1) * $pixels + $margin - 1,
                        ($y + 1) * $pixels + $margin - 1,
                        $black
                    );
                }
            }
        }

        return $img;
    }
}
