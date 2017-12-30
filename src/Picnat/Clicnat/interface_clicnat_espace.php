<?php
namespace Picnat\Clicnat;

interface interface_clicnat_espace {
	public function get_communes();
	public function get_departements();
	public function get_littoraux();
	public function get_toponymes();
	public function __toString();
}
