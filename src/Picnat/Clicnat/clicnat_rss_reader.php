<?php
namespace Picnat\Clicnat;

class clicnat_rss_reader implements Iterator {
	protected $doc;

	private $position;
	private $items;

	public function __construct($url) {
		$f = fopen($url, "r");
		$l = '';

		$this->position = 0;

		$data = '';
		while ($l = fgets($f))
			$data .= $l;

		$this->doc = new DOMDocument();
		$this->doc->loadXML($data);

		$titre = null;
		$description = null;
		$lien = null;
		$date = null;
		$items_liste = $this->doc->getElementsByTagName('item');
		foreach ($items_liste as $item) {
			$titre = $description = $lien = '';
			foreach ($item->childNodes as $e) {
				switch ($e->nodeName) {
					case 'title':
						$titre = $e->nodeValue;
						break;
					case 'description':
						$description = $e->nodeValue;
						break;
					case 'link':
						$lien = $e->nodeValue;
						break;
					case 'pubDate':
						$date = strftime($e->nodeValue);
						break;
				}
			}
			$this->items[] = new clicnat_rss_reader_item($titre, $description, $lien, $date);
		}
	}

	public function rewind() {
		$this->position = 0;
	}

	public function current() {
		return $this->items[$this->position];
	}

	public function key() {
		return $this->position;
	}

	public function next() {
		$this->position++;
	}

	public function valid() {
		return isset($this->items[$this->position]);
	}
}
