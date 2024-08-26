<?php

declare(strict_types=1);

namespace Tests\Helper\Service;

final class ClassGenerator
{
    private const string CLASS_STUB_WITHOUT_CONSTRUCTOR = <<<CLASS
    <?php
    
    declare(strict_types=1);
    
    namespace Tests\Fixture\Stub\Class;
    
    %s
    %s %s %s %s
    {
    }
    CLASS;
    private const string CLASS_STUB_WITH_CONSTRUCTOR = <<<CLASS
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

    private static int $generatedClassNumber = 0;

    /**
     * @var ClassBuilder[] $builders
     */
    private array $builders = [];

    public static function getClassName(): string
    {
        return 'TestClass'.(++self::$generatedClassNumber);
    }

    public function addBuilder(ClassBuilder $builder): self
    {
        $this->builders[] = $builder;

        return $this;
    }

    public function generate(): void
    {
        foreach ($this->builders as $builder) {
            /** @psalm-suppress MixedArgumentTypeCoercion */
            $args = [
                implode(PHP_EOL, $builder->getAttributes()),
                $builder->getPrefix(),
                $builder->getName(),
                $builder->getExtends() ? 'extends '.implode(', ', $builder->getExtends()) : '',
                $builder->getInterfaceImplementations()
                    ? ' implements '.implode(', ', $builder->getInterfaceImplementations())
                    : '',
            ];

            if ($builder->hasConstructor()) {
                /** @psalm-suppress MixedArgumentTypeCoercion */
                $args = [
                    ...$args,
                    $builder->getConstructorVisibility(),
                    implode(PHP_EOL.'        ', $builder->getConstructorArguments()),
                ];
            }

            file_put_contents(
                $builder->getAbsolutePath(),
                sprintf(
                    $builder->hasConstructor()
                        ? self::CLASS_STUB_WITH_CONSTRUCTOR
                        : self::CLASS_STUB_WITHOUT_CONSTRUCTOR,
                    ...$args,
                ),
            );
        }
    }
}
