<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Shared;

final readonly class PassportElementErrorDataField
{
    public function __construct(
        public string $source,
        public string $type,
        public string $fieldName,
        public string $dataHash,
        public string $message,
    ) {
    }

    public function format(): array
    {
        return [
            'source'     => $this->source,
            'type'       => $this->type,
            'field_name' => $this->fieldName,
            'data_hash'  => $this->dataHash,
            'message'    => $this->message,
        ];
    }
}
