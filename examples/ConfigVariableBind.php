<?php

declare(strict_types=1);

namespace Example;

require __DIR__.'/../vendor/autoload.php';

use Example\ConfigVariableBind\Class1;
use Example\ConfigVariableBind\Enum1;
use Stringable;
use Temkaa\Container\Builder\Config\ClassBuilder;
use Temkaa\Container\Builder\ConfigBuilder;
use Temkaa\Container\Builder\ContainerBuilder;
use function var_dump;

putenv('ENV_VAR_1=env_var_1_value');
putenv('ENV_VAR_2=2');

$stringableClass = new class implements Stringable {
    public function __toString(): string
    {
        return 'stringableClass';
    }
};
$callback = static fn (): string => 'string';
$object = new class {};

$config = ConfigBuilder::make()
    ->include(__DIR__.'/ConfigVariableBind/')
    ->bindVariable('stringVariable1', 'variable_1_value')
    ->configure(
        ClassBuilder::make(Class1::class)
            ->bindVariable('stringVariable2', 'env(ENV_VAR_2)')
            ->bindVariable('stringVariable3', $stringableClass)
            ->bindVariable('stringVariable4', Enum1::CaseOne)
            ->bindVariable('intVariable1', 10)
            ->bindVariable('intVariable2', '10')
            ->bindVariable('floatVariable1', 10.1)
            ->bindVariable('floatVariable2', '10.1')
            ->bindVariable('boolVariable1', true)
            ->bindVariable('boolVariable2', 'true')
            ->bindVariable('boolVariable3', '1')
            ->bindVariable('boolVariable4', false)
            ->bindVariable('boolVariable5', 'false')
            ->bindVariable('boolVariable6', '0')
            ->bindVariable('closureVariable1', $callback)
            ->bindVariable('objectVariable1', $object)
            ->build(),
    )
    ->build();

$container = ContainerBuilder::make()->add($config)->build();

/**
 * object(Example\ConfigVariableBind\Class1)#47 (16) {
 *     ["stringVariable1"]=>
 *         string(16) "variable_1_value"
 *     ["stringVariable2"]=>
 *         string(1) "2"
 *     ["stringVariable3"]=>
 *         string(15) "stringableClass"
 *     ["stringVariable4"]=>
 *         string(7) "CaseOne"
 *     ["intVariable1"]=>
 *         int(10)
 *     ["intVariable2"]=>
 *         int(10)
 *     ["floatVariable1"]=>
 *         float(10.1)
 *     ["floatVariable2"]=>
 *         float(10.1)
 *     ["boolVariable1"]=>
 *         bool(true)
 *     ["boolVariable2"]=>
 *         bool(true)
 *     ["boolVariable3"]=>
 *         bool(true)
 *     ["boolVariable4"]=>
 *         bool(false)
 *     ["boolVariable5"]=>
 *         bool(false)
 *     ["boolVariable6"]=>
 *         bool(false)
 *     ["closureVariable1"]=>
 *         object(Closure)#3 (0) {
 *         }
 *     ["objectVariable1"]=>
 *         object(class@anonymous)#5 (0) {
 *         }
 * }
 */
$class = $container->get(Class1::class);
