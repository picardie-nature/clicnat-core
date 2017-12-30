<?php
namespace Picnat\Clicnat;

/**
 * @deprecated
 */
class bobs_rss {
    protected $db;
    public $title;
    public $desc;
    public $link;
    public $cache_file;

    function __toString()
    {

	if (empty($this->cache_file))
		throw new Exception("no cachefile defined");

	if (!file_exists($this->cache_file))
		$this->cache();

	return file_get_contents($this->cache_file);
    }

    function __construct($db, $title, $desc, $link, $cache_file)
    {
	$this->db = $db;
	$this->title = $title;
	$this->desc = $desc;
	$this->link = $link;
	$this->cache_file = $cache_file;
    }

    /**
     *
     * @return string items
     */
    function get_items()
    {
	return "";
    }

    function build()
    {
	$ldate = date("r");
	return "<?xml version=\"1.0\" encoding=\"utf-8\"?>
	    <rss version=\"2.0\">
		<channel>
		    <title>{$this->title}</title>
		    <description>{$this->desc}</description>
		    <lastBuildDate>{$ldate}</lastBuildDate>
		    <link>{$this->link}</link>
		    {$this->get_items()}
		</channel>
	    </rss>\n";
    }

    function cache() {
	$f = fopen($this->cache_file, "w");
	fwrite($f, $this->build());
	fclose($f);
    }
}
