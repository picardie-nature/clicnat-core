<?php
namespace Picnat\Clicnat;

class bobs_gpx {
	private $dom;

	public function __construct($gpx_filename) 	{
		$this->dom = new DomDocument();
		$this->dom->load($gpx_filename);
	}

	public function get_wpts() 	{
		$wpts_r = array();
		$wpts = $this->dom->getElementsByTagName('wpt');

		foreach ($wpts as $wpt) {
			$wpts_r[] = new bobs_gpx_wpt($wpt);
		}

		return $wpts_r;
	}
}
