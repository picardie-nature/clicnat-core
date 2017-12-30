<?php
namespace Picnat\Clicnat;

class bobs_maps_image {
	public $title;
	public $mapname;
	public $timestamp;

	public function __construct($mapname) {
		$this->mapname = $mapname;
		$path = $this->get_path();

		if (!file_exists($path)) {
			throw new \Exception('no map');
		}
		$this->title = file_get_contents($path.'/title');

		$this->timestamp = file_get_contents($path.'/timestamp');
	}

	private function get_path() {
		if (empty($this->mapname)) {
			throw new \Exception('$mapname is empty');
		}
		return MAPSTORE.'/'.$this->mapname;
	}

	public static function archive($mapname, $title, $im) {
		$path = MAPSTORE.'/'.$mapname;

		if (!file_exists($path)) {
			mkdir($path);
		}

		$f = fopen($path.'/title', 'w');
		fwrite($f, $title);
		fclose($f);

		$f = fopen($path.'/timestamp','w');
		fwrite($f, mktime());
		fclose($f);

		bobmap_image($im, $path.'/image.png');
  }

	public function ajoute_fond() {
		$path = $this->get_path();
		$im = imagecreatefrompng($path.'/image.png');
		$w = imagesx($im);
		$h = imagesy($im);

		$bg_path = sprintf(MAPSTORE_BACKGROUNDS.'/fond_%dx%d.png', $w, $h);
		if (!file_exists($bg_path)) {
			throw new \Exception('No background found');
		}
		$added = array();
		$tr_fond = -1;
		$im_fond = imagecreatefrompng($bg_path);

		for ($x=0; $x<$w; $x++) {
			for ($y=0; $y<$h; $y++) {
				$c_fond = imagecolorat($im_fond, $x, $y);

				if ($c_fond == $tr_fond) {
					continue;
				}

				if (!isset($added[$c_fond])) {
					$c = imagecolorsforindex($im_fond, $c_fond);
					if ($c['alpha'] > 0) {
						$tr_fond = $c_fond;
						continue;
					}
					$nc = imagecolorallocate($im, $c['red'], $c['green'], $c['blue']);
					$added[$c_fond] = $nc;
				} else {
					$nc = $added[$c_fond];
				}

				imagesetpixel($im, $x, $y, $nc);
			}
		}

		$cfont = imagecolorallocate($im, 255, 255, 255);
		imagefttext($im, 20, 0, 100, 40, $cfont, FONT_ARIAL, $this->title);
		imagefttext($im, 10, 0, 645, 58, $cfont, FONT_ARIAL, strftime('mise à jour le %d-%m-%Y', $this->timestamp));
		imagepng($im, $path.'/image_bg.png');
	}


	public function get_image_fond() {
		if (!file_exists($this->get_path().'/image_bg.png')) {
			throw new \Exception('image not found');
		}

		header("Content-Type: image/png");
		echo file_get_contents($this->get_path().'/image_bg.png');
	}
}
