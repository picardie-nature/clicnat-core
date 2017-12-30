<?php
namespace Picnat\Clicnat;

class clicnat_couleur {
	public static function rvb2tsv($r,$v,$b) {
		$max = max($r,$v,$b);
		$min = min($r,$v,$b);

		if ($max == $min) return 0;

		switch ($max) {
			case $r:
				$t = (60*($v-$b)/($max-$min)+360)%360;
				break;
			case $v:
				$t = 60*($b-$r)/($max-$min)+120;
				break;
			case $b:
				$t = 60*($r-$v)/($max-$min)+240;
				break;
		}
		$s = ($max==0) ? 0 : 1-$min/$max;
		$v = $max;
		return array($t,$s,$v);
	}

	public static function tsv2rvb($t,$s,$v) {
		$ti = ($t/60)%6;
		$f = $t/60-$ti;
		$l = $v*(1-$s);
		$m = $v*(1-$f*$s);
		$n = $v*(1-(1-$f)*$s);
		switch ($ti) {
			case 0: return array($v,$n,$l);
			case 1: return array($m,$v,$l);
			case 2: return array($l,$v,$n);
			case 3: return array($l,$m,$v);
			case 4: return array($n,$l,$v);
			case 5: return array($v,$l,$m);
		}
		throw new Exception('($t/60)%60 < 0 ou > 5');
	}

	public static function cmjn2rvb_1($c,$m,$j,$n) {
		$C = $c + $n;
		$M = $m + $n;
		$J = $j + $n;
		return array(1-$C, 1-$M, 1-$J);
	}

	public static function cmjn2rvb_2($c,$m,$j,$n) {
		$C = $c*(1-$n) + $n;
		$M = $m*(1-$n) + $n;
		$J = $j*(1-$n) + $n;
		return array(1-$C,1-$M,1-$J);
	}

	public static function a2rgb($t) {
		return sprintf("rgb(%03d,%03d,%03d);",$t[0],$t[1],$t[2]);
	}
}
?>
