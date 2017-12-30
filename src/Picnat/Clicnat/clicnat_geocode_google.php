<?php
namespace Picnat\Clicnat;

class clicnat_geocode_google implements clicnat_geocode {
	public static function adresse($txt) {
		$txt_url = urlencode($txt);
		$resultat  = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address=$txt_url&key=".GOOGLE_GEOCODE_API.'&language=fr&region=fr');
		if (!$resultat)
			return false;
		$r = json_decode($resultat, true);
		if ($r['status'] == 'OK') {
			return array($r['results'][0]['geometry']['location']['lng'], $r['results'][0]['geometry']['location']['lat']);
		}
		return false;
	}
}
