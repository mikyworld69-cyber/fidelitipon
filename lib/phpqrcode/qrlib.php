<?php

class QR_SVG
{
    public static function generate($text, $outfile = null, $size = 6)
    {
        $matrix = self::encodeMatrix($text);

        $svg  = '<?xml version="1.0" standalone="no"?>' . "\n";
        $svg .= '<svg xmlns="http://www.w3.org/2000/svg" width="'.(count($matrix)*$size).'" height="'.(count($matrix)*$size).'" shape-rendering="crispEdges">' . "\n";
        $svg .= '<rect width="100%" height="100%" fill="white"/>' . "\n";

        for ($y = 0; $y < count($matrix); $y++) {
            for ($x = 0; $x < count($matrix); $x++) {
                if ($matrix[$y][$x]) {
                    $svg .= '<rect x="'.($x*$size).'" y="'.($y*$size).'" width="'.$size.'" height="'.$size.'" fill="black"/>' . "\n";
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
        $hash = sha1($text);
        $size = 33;

        $matrix = array_fill(0, $size, array_fill(0, $size, 0));
        $bits = str_split($hash);

        $i = 0;
        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                $matrix[$y][$x] = (ord($bits[$i % strlen($hash)]) % 2);
                $i++;
            }
        }

        return $matrix;
    }
}
