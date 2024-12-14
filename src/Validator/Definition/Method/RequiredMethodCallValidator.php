<?php

declare(strict_types=1);

namespace Temkaa\Container\Validator\Definition\Method;

use ReflectionClass;
use Temkaa\Container\Attribute\Bind\Required;
use Temkaa\Container\Exception\RequiredMethodCallException;
use Temkaa\Container\Model\Config\ClassConfig;
use function sprintf;

/**
 * @internal
 */
final readonly class RequiredMethodCallValidator
{
    public function validate(?ClassConfig $config, ReflectionClass $reflection): void
    {
        $this->validateAttributeCalls($reflection);

        $this->validateConfigCalls($config, $reflection);
    }

    private function validateAttributeCalls(ReflectionClass $reflection): void
    {
        $methods = $reflection->getMethods();
        foreach ($methods as $method) {
            $requiredAttribute = $method->getAttributes(Required::class);
            if (!$requiredAttribute) {
                continue;
            }

            $this->validateMethod($reflection, $method->getName());
        }
    }

    private function validateConfigCalls(?ClassConfig $config, ReflectionClass $reflection): void
    {
        foreach ($config?->getMethodCalls() ?? [] as $method) {
            $this->validateMethod($reflection, $method);
        }
    }

    private function validateMethod(ReflectionClass $reflection, string $methodName): void
    {
        if ($methodName === '__construct') {
            throw new RequiredMethodCallException(
                sprintf(
                    'Could not call method "%s::%s" as it is constructor.',
                    $reflection->getName(),
                    $methodName,
                ),
            );
        }

        if (!$reflection->hasMethod($methodName)) {
            throw new RequiredMethodCallException(
                sprintf(
                    'Class "%s" does not have method called "%s".',
                    $reflection->getName(),
                    $methodName,
                ),
            );
        }

        $method = $reflection->getMethod($methodName);
        if ($method->isStatic()) {
            throw new RequiredMethodCallException(
                sprintf(
                    'Calling static method "%s::%s" is not supported.',
                    $reflection->getName(),
                    $method->getName(),
                ),
            );
        }

        if (!$method->isPublic()) {
            throw new RequiredMethodCallException(
                sprintf(
                    'Could not call method "%s::%s" as it is not public.',
                    $reflection->getName(),
                    $method->getName(),
                ),
            );
        }
    }
}
