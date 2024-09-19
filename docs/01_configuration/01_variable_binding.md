# Binding variables into class

Using this feature you can bind env variable values or even expressions into classes.
Example using config:
```php
<?php

declare(strict_types=1);

use Temkaa\Container\Builder\ConfigBuilder;
use Temkaa\Container\Builder\Config\ClassBuilder;
use Temkaa\Container\Builder\ContainerBuilder;

enum BackedEnum: string
{
    case CaseOne = 'case_one';
}

enum UnitEnum
{
    case CaseOne;
}

final readonly class ClassWithBoundedEnvVariables
{
    public function __construct(
        public string $stringVar,
        public int $intVar,
        public float $floatVar,
        public BackedEnum $backedEnum,
        public UnitEnum $unitEnum,
        public string $expressionVar,
    ) {
    }
}

$envVariables = [
    'STRING_ENV_VAR' => 'string',
    'INT_ENV_VAR' => '10',
    'FLOAT_ENV_VAR' => '10.12',
    'REFERENCE_ENV_VAR' => 'env(STRING_ENV_VAR)_env(INT_ENV_VAR)',
    'EXPRESSION_ENV_VAR' => 'env(REFERENCE_ENV_VAR)',
];

foreach ($envVariables as $envVariable => $envVariableValue) {
    putenv("$envVariable=$envVariableValue");
}

$config = ConfigBuilder::make()
    ->include(__DIR__.'../../some/path/with/classes/')
    ->bindClass(
        ClassBuilder::make(ClassWithAlias::class)
            ->bindVariable('$stringVar', 'env(STRING_ENV_VAR)')
            ->bindVariable('intVar', 'env(INT_ENV_VAR)')
            ->bindVariable('$floatVar', 'env(FLOAT_ENV_VAR)')
            ->bindVariable('backedEnum', BackedEnum::CaseOne)
            ->bindVariable('$unitEnum', UnitEnum::CaseOne)
            ->bindVariable('expressionVar', 'env(EXPRESSION_ENV_VAR)')
            ->build(),
    )
    ->build();

$container = ContainerBuilder::make()->add($config)->build();

/**
 * in this instance all constructor properties will be substituted with bounded variables:
 * $stringVar = 'string'
 * $intVar = 10
 * $floatVar = 10.12
 * $backedEnum = BackedEnum::CaseOne
 * $unitEnum = UnitEnum::CaseOne
 * $expressionVar = 'string_10'
 */
$classWithBoundedVariables = $container->get(ClassWithBoundedEnvVariables::class);
```

Example using attributes:
```php
<?php

declare(strict_types=1);

use Temkaa\Container\Builder\ConfigBuilder;
use Temkaa\Container\Builder\Config\ClassBuilder;
use Temkaa\Container\Builder\ContainerBuilder;
use Temkaa\Container\Attribute\Bind\Parameter;

enum BackedEnum: string
{
    case CaseOne = 'case_one';
}

enum UnitEnum
{
    case CaseOne;
}

final readonly class ClassWithBoundedEnvVariables
{
    public function __construct(
        #[Parameter('env(STRING_ENV_VAR)')]
        public string $stringVar,
        #[Parameter('env(INT_ENV_VAR)')]
        public int $intVar,
        #[Parameter('env(FLOAT_ENV_VAR)')]
        public float $floatVar,
        #[Parameter(BackedEnum::CaseOne)]
        public BackedEnum $backedEnum,
        #[Parameter(UnitEnum::CaseOne)]
        public UnitEnum $unitEnum,
        #[Parameter('env(EXPRESSION_ENV_VAR)')]
        public string $expressionVar,
    ) {
    }
}

$envVariables = [
    'STRING_ENV_VAR' => 'string',
    'INT_ENV_VAR' => '10',
    'FLOAT_ENV_VAR' => '10.12',
    'REFERENCE_ENV_VAR' => 'env(STRING_ENV_VAR)_env(INT_ENV_VAR)',
    'EXPRESSION_ENV_VAR' => 'env(REFERENCE_ENV_VAR)',
];

foreach ($envVariables as $envVariable => $envVariableValue) {
    putenv("$envVariable=$envVariableValue");
}

$config = ConfigBuilder::make()
    ->include(__DIR__.'../../some/path/with/classes/')
    ->build();

$container = ContainerBuilder::make()->add($config)->build();

// result here is the same as in config example
$classWithBoundedVariables = $container->get(ClassWithBoundedEnvVariables::class);
```
Also, config allows you to bind variables not in a single class scope, but globally, example:
```php
<?php

declare(strict_types=1);

use Temkaa\Container\Builder\ConfigBuilder;
use Temkaa\Container\Builder\Config\ClassBuilder;
use Temkaa\Container\Builder\ContainerBuilder;

enum BackedEnum: string
{
    case CaseOne = 'case_one';
}

enum UnitEnum
{
    case CaseOne;
}

final readonly class ClassWithBoundedEnvVariables
{
    public function __construct(
        public string $githubToken,
        public int $githubTokenLifetime,
    ) {
    }
}

final readonly class ClassWithOverwrittenBoundedEnvVariables
{
    public function __construct(
        public string $githubToken,
        public int $githubTokenLifetime,
    ) {
    }
}

// this is how to bind variable values globally, e.g. any class which has `$githubToken` or `$githubTokenLifetime` signature
// will have this bounded values
// please note here, class context binding takes precedence over global variable values
$config = ConfigBuilder::make()
    ->include(__DIR__.'../../some/path/with/classes/')
    ->bindVariable('$githubToken', 'some_token_here')
    ->bindVariable('$githubTokenLifetime', '1200')
    ->bindClass(
        ClassBuilder::make(ClassWithOverwrittenBoundedEnvVariables::class),
            ->bindVariable('$githubToken', 'some_other_here')
            ->bindVariable('$githubTokenLifetime', '2400')
            ->build(),
    )
    ->build();

$container = ContainerBuilder::make()->add($config)->build();

/**
 * in this instance all constructor properties will be substituted with bounded variables:
 * $githubToken = 'some_token_here'
 * $githubTokenLifetime = 1200
 */
$classWithBoundedVariables = $container->get(ClassWithBoundedEnvVariables::class);

/**
 * in this instance all constructor properties will be substituted with bounded variables:
 * $githubToken = 'some_other_here'
 * $githubTokenLifetime = 2400
 */
$classWithBoundedVariables = $container->get(ClassWithOverwrittenBoundedEnvVariables::class);
```
