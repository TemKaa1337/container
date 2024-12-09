<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Shared;

final readonly class PassportElementErrorTranslationFiles
{
    /**
     * @param string[] $fileHashes
     */
    public function __construct(
        public string $source,
        public string $type,
        public array $fileHashes,
        public string $message,
    ) {
    }

    public function format(): array
    {
        return [
            'source'      => $this->source,
            'type'        => $this->type,
            'file_hashes' => $this->fileHashes,
            'message'     => $this->message,
        ];
    }
}
