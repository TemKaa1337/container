<?php

declare(strict_types=1);

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

namespace Tests\Fixture\Benchmark\Model\Shared;

use InvalidArgumentException;
use SplFileInfo;

/**
 * @api
 */
final readonly class InputFile
{
    public static function fromContent(string $content, ?string $fileName = null): self
    {
        return new self(content: $content, fileName: $fileName);
    }

    public static function fromFile(string $path, ?string $fileName = null): self
    {
        if (!file_exists($path) || !is_readable($path)) {
            throw new InvalidArgumentException(sprintf('Could not find file at path: "%s".', $path));
        }

        $fileName = $fileName ?: (new SplFileInfo($path))->getFilename();

        return new self(path: $path, fileName: $fileName);
    }

    public function format(): self
    {
        return $this;
    }

    /**
     * @return string|resource
     */
    public function getContent(): mixed
    {
        if ($this->content !== null) {
            return $this->content;
        }

        $resource = fopen($this->path, 'rb');
        if ($resource === false) {
            throw new InvalidArgumentException(sprintf('Could not open file: "%s".', $this->path));
        }

        return $resource;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    private function __construct(
        private ?string $path = null,
        private ?string $content = null,
        private ?string $fileName = null,
    ) {
    }
}
