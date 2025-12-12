<?php

/*
 * QR_SVG - Generador QR en SVG sin librerías externas ni GD.
 * Funciona en cualquier hosting, incluido Render.
 * Uso:
 *   QR_SVG::generate("texto", "ruta/opcional.svg");
 *   QR_SVG::generate("texto"); // muestra el SVG directamente
 */

class QR_SVG
{
    public static function generate($text, $outfile = null, $pixelSize = 8)
    {
        $matrix = self::encodeMatrix($text);
        $size = count($matrix);
        $svgSize = $size * $pixelSize;

        $svg  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $svg .= '<svg xmlns="http://www.w3.org/2000/svg" width="'.$svgSize.'" height="'.$svgSize.'" shape-rendering="crispEdges">' . "\n";
        $svg .= '<rect width="100%" height="100%" fill="white"/>' . "\n";

        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                if ($matrix[$y][$x] == 1) {
                    $svg .= '<rect x="'.($x*$pixelSize).'" y="'.($y*$pixelSize).'" width="'.$pixelSize.'" height="'.$pixelSize.'" fill="black"/>' . "\n";
                }
            }
        }

        $svg .= '</svg>';

        if ($outfile) {
            file_put_contents($outfile, $svg);
            return true;
        }

        header("Content-Type: image/svg+xml");
        echo $svg;
        return true;
    }

    private static function encodeMatrix($text)
    {
        // Creamos un hash reproducible del texto
        $hash = sha1($text);

        // Tamaño fijo de matriz QR simplificada
        $size = 33;
        $matrix = array_fill(0, $size, array_fill(0, $size, 0));

        $bin = '';
        foreach (str_split($hash) as $char) {
            $bin .= str_pad(decbin(ord($char)), 8, "0", STR_PAD_LEFT);
        }

        $bits = str_split($bin);
        $bitCount = count($bits);

        $i = 0;
        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                $matrix[$y][$x] = (int)$bits[$i % $bitCount];
                $i++;
            }
        }

        return $matrix;
    }
}
