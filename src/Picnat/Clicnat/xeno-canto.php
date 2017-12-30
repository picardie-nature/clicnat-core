<?php
namespace  Picnat\Clicnat;

class clicnat_oiseau_xcanto extends bobs_espece {

	const url = 'http://www.xeno-canto.org/api/2/recordings?query=';

	public function req() {
		$qs = [];
		$qs[] = "{$this->nom_s} cnt:france";

		$esp_mnhn = $this->get_inpn_ref();
		if ($esp_mnhn)
			$qs[] = "{$esp_mnhn->lb_nom} cnt:france";

		foreach ($qs as $query) {
			$url = sprintf("%s%s",self::url,urlencode($query));
			$res = file_get_contents($url);
			$ret = json_decode($res,true);
			if ($ret['numRecordings'] > 0)
				break;
		}
		return $ret;
	}

	public function enregistrements() {
		return $this->req();
	}
}
