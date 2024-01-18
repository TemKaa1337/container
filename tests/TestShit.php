<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Tests\Fixture\Stub\Class\SomeInterface;
use Tests\Fixture\Stub\Class\SomeInterfaceImplementation;
use Tests\Helper\ClassStubGeneratorTrait;

final class TestShit extends TestCase
{
    use ClassStubGeneratorTrait;

    public function test(): void
    {
        // $obj = new ClassWithPrivateConstructor();
        // var_dump($obj);

        // $this->generateClass(
        //     realpath(__DIR__.'/Fixture/Stub/Class/Generated/').'/TestClass1.php',
        //     'TestClass1',
        // );
        //
        // $this->generateClass(
        //     realpath(__DIR__.'/Fixture/Stub/Class/Generated/').'/TestClass2.php',
        //     'TestClass2',
        //     hasConstructor: true,
        //     constructorArguments: [
        //         'public readonly string $dollar,', 'public readonly string $euro,', 'public readonly string $rub,'
        //     ]
        // );

        // $start = microtime(true);
        // $r = new \ReflectionClass(CircularCollectorClass::class);
        // echo ((microtime(true) - $start)).PHP_EOL;
        //
        // $start = microtime(true);
        // $r = new \ReflectionClass(CircularCollectorClass::class);
        // echo ((microtime(true) - $start)).PHP_EOL;
        //
        // $start = microtime(true);
        // $r = new \ReflectionClass(CircularCollectorClass::class);
        // echo ((microtime(true) - $start)).PHP_EOL;
        //
        // $start = microtime(true);
        // $r = new \ReflectionClass(CircularCollectorClass::class);
        // echo ((microtime(true) - $start)).PHP_EOL;

        // $config = [
        //     'config_dir'         => __DIR__,
        //     'services'           => [
        //         'include' => [
        //             // '/Fixture/Stub/Class/ClassWithDependencies.php',
        //             '/Fixture/Stub/Class/EmptyClass1.php',
        //             // '/Fixture/Stub/Class/EmptyClass2.php',
        //             // '/Fixture/Stub/Class/ClassWithInterfaceDependency.php',
        //             // '/Fixture/Stub/Class/AbstractClass.php',
        //             // '/Fixture/Stub/Class/NonAutowirableClass.php',
        //             // '/Fixture/Stub/Class/SomeInterfaceImplementation.php',
        //             // '/Fixture/Stub/Class/InheritedInterfaceImplementation.php',
        //             // '/Fixture/Stub/Class/SomeInterface.php',
        //             '/Fixture/Stub/Class/ClassWithTaggedDependencies.php',
        //             // '/Fixture/Stub/Class/TaggedCircularCollectorClass.php',
        //         ],
        //         'exclude' => [
        //             // '/Fixture/Stub/Class/EmptyClass3.php',
        //         ],
        //     ],
        //     'interface_bindings' => [
        //         SomeInterface::class => SomeInterfaceImplementation::class,
        //     ]
        // ];
        //
        // $b = new Builder(new Config(config: $config, env: []));
        // $b->build();
        //
        // $resolver = new Resolver();
        // $resolver->resolveAll($b);
    }
}
