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
    // [base, shade, highlight, seam]
    return [
        'galvaniz'  => ['#c3cbd4', '#8e9aa8', '#eef1f4', '#a4afbb'],
        'paslanmaz' => ['#dfe4e9', '#a5afba', '#ffffff', '#bcc5ce'],
        'grp'       => ['#7fb0d1', '#4d7fa3', '#c1dcee', '#6b9cc0'],
        'sandvic'   => ['#ecedea', '#b6b8b2', '#ffffff', '#d2d4ce'],
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
        . '<filter id="' . $id . 'blur" x="-20%" y="-50%" width="140%" height="200%"><feGaussianBlur stdDeviation="7"/></filter>'
        . '<linearGradient id="' . $id . 'cv" x1="0" x2="1"><stop offset="0" stop-color="#9aa6b2"/><stop offset=".22" stop-color="#e9edf1"/><stop offset=".42" stop-color="#ffffff"/><stop offset=".62" stop-color="#cfd6dd"/><stop offset="1" stop-color="#8793a0"/></linearGradient>'
        . '<linearGradient id="' . $id . 'ch" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#9aa6b2"/><stop offset=".22" stop-color="#e9edf1"/><stop offset=".42" stop-color="#ffffff"/><stop offset=".62" stop-color="#cfd6dd"/><stop offset="1" stop-color="#8793a0"/></linearGradient>'
        . '<linearGradient id="' . $id . 'bv" x1="0" x2="1"><stop offset="0" stop-color="#23497a"/><stop offset=".3" stop-color="#4f86c2"/><stop offset=".45" stop-color="#8db6e0"/><stop offset=".65" stop-color="#3d72ad"/><stop offset="1" stop-color="#1f3f6a"/></linearGradient>'
        . '<linearGradient id="' . $id . 'bh" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#23497a"/><stop offset=".3" stop-color="#4f86c2"/><stop offset=".45" stop-color="#8db6e0"/><stop offset=".65" stop-color="#3d72ad"/><stop offset="1" stop-color="#1f3f6a"/></linearGradient>'
        . '<linearGradient id="' . $id . 'rv" x1="0" x2="1"><stop offset="0" stop-color="#9c1014"/><stop offset=".35" stop-color="#ef4a4f"/><stop offset=".5" stop-color="#ff8a8d"/><stop offset=".7" stop-color="#d8252b"/><stop offset="1" stop-color="#8a0d11"/></linearGradient>'
        . '</defs>';
    $GLOBALS['__svg_id'] = $id;
    $body = match ($type) {
        'tank'    => svg_tank($variant ?: 'galvaniz', (int)($opt['w'] ?? 4), (int)($opt['l'] ?? 3), (int)($opt['h'] ?? 2), $id),
        'booster' => svg_booster(max(1, min(4, (int)$variant)), $id),
        'pump'    => match ($variant) { 'vertical' => svg_pump_vertical($id), 'circulator' => svg_circulator($id), default => svg_pump_horizontal($id) },
        'sub'     => $variant === 'drain' ? svg_drain($id) : svg_deepwell($id),
        default   => svg_tank('galvaniz', 4, 3, 2, $id),
    };
    return '<svg class="' . e($class) . '" viewBox="0 0 480 330" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">' . $defs . $body . '</svg>';
}

/** Isometric modular tank built from 1×1 m panels (mirrored in assets/js/site.js → tankSVG). */
function svg_tank(string $variant, int $W, int $L, int $H, string $id = 't'): string
{
    [$base, $dark, $light, $rib] = tank_materials()[$variant] ?? tank_materials()['galvaniz'];
    $c = 0.8660254; $h = 0.5;
    $vw = 480; $vh = 330; $pad = 34;
    $s = min(($vw - 2 * $pad) / (($W + $L) * $c), ($vh - 2 * $pad - 14) / (($W + $L) * $h + $H + 0.35));
    $ox = $vw / 2 + ($L - $W) * $c * $s / 2;
    $oy = ($vh - (($W + $L) * $h * $s + $H * $s)) / 2 + $H * $s - 4;
    $f = fn(float ...$v) => implode(',', array_map(fn($x) => round($x, 3), $v));
    $flat = $variant === 'sandvic' || $variant === 'grp_flat';
    $defs = '<defs>'
        . '<linearGradient id="' . $id . 'pn" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="' . $light . '"/><stop offset="1" stop-color="' . $base . '"/></linearGradient>'
        . '<radialGradient id="' . $id . 'dm" cx=".42" cy=".38" r=".6"><stop offset="0" stop-color="#fff" stop-opacity=".85"/><stop offset=".45" stop-color="#fff" stop-opacity=".2"/><stop offset=".8" stop-color="' . $dark . '" stop-opacity=".25"/><stop offset="1" stop-color="' . $dark . '" stop-opacity=".55"/></radialGradient>'
        . '</defs>';
    $face = function (string $m, int $cols, int $rows, string $tint, float $tintOpacity, bool $dimples) use ($id, $rib, $flat): string {
        $g = '<g transform="matrix(' . $m . ')">';
        $g .= '<rect x="0" y="0" width="' . $cols . '" height="' . $rows . '" fill="' . $rib . '"/>';
        for ($i = 0; $i < $cols; $i++) {
            for ($j = 0; $j < $rows; $j++) {
                $g .= '<rect x="' . ($i + .025) . '" y="' . ($j + .025) . '" width=".95" height=".95" rx=".07" fill="url(#' . $id . 'pn)"/>';
                if ($dimples && !$flat) {
                    $g .= '<circle cx="' . ($i + .5) . '" cy="' . ($j + .5) . '" r=".31" fill="url(#' . $id . 'dm)"/>';
                } elseif ($dimples) {
                    $g .= '<rect x="' . ($i + .14) . '" y="' . ($j + .14) . '" width=".72" height=".72" rx=".05" fill="#fff" opacity=".22"/>';
                }
            }
        }
        if ($tintOpacity > 0) {
            $g .= '<rect x="0" y="0" width="' . $cols . '" height="' . $rows . '" fill="' . $tint . '" opacity="' . $tintOpacity . '"/>';
        }
        return $g . '</g>';
    };
    $lm = $f($c * $s, $h * $s, 0, $s, $ox - $L * $c * $s, $oy + $L * $h * $s - $H * $s);
    $rm = $f($c * $s, -$h * $s, 0, $s, $ox + ($W - $L) * $c * $s, $oy + ($W + $L) * $h * $s - $H * $s);
    $tm = $f($c * $s, $h * $s, -$c * $s, $h * $s, $ox, $oy - $H * $s);
    $o = $defs;
    $o .= '<ellipse cx="' . round($ox + ($W - $L) * $c * $s / 2, 1) . '" cy="' . round($oy + ($W + $L) * $h * $s + 12, 1) . '" rx="' . round(($W + $L) * $c * $s / 1.9, 1) . '" ry="14" fill="#1f3a60" opacity=".22" filter="url(#' . $id . 'blur)"/>';
    $o .= '<path d="M' . $f($ox - $L * $c * $s, $oy + $L * $h * $s) . ' L' . $f($ox + ($W - $L) * $c * $s, $oy + ($W + $L) * $h * $s) . ' L' . $f($ox + $W * $c * $s, $oy + $W * $h * $s)
        . ' l0,7 L' . $f($ox + ($W - $L) * $c * $s, $oy + ($W + $L) * $h * $s + 7) . ' L' . $f($ox - $L * $c * $s, $oy + $L * $h * $s + 7) . 'z" fill="#3b4d66"/>';
    $o .= $face($lm, $W, $H, '#ffffff', 0.0, true);
    $o .= $face($rm, $L, $H, '#1f3a60', 0.16, true);
    $o .= $face($tm, $W, $L, '#ffffff', 0.28, false);
    // roof: manhole + vent
    $o .= '<g transform="matrix(' . $tm . ')"><circle cx=".75" cy=".75" r=".33" fill="' . $dark . '"/><circle cx=".73" cy=".73" r=".27" fill="url(#' . $id . 'pn)"/><circle cx=".73" cy=".73" r=".27" fill="url(#' . $id . 'dm)" opacity=".7"/>'
        . '<circle cx="' . ($W - .5) . '" cy="' . ($L - .5) . '" r=".13" fill="#3b4d66"/><circle cx="' . ($W - .52) . '" cy="' . ($L - .52) . '" r=".07" fill="#8b9bb0"/></g>';
    // level gauge on the right face
    $gx = $L - 0.35; $gh = $H - 0.45;
    $o .= '<g transform="matrix(' . $rm . ')"><rect x="' . ($gx - .06) . '" y=".25" width=".12" height="' . $gh . '" rx=".06" fill="#ffffff" opacity=".9"/>'
        . '<rect x="' . ($gx - .035) . '" y="' . (.25 + $gh * .3) . '" width=".07" height="' . ($gh * .7) . '" rx=".035" fill="#3b8fd1"/></g>';
    // inlet pipe on the left face
    $o .= '<g transform="matrix(' . $lm . ')"><circle cx=".5" cy=".45" r=".15" fill="#3b4d66"/><circle cx=".5" cy=".45" r=".09" fill="#9aa9bb"/><circle cx=".48" cy=".43" r=".04" fill="#e9eef3"/></g>';
    return $o;
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
    $id = $GLOBALS['__svg_id'] ?? '';
    return '<ellipse cx="' . $cx . '" cy="' . $cy . '" rx="' . $rx . '" ry="' . ($rx * .1) . '" fill="#1f3a60" opacity=".22" filter="url(#' . $id . 'blur)"/>';
}

/** Vertical multistage pump: motor on top, stage stack, inline flanges at the foot. */
function svg_vpump(float $cx, float $foot, float $scale, string $id): string
{
    $s = $scale;
    $o = '';
    $o .= '<rect x="' . ($cx - 34 * $s) . '" y="' . ($foot - 16 * $s) . '" width="' . (68 * $s) . '" height="' . (16 * $s) . '" rx="' . (3 * $s) . '" fill="#3b4d66"/>';
    $o .= svg_cyl_h($cx - 58 * $s, $cx - 30 * $s, $foot - 30 * $s, 11 * $s, 'url(#' . $id . 'ch)', '#9aa5b1');
    $o .= svg_cyl_h($cx + 30 * $s, $cx + 58 * $s, $foot - 30 * $s, 11 * $s, 'url(#' . $id . 'ch)', '#9aa5b1');
    $o .= svg_cyl_v($cx, $foot - 150 * $s, $foot - 16 * $s, 22 * $s, 'url(#' . $id . 'cv)', '#dfe4e9');
    for ($i = 1; $i <= 5; $i++) {
        $y = $foot - 16 * $s - $i * 22 * $s;
        $o .= '<path d="M' . ($cx - 22 * $s) . ' ' . $y . 'a' . (22 * $s) . ' ' . (6 * $s) . ' 0 0 0 ' . (44 * $s) . ' 0" fill="none" stroke="#7d8894" stroke-width="' . (1.2 * $s) . '"/>';
    }
    $o .= svg_cyl_v($cx, $foot - 160 * $s, $foot - 150 * $s, 30 * $s, '#8492a3', '#c3ccd5');
    $o .= svg_cyl_v($cx, $foot - 245 * $s, $foot - 160 * $s, 27 * $s, 'url(#' . $id . 'bv)', '#5a7ea6');
    for ($i = 0; $i < 7; $i++) {
        $o .= '<path d="M' . ($cx - 27 * $s) . ' ' . ($foot - 232 * $s + $i * 10 * $s) . 'a' . (27 * $s) . ' ' . (7.5 * $s) . ' 0 0 0 ' . (54 * $s) . ' 0" fill="none" stroke="#27405f" stroke-width="' . (1 * $s) . '" opacity=".6"/>';
    }
    $o .= svg_cyl_v($cx, $foot - 258 * $s, $foot - 245 * $s, 18 * $s, '#27405f', '#3c5f86');
    $o .= '<rect x="' . ($cx + 10 * $s) . '" y="' . ($foot - 222 * $s) . '" width="' . (22 * $s) . '" height="' . (26 * $s) . '" rx="' . (2 * $s) . '" fill="#27405f"/>';
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
    $o .= '<rect x="' . ($x0 - 70 * $scale) . '" y="286" width="' . (($count - 1) * $gap + 140 * $scale + ($count === 1 ? 110 : 0)) . '" height="14" rx="2" fill="#3b4d66"/>';
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
    $o .= '<rect x="' . ($tx - 6) . '' . '" y="250" width="12" height="36" fill="#3b4d66"/>';
    $o .= '<rect x="' . (240 - 44) . '" y="22" width="88" height="62" rx="4" fill="#e9edf1" stroke="#3b4d66" stroke-width="2"/>'
        . '<rect x="' . (240 - 32) . '" y="32" width="40" height="16" rx="2" fill="#27405f"/><text x="' . (240 - 12) . '" y="44" font-size="10" fill="#7fe3a8" text-anchor="middle" font-family="monospace">4.2</text>'
        . '<circle cx="' . (240 + 24) . '" cy="40" r="5" fill="#1b7f4b"/><circle cx="' . (240 + 24) . '" cy="58" r="5" fill="#e1161c"/><rect x="' . (240 - 32) . '" y="58" width="40" height="14" rx="2" fill="#c3cad2"/>'
        ;
    return $o;
}

function svg_pump_horizontal(string $id): string
{
    $o = svg_shadow(240, 294, 190);
    $o .= '<rect x="70" y="266" width="340" height="18" rx="3" fill="#3b4d66"/>';
    // motor
    $o .= svg_cyl_h(210, 380, 190, 58, 'url(#' . $id . 'bh)', '#355a82');
    for ($i = 0; $i < 9; $i++) {
        $o .= '<path d="M' . (224 + $i * 17) . ' 134v112" stroke="#27405f" stroke-width="2" opacity=".45"/>';
    }
    $o .= '<ellipse cx="385" cy="190" rx="20" ry="52" fill="#27405f"/>'
        . '<rect x="250" y="116" width="60" height="30" rx="3" fill="#27405f"/>'
        . '<rect x="232" y="246" width="28" height="22" fill="#3b4d66"/><rect x="340" y="246" width="28" height="22" fill="#3b4d66"/>';
    // coupling / bearing bracket
    $o .= svg_cyl_h(170, 214, 190, 30, 'url(#' . $id . 'ch)', '#c3ccd5');
    // volute casing
    $o .= '<circle cx="130" cy="186" r="66" fill="url(#' . $id . 'cv)"/><circle cx="130" cy="186" r="66" fill="none" stroke="#8492a3" stroke-width="3"/>'
        . '<circle cx="118" cy="186" r="30" fill="#b3bcc5" stroke="#8492a3" stroke-width="2"/><circle cx="118" cy="186" r="12" fill="#8492a3"/>';
    // discharge (up) and suction (front)
    $o .= svg_cyl_v(146, 88, 132, 20, 'url(#' . $id . 'cv)', '#dfe4e9') . svg_cyl_v(146, 82, 90, 28, '#8492a3', '#c3ccd5')
        . '<rect x="104" y="248" width="52" height="20" fill="#3b4d66"/>';
    return $o;
}

function svg_circulator(string $id): string
{
    $o = svg_shadow(240, 290, 150);
    $o .= svg_cyl_h(80, 400, 232, 22, 'url(#' . $id . 'ch)', '#9aa5b1');
    $o .= svg_cyl_h(96, 120, 232, 34, '#8492a3', '#c3ccd5') . svg_cyl_h(360, 384, 232, 34, '#8492a3', '#c3ccd5');
    $o .= svg_cyl_v(240, 196, 262, 54, 'url(#' . $id . 'cv)', '#dfe4e9');
    $o .= svg_cyl_v(240, 100, 196, 48, 'url(#' . $id . 'bv)', '#4f7299');
    $o .= '<rect x="196" y="56" width="88" height="56" rx="10" fill="#27405f"/><rect x="210" y="68" width="42" height="20" rx="3" fill="#0b1626"/>'
        . '<text x="231" y="83" font-size="12" fill="#7fe3a8" text-anchor="middle" font-family="monospace">12W</text><circle cx="268" cy="78" r="6" fill="#e1161c"/>';
    return $o;
}

function svg_deepwell(string $id): string
{
    $o = svg_shadow(240, 312, 60);
    $o .= '<path d="M240 10 C 250 60, 226 80, 240 110" stroke="#27405f" stroke-width="5" fill="none"/>';
    $o .= svg_cyl_v(240, 30, 46, 26, '#8492a3', '#c3ccd5');
    $o .= svg_cyl_v(240, 46, 170, 22, 'url(#' . $id . 'cv)', '#dfe4e9');
    for ($i = 0; $i < 6; $i++) {
        $o .= '<path d="M218 ' . (62 + $i * 18) . 'a22 6 0 0 0 44 0" fill="none" stroke="#7d8894" stroke-width="1.4"/>';
    }
    $o .= '<rect x="220" y="172" width="40" height="18" fill="#27405f"/>';
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
    $o .= '<path d="M240 20 V 60" stroke="#27405f" stroke-width="6"/>' . svg_cyl_v(240, 60, 82, 22, '#8492a3', '#c3ccd5');
    $o .= '<path d="M300 110 C 350 100, 360 60, 400 40" stroke="#27405f" stroke-width="4" fill="none"/><path d="M336 170 l40 26" stroke="#27405f" stroke-width="3"/><rect x="368" y="186" width="34" height="22" rx="10" fill="#e1161c"/>';
    $o .= svg_cyl_v(240, 90, 210, 64, 'url(#' . $id . 'cv)', '#dfe4e9');
    $o .= '<rect x="182" y="96" width="30" height="16" rx="3" fill="#27405f"/>';
    $o .= svg_cyl_v(240, 210, 262, 72, '#8492a3', '#c3ccd5');
    for ($i = 0; $i < 9; $i++) {
        $o .= '<rect x="' . (180 + $i * 14) . '" y="226" width="6" height="26" rx="3" fill="#3b4d66"/>';
    }
    $o .= '<path d="M172 268h136" stroke="#3b4d66" stroke-width="10" stroke-linecap="round"/>';
    return $o;
}
