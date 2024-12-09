<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\EncryptedPassportElement;
use Tests\Fixture\Benchmark\Model\Response\Nested\PassportFile;

final readonly class EncryptedPassportElementFactory
{
    public function __construct(private PassportFileFactory $passportFileFactory)
    {
    }

    public function create(array $message): EncryptedPassportElement
    {
        return new EncryptedPassportElement(
            $message['type'],
            $message['hash'],
            $message['data'] ?? null,
            $message['phone_number'] ?? null,
            $message['email'] ?? null,
            match (true) {
                isset($message['files']) => array_map(
                    fn (array $nested): PassportFile => $this->passportFileFactory->create($nested),
                    $message['files'],
                ),
                default                  => null,
            },
            isset($message['front_side']) ? $this->passportFileFactory->create($message['front_side']) : null,
            isset($message['reverse_side']) ? $this->passportFileFactory->create($message['reverse_side']) : null,
            isset($message['selfie']) ? $this->passportFileFactory->create($message['selfie']) : null,
            match (true) {
                isset($message['translation']) => array_map(
                    fn (array $nested): PassportFile => $this->passportFileFactory->create($nested),
                    $message['translation'],
                ),
                default                        => null,
            },
        );
    }
}
