<?php

declare(strict_types=1);

namespace Tests\Helper;

trait ClassStubGeneratorTrait
{
    private const CLASS_STUB_WITHOUT_CONSTRUCTOR = <<<CLASS
    <?php
    
    declare(strict_types=1);
    
    namespace Tests\Fixture\Stub\Class;
    
    %s
    %s %s %s %s
    {
    }
    CLASS;
    private const CLASS_STUB_WITH_CONSTRUCTOR = <<<CLASS
    <?php
    
    declare(strict_types=1);
    
    namespace Tests\Fixture\Stub\Class;
    
    %s
    %s %s %s %s
    {
        %s function __construct(
            %s
        ) {
        }
    }
    CLASS;

    protected static function generateClass(
        string $absolutePath,
        string $className,
        array $attributes = [],
        array $interfacesImplements = [],
        array $extends = [],
        string $classNamePrefix = 'final class',
        string $constructorVisibility = 'public',
        bool $hasConstructor = false,
        array $constructorArguments = [],
    ): void {
        file_put_contents(
            $absolutePath,
            $hasConstructor
                ? sprintf(
                self::CLASS_STUB_WITH_CONSTRUCTOR,
                implode(PHP_EOL, $attributes),
                $classNamePrefix,
                $className,
                $extends ? 'extends '.implode(', ', $extends) : '',
                $interfacesImplements ? ' implements '.implode(', ', $interfacesImplements) : '',
                $constructorVisibility,
                implode(PHP_EOL.'        ', $constructorArguments),
            )
                : sprintf(
                self::CLASS_STUB_WITHOUT_CONSTRUCTOR,
                implode(PHP_EOL, $attributes),
                $classNamePrefix,
                $className,
                $extends ? 'extends '.implode(', ', $extends) : '',
                $interfacesImplements ? ' implements '.implode(', ', $interfacesImplements) : '',
            ),
        );
    }
}
