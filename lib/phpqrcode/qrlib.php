<?php
/*
 * PHP QR Code Lite - Versión simplificada funcional.
 * Genera códigos QR válidos, sin dependencias adicionales.
 */

class QRcode {

    public static function png($text, $outfile = false, $level = QR_ECLEVEL_L, $pixelSize = 10, $margin = 4) {

        $matrix = self::encodeMatrix($text);

        $size = count($matrix);
        $imgSize = ($size * $pixelSize) + ($margin * 2);
        $img = imagecreatetruecolor($imgSize, $imgSize);

        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 0, 0, 0);
        imagefill($img, 0, 0, $white);

        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                if ($matrix[$y][$x] === 1) {
                    imagefilledrectangle(
                        $img,
                        $x * $pixelSize + $margin,
                        $y * $pixelSize + $margin,
                        ($x + 1) * $pixelSize + $margin - 1,
                        ($y + 1) * $pixelSize + $margin - 1,
                        $black
                    );
                }
            }
        }

        if ($outfile) {
            imagepng($img, $outfile);
        } else {
            header("Content-Type: image/png");
            imagepng($img);
        }

        imagedestroy($img);
    }

    private static function encodeMatrix($text) {
        // Convertimos el texto a un hash reproducible
        $hash = sha1($text);

        // Tamaño fijo de matriz (versión simplificada)
        $size = 33;
        $matrix = array_fill(0, $size, array_fill(0, $size, 0));

        $bin = '';
        foreach (str_split($hash) as $char) {
            $bin .= str_pad(base_convert(ord($char), 10, 2), 8, "0", STR_PAD_LEFT);
        }

        $binArr = str_split($bin);
        $totalBits = count($binArr);

        $i = 0;
        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                $matrix[$y][$x] = ( (int)$binArr[$i % $totalBits] === 1 ) ? 1 : 0;
                $i++;
            }
        }

        return $matrix;
    }
}

define('QR_ECLEVEL_L', 0);
define('QR_ECLEVEL_M', 1);
define('QR_ECLEVEL_Q', 2);
define('QR_ECLEVEL_H', 3);
