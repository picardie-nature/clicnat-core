<?php
namespace Picnat\Clicnat;

interface clicnat_geocode {
	/**
	 * @brief retourne les coordonnées de l'adresse
	 * @param $txt le texte de l'adresse
	 * @return array(long,lat) en degrés
	 */
	public static function adresse($txt);
}
