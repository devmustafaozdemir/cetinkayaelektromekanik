<?php
// One-off helper: cleans and crops supplied brand logos into assets/img/brands/<slug>.webp
// Usage: php tools/prepare-brand-logos.php <src> <slug> [knockout-light-bg]
[$_, $src, $slug] = $argv;
$knock = !empty($argv[3]);
$im = imagecreatefromstring(file_get_contents($src));
imagepalettetotruecolor($im);
imagealphablending($im, false);
imagesavealpha($im, true);
$w = imagesx($im); $h = imagesy($im);
$minX = $w; $minY = $h; $maxX = 0; $maxY = 0;
for ($y = 0; $y < $h; $y++) {
    for ($x = 0; $x < $w; $x++) {
        $c = imagecolorat($im, $x, $y);
        $a = ($c >> 24) & 127; $r = ($c >> 16) & 255; $g = ($c >> 8) & 255; $b = $c & 255;
        if ($knock && $a < 127) {
            // Light, unsaturated pixels (white or checkerboard grey) become transparent;
            // anti-aliased edges keep partial alpha based on how light they are.
            $max = max($r, $g, $b); $min = min($r, $g, $b);
            if ($max - $min < 28) {
                $light = $min;
                if ($light >= 222) { imagesetpixel($im, $x, $y, imagecolorallocatealpha($im, 255, 255, 255, 127)); continue; }
                if ($light > 150) {
                    $alpha = (int)round(127 * ($light - 150) / 72);
                    imagesetpixel($im, $x, $y, imagecolorallocatealpha($im, $r, $g, $b, $alpha));
                    $a = $alpha;
                }
            }
        }
        $isWhite = $r > 245 && $g > 245 && $b > 245;
        if ($a < 110 && !$isWhite) { $minX = min($minX, $x); $maxX = max($maxX, $x); $minY = min($minY, $y); $maxY = max($maxY, $y); }
    }
}
$cw = $maxX - $minX + 1; $ch = $maxY - $minY + 1;
$scale = min(1, 180 / $ch, 900 / $cw);
$nw = (int)round($cw * $scale); $nh = (int)round($ch * $scale);
$out = imagecreatetruecolor($nw, $nh);
imagealphablending($out, false); imagesavealpha($out, true);
imagefill($out, 0, 0, imagecolorallocatealpha($out, 0, 0, 0, 127));
imagecopyresampled($out, $im, 0, 0, $minX, $minY, $nw, $nh, $cw, $ch);
$dst = __DIR__ . '/../assets/img/brands/' . $slug . '.webp';
imagewebp($out, $dst, 90);
echo "$slug: {$nw}x{$nh} crop=({$minX},{$minY}) " . filesize($dst) . " bytes\n";
