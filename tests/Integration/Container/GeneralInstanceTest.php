<?php

declare(strict_types=1);

namespace Container;

use InvalidArgumentException;
use Temkaa\Container\Attribute\Bind\Instance;
use Tests\Integration\Container\AbstractContainerTestCase;

final class GeneralInstanceTest extends AbstractContainerTestCase
{
    public function testDoesNotLoadDueToInvalidClassName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot find class with id: "non_existing_class".');

        new Instance(
            id: 'non_existing_class',
        );
    }
}
