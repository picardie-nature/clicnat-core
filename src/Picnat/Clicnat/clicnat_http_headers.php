<?php
namespace Picnat\Clicnat;

trait clicnat_http_headers {
	public static function header_csv($filename,$size=0) {
		header("Content-type: text/csv; charset=UTF-8");
		//header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"$filename\";" );
		//header("Content-Transfer-Encoding: binary");
	}

	public static function header_json() {
		header('Content-type: application/json');
	}

	public static function header_xml() {
		header('Content-type: text/xml');
	}

	public static function header_403() {
		header("HTTP/1.0 403 Forbidden");
	}

	public static function header_404() {
		header("HTTP/1.0 404 Not Found");
	}

	public static function header_500() {
		header("HTTP/1.0 500 Internal Server Error");
	}

	public static function header_kml() {
		header('Content-type: application/vnd.google-earth.kml+xml');
	}

	public static function header_pdf() {
		header('Content-Type: application/pdf');
	}

	public static function header_zip() {
		header("Content-Description: File Transfer");
		header("Content-type: application/octet-stream");
		header("Content-Transfer-Encoding: binary");
	}

	public static function header_ods() {
		header('Content-Type: application/vnd.oasis.opendocument.spreadsheet');
	}

	public static function header_filename($filename) {
		header("Content-Disposition: attachment; filename=\"$filename\"");
	}

	/**
	 * @brief ajout d'en-têtes pour la mise en cache public
	 *
	 * l'objectif est aussi de permettre la mise en cache par le reverse proxy
	 * c'est pourquoi le contenu ne doit pas être lié à la session
	 */
	public static function header_cacheable($expiration_en_secondes=86400) {
		// pour tester la validité des entetes
		// http://redbot.org
		$h = new Datetime("now");
		$h->setTimeZone(new DateTimeZone('Europe/London'));
		$h->add(new DateInterval(sprintf("PT%dS",$expiration_en_secondes)));
		header("Expires: ".str_replace('+0000','GMT',$h->format(DateTime::RFC1123)));
		header(sprintf('Cache-control: public, max-age=%d', $expiration_en_secondes));
		header_remove('Set-Cookie');
		header_remove('X-Powered-By');
		header_remove('Pragma');
	}
}
