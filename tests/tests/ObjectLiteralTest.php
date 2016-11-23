<?php
declare(strict_types = 1);


use Nette\Utils\Json;

class ObjectLiterals extends BaseTestCase
{
	public function test_createObjectLiteral()
	{
		$a = [
			(object) ['a' => 'hello', 'b' => 2],
			(object) ['c' => 2, 'd' => 2.0, 'arr' => [1, 2]],
		];
		$this->assertEquals(
			'[{"a":"hello","b":2},{"c":2,"d":2.0,"arr":[1,2]}]',
			Json::encode($a)
		);
	}

}
