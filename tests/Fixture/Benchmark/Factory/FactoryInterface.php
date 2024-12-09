<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Model\ResponseInterface;

/**
 * @api
 */
interface FactoryInterface
{
    public function create(array $message): ResponseInterface;

    public function supports(ApiMethod $method): bool;
}
