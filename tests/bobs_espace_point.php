<?php
namespace Picnat\Clicnat;

use PHPUnit\Framework\TestCase;

class bobs_espace_pointTests extends TestCase {
	public function testInsertPoint() {
		$data = [
			'id_utilisateur' => 1,
			'reference'      => 'ref plop',
			'nom'            => 'coin pan',
			'x'              => 2.299,
			'y'              => 49.892
		];
		$id_espace = bobs_espace_point::insert(get_db(), $data);
		$this->assertTrue(is_int($id_espace));

		$point = new bobs_espace_point(get_db(), $id_espace);

		$this->assertEquals($data['id_utilisateur'], $point->id_utilisateur);
		$this->assertEquals($data['reference'], $point->reference);
		$this->assertEquals($data['nom'], $point->nom);
		$this->assertEquals($data['x'], $point->get_x());
		$this->assertEquals($data['y'], $point->get_y());
		$this->assertEquals($point->get_commune()->code_insee_txt, '80021');
	}
}
