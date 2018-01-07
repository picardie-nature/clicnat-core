<?php
define("BOBS_LOG_FILE", "/tmp/clicnat.log");
require_once(__DIR__.'/../vendor/autoload.php');
$dbstr = getenv("POSTGRES_DB_TEST");
if (empty($dbstr)) {
	throw new \Exception("define POSTGRES_DB_TEST");
}
$db = pg_connect($dbstr);
\Picnat\Clicnat\get_db($db);
