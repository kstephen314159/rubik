<?php
function rotu($u,$v,$a) { return $u*cos($a) - $v*sin($a); }
function rotv($u,$v,$a) { return $u*sin($a) + $v*cos($a); }
function rotxy($x,$y,$z,$ex,$ey,$ez,$ax,$ay) {
 $ry = rotu($ey,$ez,$ax);
 $rz = rotv($ey,$ez,$ax);
 $rx = rotu($ex,$rz,$ay);
 $rz = rotv($ex,$rz,$ay);
 return $x*$rx + $y*$ry + $z*$rz;
}
function projxy($x,$y,$z,$ex,$ey,$ax,$ay,$m) {
 $xx = $m == 2 ? $z : $x;
 $yy = $m == 0 ? $z : $y;
 $zz = $m == 0 ? -$y : ($m == 2 ? $x : $z);
 return rotxy($xx,$yy,$zz,$ex,$ey,0,$ax,$ay) / (1.0 - 0.3 * rotxy($xx,$yy,$zz,0,0,-1,$ax,$ay));
}
function outcoordx($x,$m) { return $m > 0 ? $x * 0.58 + 0.52 : $x * -0.58 + 0.48; }
function outcoordy($y,$m) { return $m > 0 ? $y * 0.58 + 0.46 : $y * -0.58 + 0.54; }

function tile($im,$r, $x0,$y0, $x1,$y1, $x2,$y2, $x3,$y3, $z, $ax, $ay, $c, $m, $mx, $my) {
 $xx0 = outcoordx(projxy($x0,$y0,$z,1,0,$ax,$ay,$m), $mx) * $r;
 $yy0 = outcoordy(projxy($x0,$y0,$z,0,1,$ax,$ay,$m), $my) * $r;
 $xx1 = outcoordx(projxy($x1,$y1,$z,1,0,$ax,$ay,$m), $mx) * $r;
 $yy1 = outcoordy(projxy($x1,$y1,$z,0,1,$ax,$ay,$m), $my) * $r;
 $xx2 = outcoordx(projxy($x2,$y2,$z,1,0,$ax,$ay,$m), $mx) * $r;
 $yy2 = outcoordy(projxy($x2,$y2,$z,0,1,$ax,$ay,$m), $my) * $r;
 $xx3 = outcoordx(projxy($x3,$y3,$z,1,0,$ax,$ay,$m), $mx) * $r;
 $yy3 = outcoordy(projxy($x3,$y3,$z,0,1,$ax,$ay,$m), $my) * $r;
 imagefilledpolygon($im, array($xx0,$yy0, $xx1,$yy1, $xx2,$yy2, $xx3,$yy3), 4, $c);
}
function square($im,$r, $x,$y, $w, $b, $z, $m, $c, $mx, $my) {
 tile($im,$r, $x+$b-0.5,$y+$b-0.5, $x+$w-$b-0.5,$y+$b-0.5, $x+$w-$b-0.5,$y+$w-$b-0.5, $x+$b-0.5,$y+$w-$b-0.5, $z, -0.5, 0.6, $c, $m, $mx, $my);
}

$side = isset($_GET['n']) ? 1 * $_GET['n'] : 3;
$size = isset($_GET['size']) ? $_GET['size'] : 100;
$b = isset($_GET['b']) ? $_GET['b'] : 25;
$d = isset($_GET['d']) ? $_GET['d'] : 5;
$dim = min(5 * $size, 500);
$fl = isset($_GET['fl']) ? $_GET['fl'] : $_GET['stickers'];
$bg = isset($_GET['bg']) ? hexdec($_GET['bg']) : 0xFFFFFF;
$c = array( 'r'=>0xD00000, 'o'=>0xEE8800, 'b'=>0x2040D0, 'g'=>0x11AA00, 'w'=>0xFFFFFF, 'y'=>0xFFFF00,
 'l'=>0xDDDDDD, 'd'=>0x555555, 'x'=>0x999999, 'k'=>0x111111, 'c'=>0x0099FF, 'p'=>0xFF99CC, 'm'=>0xFF0099);
$mx = isset($_GET['m']) ? ($_GET['m'] == 'x' || $_GET['m'] == 'xy' ? -1 : 1) : 1;
$my = isset($_GET['m']) ? ($_GET['m'] == 'y' || $_GET['m'] == 'xy' ? -1 : 1) : 1;
 
$im = imagecreatetruecolor($dim,$dim);
imagefilledrectangle($im, 0,0, $dim-1,$dim-1, $bg);

square($im,$dim, 0,0, 1, 0, -0.5, 0, 0x010101, $mx, $my); // U
square($im,$dim, 0,0, 1, 0, -0.5, 1, 0x090909, $mx, $my); // F
square($im,$dim, 0,0, 1, 0,  0.5, 2, 0x050505, $mx, $my); // R
for ($m = 0; $m < 3; $m++)
 for ($i = 0; $i < $side; $i++)
  for ($j = 0; $j < $side; $j++)
   square($im,$dim, $j/(1.0*$side),$i/(1.0*$side), 1.0/$side, $b/1000.0, $m < 2 ? -0.5-$d/1000.0 : 0.5+$d/1000.0, $m, $c[substr($fl,$m*$side*$side+$i*$side+$j,1)], $mx, $my);

$im2 = imagecreatetruecolor($size,$size);
imagecopyresampled($im2, $im, 0,0, 0,0, $size,$size, $dim,$dim);
imagedestroy($im);

if (!isset($_GET['f']) || $_GET['f'] == 'gif'):
 header('Content-type: image/gif');
 imagegif($im2);
elseif ($_GET['f'] == 'png'):
 header("Content-type: image/png");
 imagepng($im2);
elseif ($_GET['f'] == 'jpg' || $_GET['f'] == 'jpeg'):
 header('Content-type: image/jpeg');
 imagejpeg($im2);
endif;
imagedestroy($im2);
?>
