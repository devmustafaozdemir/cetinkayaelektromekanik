<?php
declare(strict_types=1);

/**
 * Product "model" specs drive both the server-side 2D drawing (SVG, below) and the
 * lazy-loaded 3D viewer (assets/src/viewer3d.js). A spec is "type:variant", e.g.
 * "tank:galvaniz" or "booster:3". Keep the two renderers in sync when adding types.
 */
function model_types(): array
{
    return [
        'tank:galvaniz'    => 'Modüler depo – galvaniz',
        'tank:paslanmaz'   => 'Modüler depo – paslanmaz',
        'tank:grp'         => 'Modüler depo – GRP',
        'tank:sandvic'     => 'Modüler depo – izolasyonlu',
        'booster:1'        => 'Hidrofor – tek pompalı',
        'booster:2'        => 'Hidrofor – çift pompalı',
        'booster:3'        => 'Hidrofor – üç pompalı',
        'booster:4'        => 'Hidrofor – dört pompalı',
        'pump:horizontal'  => 'Pompa – yatay santrifüj',
        'pump:vertical'    => 'Pompa – dikey çok kademeli',
        'pump:circulator'  => 'Pompa – sirkülasyon',
        'sub:deep'         => 'Dalgıç – derin kuyu',
        'sub:drain'        => 'Dalgıç – drenaj',
    ];
}

/** Category drawing keys (stored in product_categories.art) → default model spec. */
function art_default_model(string $art): string
{
    return ['tank' => 'tank:galvaniz', 'booster' => 'booster:3', 'pump' => 'pump:horizontal', 'submersible' => 'sub:deep', 'drop' => 'tank:grp'][$art] ?? 'tank:galvaniz';
}

function product_model(array $p): string
{
    $m = (string)($p['model'] ?? '');
    return isset(model_types()[$m]) ? $m : art_default_model((string)($p['art'] ?? 'tank'));
}

function tank_materials(): array
{
    // [left face, right face, top, rib/edge]
    return [
        'galvaniz'  => ['#c9d0d7', '#a9b2bc', '#dde2e7', '#7f8a96'],
        'paslanmaz' => ['#e3e7eb', '#c3cad1', '#f1f3f5', '#8d98a3'],
        'grp'       => ['#7fa9c4', '#5f8aa6', '#9cc0d6', '#406a86'],
        'sandvic'   => ['#eeeeea', '#d3d4cf', '#f8f8f5', '#9a9c95'],
    ];
}

/** Returns the 2D drawing for a model spec as inline SVG. */
function model_svg(string $spec, string $class = 'art', array $opt = []): string
{
    static $n = 0;
    $n++;
    $id = 'm' . $n;
    [$type, $variant] = array_pad(explode(':', $spec, 2), 2, '');
    $defs = '<defs>'
        . '<linearGradient id="' . $id . 'cv" x1="0" x2="1"><stop offset="0" stop-color="#8793a0"/><stop offset=".35" stop-color="#eef1f4"/><stop offset=".6" stop-color="#c3cbd3"/><stop offset="1" stop-color="#6f7b88"/></linearGradient>'
        . '<linearGradient id="' . $id . 'ch" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#8793a0"/><stop offset=".35" stop-color="#eef1f4"/><stop offset=".6" stop-color="#c3cbd3"/><stop offset="1" stop-color="#6f7b88"/></linearGradient>'
        . '<linearGradient id="' . $id . 'bv" x1="0" x2="1"><stop offset="0" stop-color="#1b3556"/><stop offset=".4" stop-color="#4f7299"/><stop offset=".65" stop-color="#355a82"/><stop offset="1" stop-color="#142a45"/></linearGradient>'
        . '<linearGradient id="' . $id . 'bh" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#1b3556"/><stop offset=".4" stop-color="#4f7299"/><stop offset=".65" stop-color="#355a82"/><stop offset="1" stop-color="#142a45"/></linearGradient>'
        . '<linearGradient id="' . $id . 'rv" x1="0" x2="1"><stop offset="0" stop-color="#8a0c10"/><stop offset=".4" stop-color="#e7454a"/><stop offset=".65" stop-color="#c3161b"/><stop offset="1" stop-color="#760a0d"/></linearGradient>'
        . '</defs>';
    $body = match ($type) {
        'tank'    => svg_tank($variant ?: 'galvaniz', (int)($opt['w'] ?? 4), (int)($opt['l'] ?? 3), (int)($opt['h'] ?? 2)),
        'booster' => svg_booster(max(1, min(4, (int)$variant)), $id),
        'pump'    => match ($variant) { 'vertical' => svg_pump_vertical($id), 'circulator' => svg_circulator($id), default => svg_pump_horizontal($id) },
        'sub'     => $variant === 'drain' ? svg_drain($id) : svg_deepwell($id),
        default   => svg_tank('galvaniz', 4, 3, 2),
    };
    return '<svg class="' . e($class) . '" viewBox="0 0 480 330" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">' . $defs . $body . '</svg>';
}

/** Isometric modular tank built from 1×1 m panels. */
function svg_tank(string $variant, int $W, int $L, int $H): string
{
    [$cl, $cr, $ct, $rib] = tank_materials()[$variant] ?? tank_materials()['galvaniz'];
    $c = 0.8660254;
    $h = 0.5;
    $vw = 480; $vh = 330; $pad = 34;
    $s = min(($vw - 2 * $pad) / (($W + $L) * $c), ($vh - 2 * $pad - 14) / (($W + $L) * $h + $H + 0.35));
    $ox = $vw / 2 + ($L - $W) * $c * $s / 2;
    $oy = ($vh - (($W + $L) * $h * $s + $H * $s)) / 2 + $H * $s - 4;
    $fmt = fn(float ...$v) => implode(',', array_map(fn($x) => round($x, 3), $v));
    $face = function (string $matrix, int $cols, int $rows, string $fill, bool $dimples = true) use ($rib, $variant): string {
        $g = '<g transform="matrix(' . $matrix . ')">';
        for ($i = 0; $i < $cols; $i++) {
            for ($j = 0; $j < $rows; $j++) {
                $g .= '<rect x="' . ($i + .015) . '" y="' . ($j + .015) . '" width=".97" height=".97" fill="' . $fill . '" stroke="' . $rib . '" stroke-width=".03"/>';
                if ($dimples && $variant !== 'sandvic') {
                    $g .= '<circle cx="' . ($i + .5) . '" cy="' . ($j + .5) . '" r=".27" fill="none" stroke="' . $rib . '" stroke-width=".028" opacity=".75"/>'
                        . '<circle cx="' . ($i + .47) . '" cy="' . ($j + .47) . '" r=".2" fill="#fff" opacity=".18"/>';
                } elseif ($dimples) {
                    $g .= '<rect x="' . ($i + .12) . '" y="' . ($j + .12) . '" width=".76" height=".76" fill="none" stroke="' . $rib . '" stroke-width=".02" opacity=".5"/>';
                }
            }
        }
        return $g . '</g>';
    };
    $shadow = '<ellipse cx="' . round($ox + ($W - $L) * $c * $s / 2, 1) . '" cy="' . round($oy + ($W + $L) * $h * $s + 10, 1) . '" rx="' . round(($W + $L) * $c * $s / 1.8, 1) . '" ry="12" fill="#13243d" opacity=".12"/>';
    // Base beams (skid) under the front edges
    $base = '<path d="M' . $fmt($ox - $L * $c * $s, $oy + $L * $h * $s) . ' L' . $fmt($ox + ($W - $L) * $c * $s, $oy + ($W + $L) * $h * $s) . ' L' . $fmt($ox + $W * $c * $s, $oy + $W * $h * $s)
        . ' l0,7 L' . $fmt($ox + ($W - $L) * $c * $s, $oy + ($W + $L) * $h * $s + 7) . ' L' . $fmt($ox - $L * $c * $s, $oy + $L * $h * $s + 7) . 'z" fill="#2d3d52"/>';
    $left = $face($fmt($c * $s, $h * $s, 0, $s, $ox - $L * $c * $s, $oy + $L * $h * $s - $H * $s), $W, $H, $cl);
    $right = $face($fmt($c * $s, -$h * $s, 0, $s, $ox + ($W - $L) * $c * $s, $oy + ($W + $L) * $h * $s - $H * $s), $L, $H, $cr);
    $top = $face($fmt($c * $s, $h * $s, -$c * $s, $h * $s, $ox, $oy - $H * $s), $W, $L, $ct, false);
    // Manhole + vent on the roof, level gauge on the right face
    $roof = '<g transform="matrix(' . $fmt($c * $s, $h * $s, -$c * $s, $h * $s, $ox, $oy - $H * $s) . ')">'
        . '<circle cx=".75" cy=".75" r=".32" fill="' . $cr . '" stroke="' . $rib . '" stroke-width=".04"/><circle cx=".75" cy=".75" r=".22" fill="none" stroke="' . $rib . '" stroke-width=".03"/>'
        . '<circle cx="' . ($W - .5) . '" cy="' . ($L - .5) . '" r=".12" fill="#2d3d52"/></g>';
    $gx = $L - 0.35;
    $gauge = '<g transform="matrix(' . $fmt($c * $s, -$h * $s, 0, $s, $ox + ($W - $L) * $c * $s, $oy + ($W + $L) * $h * $s - $H * $s) . ')">'
        . '<rect x="' . ($gx - .05) . '" y=".25" width=".1" height="' . ($H - .45) . '" rx=".05" fill="#f8fafc" stroke="#2d3d52" stroke-width=".025"/>'
        . '<rect x="' . ($gx - .035) . '" y="' . (.25 + ($H - .45) * .3) . '" width=".07" height="' . (($H - .45) * .7) . '" rx=".035" fill="#2b7fb8"/>'
        . '<path d="M.15 ' . ($H - .25) . 'h-.3" stroke="#2d3d52" stroke-width=".12"/></g>';
    // Inlet pipe entering the left face near the top
    $inlet = '<g transform="matrix(' . $fmt($c * $s, $h * $s, 0, $s, $ox - $L * $c * $s, $oy + $L * $h * $s - $H * $s) . ')"><circle cx=".5" cy=".45" r=".13" fill="#2d3d52"/><circle cx=".5" cy=".45" r=".07" fill="#6b7a8c"/></g>';
    return $shadow . $base . $left . $right . $top . $roof . $gauge . $inlet;
}

function svg_cyl_v(float $cx, float $top, float $bottom, float $r, string $fill, string $cap): string
{
    $ry = $r * .28;
    return '<path d="M' . ($cx - $r) . ' ' . $top . 'V' . $bottom . 'A' . $r . ' ' . $ry . ' 0 0 0 ' . ($cx + $r) . ' ' . $bottom . 'V' . $top . 'z" fill="' . $fill . '"/>'
        . '<ellipse cx="' . $cx . '" cy="' . $top . '" rx="' . $r . '" ry="' . $ry . '" fill="' . $cap . '"/>';
}

function svg_cyl_h(float $x1, float $x2, float $cy, float $r, string $fill, string $cap): string
{
    $rx = $r * .3;
    return '<path d="M' . $x1 . ' ' . ($cy - $r) . 'H' . $x2 . 'A' . $rx . ' ' . $r . ' 0 0 1 ' . $x2 . ' ' . ($cy + $r) . 'H' . $x1 . 'z" fill="' . $fill . '"/>'
        . '<ellipse cx="' . $x1 . '" cy="' . $cy . '" rx="' . $rx . '" ry="' . $r . '" fill="' . $cap . '"/>';
}

function svg_shadow(float $cx, float $cy, float $rx): string
{
    return '<ellipse cx="' . $cx . '" cy="' . $cy . '" rx="' . $rx . '" ry="' . ($rx * .12) . '" fill="#13243d" opacity=".13"/>';
}

/** Vertical multistage pump: motor on top, stage stack, inline flanges at the foot. */
function svg_vpump(float $cx, float $foot, float $scale, string $id): string
{
    $s = $scale;
    $o = '';
    $o .= '<rect x="' . ($cx - 34 * $s) . '" y="' . ($foot - 16 * $s) . '" width="' . (68 * $s) . '" height="' . (16 * $s) . '" rx="' . (3 * $s) . '" fill="#2d3d52"/>';
    $o .= svg_cyl_h($cx - 58 * $s, $cx - 30 * $s, $foot - 30 * $s, 11 * $s, 'url(#' . $id . 'ch)', '#9aa5b1');
    $o .= svg_cyl_h($cx + 30 * $s, $cx + 58 * $s, $foot - 30 * $s, 11 * $s, 'url(#' . $id . 'ch)', '#9aa5b1');
    $o .= svg_cyl_v($cx, $foot - 150 * $s, $foot - 16 * $s, 22 * $s, 'url(#' . $id . 'cv)', '#dfe4e9');
    for ($i = 1; $i <= 5; $i++) {
        $y = $foot - 16 * $s - $i * 22 * $s;
        $o .= '<path d="M' . ($cx - 22 * $s) . ' ' . $y . 'a' . (22 * $s) . ' ' . (6 * $s) . ' 0 0 0 ' . (44 * $s) . ' 0" fill="none" stroke="#7d8894" stroke-width="' . (1.2 * $s) . '"/>';
    }
    $o .= svg_cyl_v($cx, $foot - 160 * $s, $foot - 150 * $s, 30 * $s, '#6f7b88', '#aab4be');
    $o .= svg_cyl_v($cx, $foot - 245 * $s, $foot - 160 * $s, 27 * $s, 'url(#' . $id . 'bv)', '#5a7ea6');
    for ($i = 0; $i < 7; $i++) {
        $o .= '<path d="M' . ($cx - 27 * $s) . ' ' . ($foot - 232 * $s + $i * 10 * $s) . 'a' . (27 * $s) . ' ' . (7.5 * $s) . ' 0 0 0 ' . (54 * $s) . ' 0" fill="none" stroke="#12263f" stroke-width="' . (1 * $s) . '" opacity=".6"/>';
    }
    $o .= svg_cyl_v($cx, $foot - 258 * $s, $foot - 245 * $s, 18 * $s, '#12263f', '#3c5f86');
    $o .= '<rect x="' . ($cx + 10 * $s) . '" y="' . ($foot - 222 * $s) . '" width="' . (22 * $s) . '" height="' . (26 * $s) . '" rx="' . (2 * $s) . '" fill="#12263f"/>';
    return $o;
}

function svg_pump_vertical(string $id): string
{
    return svg_shadow(240, 300, 110) . svg_vpump(240, 298, 1.05, $id);
}

function svg_booster(int $count, string $id): string
{
    $o = svg_shadow(240, 304, 205);
    $gap = [1 => 0, 2 => 120, 3 => 108, 4 => 86][$count];
    $scale = [1 => .95, 2 => .85, 3 => .78, 4 => .68][$count];
    $x0 = 240 - ($count - 1) * $gap / 2 + ($count === 1 ? -40 : 0);
    // frame
    $o .= '<rect x="' . ($x0 - 70 * $scale) . '" y="286" width="' . (($count - 1) * $gap + 140 * $scale + ($count === 1 ? 110 : 0)) . '" height="14" rx="2" fill="#2d3d52"/>';
    // control panel post (behind), discharge manifold (back, higher) and suction manifold (back, low)
    $x1 = $x0 - 60 * $scale; $x2 = $x0 + ($count - 1) * $gap + 60 * $scale + ($count === 1 ? 70 : 0);
    $o .= '<rect x="236" y="84" width="8" height="202" fill="#3a4a5f"/>';
    $o .= svg_cyl_h($x1, $x2, 286 - 58 * $scale, 11 * $scale + 2, 'url(#' . $id . 'ch)', '#9aa5b1');
    $o .= svg_cyl_h($x1, $x2, 286 - 28 * $scale, 13 * $scale + 2, 'url(#' . $id . 'ch)', '#9aa5b1');
    for ($i = 0; $i < $count; $i++) {
        $o .= svg_vpump($x0 + $i * $gap, 286, $scale, $id);
    }
    // membrane tank at the discharge end + control panel
    $tx = $x2 + 30;
    $o .= svg_cyl_v($tx, 150, 250, 26, 'url(#' . $id . 'rv)', '#ef6a6e');
    $o .= '<rect x="' . ($tx - 6) . '' . '" y="250" width="12" height="36" fill="#2d3d52"/>';
    $o .= '<rect x="' . (240 - 44) . '" y="22" width="88" height="62" rx="4" fill="#e9edf1" stroke="#2d3d52" stroke-width="2"/>'
        . '<rect x="' . (240 - 32) . '" y="32" width="40" height="16" rx="2" fill="#12263f"/><text x="' . (240 - 12) . '" y="44" font-size="10" fill="#7fe3a8" text-anchor="middle" font-family="monospace">4.2</text>'
        . '<circle cx="' . (240 + 24) . '" cy="40" r="5" fill="#1b7f4b"/><circle cx="' . (240 + 24) . '" cy="58" r="5" fill="#e1161c"/><rect x="' . (240 - 32) . '" y="58" width="40" height="14" rx="2" fill="#c3cad2"/>'
        ;
    return $o;
}

function svg_pump_horizontal(string $id): string
{
    $o = svg_shadow(240, 294, 190);
    $o .= '<rect x="70" y="266" width="340" height="18" rx="3" fill="#2d3d52"/>';
    // motor
    $o .= svg_cyl_h(210, 380, 190, 58, 'url(#' . $id . 'bh)', '#355a82');
    for ($i = 0; $i < 9; $i++) {
        $o .= '<path d="M' . (224 + $i * 17) . ' 134v112" stroke="#12263f" stroke-width="2" opacity=".45"/>';
    }
    $o .= '<ellipse cx="385" cy="190" rx="20" ry="52" fill="#12263f"/>'
        . '<rect x="250" y="116" width="60" height="30" rx="3" fill="#12263f"/>'
        . '<rect x="232" y="246" width="28" height="22" fill="#2d3d52"/><rect x="340" y="246" width="28" height="22" fill="#2d3d52"/>';
    // coupling / bearing bracket
    $o .= svg_cyl_h(170, 214, 190, 30, 'url(#' . $id . 'ch)', '#aab4be');
    // volute casing
    $o .= '<circle cx="130" cy="186" r="66" fill="url(#' . $id . 'cv)"/><circle cx="130" cy="186" r="66" fill="none" stroke="#6f7b88" stroke-width="3"/>'
        . '<circle cx="118" cy="186" r="30" fill="#b3bcc5" stroke="#6f7b88" stroke-width="2"/><circle cx="118" cy="186" r="12" fill="#6f7b88"/>';
    // discharge (up) and suction (front)
    $o .= svg_cyl_v(146, 88, 132, 20, 'url(#' . $id . 'cv)', '#dfe4e9') . svg_cyl_v(146, 82, 90, 28, '#6f7b88', '#aab4be')
        . '<rect x="104" y="248" width="52" height="20" fill="#2d3d52"/>';
    return $o;
}

function svg_circulator(string $id): string
{
    $o = svg_shadow(240, 290, 150);
    $o .= svg_cyl_h(80, 400, 232, 22, 'url(#' . $id . 'ch)', '#9aa5b1');
    $o .= svg_cyl_h(96, 120, 232, 34, '#6f7b88', '#aab4be') . svg_cyl_h(360, 384, 232, 34, '#6f7b88', '#aab4be');
    $o .= svg_cyl_v(240, 196, 262, 54, 'url(#' . $id . 'cv)', '#dfe4e9');
    $o .= svg_cyl_v(240, 100, 196, 48, 'url(#' . $id . 'bv)', '#4f7299');
    $o .= '<rect x="196" y="56" width="88" height="56" rx="10" fill="#12263f"/><rect x="210" y="68" width="42" height="20" rx="3" fill="#0b1626"/>'
        . '<text x="231" y="83" font-size="12" fill="#7fe3a8" text-anchor="middle" font-family="monospace">12W</text><circle cx="268" cy="78" r="6" fill="#e1161c"/>';
    return $o;
}

function svg_deepwell(string $id): string
{
    $o = svg_shadow(240, 312, 60);
    $o .= '<path d="M240 10 C 250 60, 226 80, 240 110" stroke="#12263f" stroke-width="5" fill="none"/>';
    $o .= svg_cyl_v(240, 30, 46, 26, '#6f7b88', '#aab4be');
    $o .= svg_cyl_v(240, 46, 170, 22, 'url(#' . $id . 'cv)', '#dfe4e9');
    for ($i = 0; $i < 6; $i++) {
        $o .= '<path d="M218 ' . (62 + $i * 18) . 'a22 6 0 0 0 44 0" fill="none" stroke="#7d8894" stroke-width="1.4"/>';
    }
    $o .= '<rect x="220" y="172" width="40" height="18" fill="#12263f"/>';
    for ($i = 0; $i < 5; $i++) {
        $o .= '<rect x="' . (221 + $i * 8) . '" y="174" width="4" height="14" fill="#9aa5b1"/>';
    }
    $o .= svg_cyl_v(240, 190, 300, 23, 'url(#' . $id . 'bv)', '#4f7299');
    $o .= '<circle cx="176" cy="120" r="6" fill="#2b7fb8" opacity=".35"/><circle cx="306" cy="200" r="9" fill="#2b7fb8" opacity=".3"/><circle cx="296" cy="90" r="5" fill="#2b7fb8" opacity=".35"/>';
    return $o;
}

function svg_drain(string $id): string
{
    $o = svg_shadow(240, 300, 110);
    $o .= '<path d="M240 20 V 60" stroke="#12263f" stroke-width="6"/>' . svg_cyl_v(240, 60, 82, 22, '#6f7b88', '#aab4be');
    $o .= '<path d="M300 110 C 350 100, 360 60, 400 40" stroke="#12263f" stroke-width="4" fill="none"/><path d="M336 170 l40 26" stroke="#12263f" stroke-width="3"/><rect x="368" y="186" width="34" height="22" rx="10" fill="#e1161c"/>';
    $o .= svg_cyl_v(240, 90, 210, 64, 'url(#' . $id . 'cv)', '#dfe4e9');
    $o .= '<rect x="182" y="96" width="30" height="16" rx="3" fill="#12263f"/>';
    $o .= svg_cyl_v(240, 210, 262, 72, '#6f7b88', '#aab4be');
    for ($i = 0; $i < 9; $i++) {
        $o .= '<rect x="' . (180 + $i * 14) . '" y="226" width="6" height="26" rx="3" fill="#2d3d52"/>';
    }
    $o .= '<path d="M172 268h136" stroke="#2d3d52" stroke-width="10" stroke-linecap="round"/>';
    return $o;
}
