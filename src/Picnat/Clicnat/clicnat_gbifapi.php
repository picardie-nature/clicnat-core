<?php
namespace Picnat\Clicnat;

/**
 * GBIF
 *
 * Usage : $gbif = new gbifapi();
 * $resultat = $gbif->species_search(array("q" => "Bombina variegata (Linnaeus 1758)", "rank"=>"SPECIES"));
 *
 * $resultat->preview();
 * print_r($resultat->nubKeys());
 */
class clicnat_gbifapi {
	protected $url;
	protected $fh_logs;

	const species_search_keys = "q,datasetKey,rank,highertaxonKey,status,isExtinct,habitat,threat,nameType,nomenclaturalStatus,hl,facet,facetMincount,facetMultiselect";

	public function __construct($url="http://api.gbif.org/v1") {
		$this->url = $url;
	}

	private function make_get_url($path, $keys, $params) {
		$url = "{$this->url}$path?";
		$n = 0;
		foreach (explode(',',$keys) as $k) {
			if (isset($params[$k])) {
				$url .= ($n>0?'&':'').urlencode($k)."=".urlencode($params[$k]);
			}
			$n++;
		}
		return $url;
	}

	protected function log($msg) {
		if (!isset($this->fh_logs))
			$this->fh_logs = fopen("php://stderr","w");
		fwrite($this->fh_logs, "$msg\n");
	}

	private function get($url) {
		$this->log($url);
		return file_get_contents($url);
	}

	public function species_search($params) {
		return new clicnat_gbifapi_nameusagepage($this->get($this->make_get_url("/species/search", self::species_search_keys, $params)));
	}

	public function species_match($params) {
		$params['kingdom'] = 'Animalia';
		return new clicnat_gbifapi_nameusagepage($this->get($this->make_get_url("/species/match", "name,kingdom", $params)));
	}
}
