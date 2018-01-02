<?php
namespace Picnat\Clicnat;

use PHPUnit\Framework\TestCase;
use Picnat\Clicnat\ExtractionsConditions\bobs_extractions_conditions;

class bobs_extractionsTests extends TestCase {
	public function testListeConditions() {
		$conditions = bobs_extractions::get_conditions_dispo();
		$this->assertTrue(is_array($conditions));
		$this->assertTrue(count($conditions) > 0);
		foreach ($conditions as $conditionClass => $titre) {
			$this->assertNotEmpty($titre);
			$this->assertTrue(is_subclass_of($conditionClass, bobs_extractions_conditions::class));
		}
		bobs_extractions::get_conditions_dispo(true);
		return $conditions;
	}
}
