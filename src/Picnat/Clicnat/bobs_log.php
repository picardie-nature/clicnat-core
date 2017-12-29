<?php

namespace \Picnat\Clicnat;

function bobs_log($msg) {
	global $context;
	$sid = session_id();
	$f = fopen(BOBS_LOG_FILE, "a+");

	if (!$f)
	throw new Exception('open log file failed '.BOBS_LOG_FILE);

	flock($f, LOCK_EX);
	fprintf($f, "%s bobs-%s (sid=%s) %s\n",
		strftime("%Y-%m-%d %H:%M:%S", mktime()), $context, $sid, $msg);
	flock($f, LOCK_UN);
	fclose($f);
}
