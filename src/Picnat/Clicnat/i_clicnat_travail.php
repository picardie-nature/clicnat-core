<?php
namespace Picnat\Clicnat;

interface i_clicnat_travail {
	public function __construct($db, $args);
	public function executer();
}
