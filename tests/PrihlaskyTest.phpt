<?php

namespace Test;

use Nette,
	Tester,
	Tester\Assert;

$container = require __DIR__ . '/bootstrap.php';


class PrihlaskyTest extends Tester\TestCase
{
	private $container;
    private $prihlasky;

	function __construct(Nette\DI\Container $container, App\Models\Prihlasky $prihlasky)
	{
		$this->container = $container;
		$this->prihlasky = $prihlasky;
	}


	function setUp()
	{
	}


	function testSomething()
	{
		Assert::true( true );
	}

}


id(new ExampleTest($container))->run();
