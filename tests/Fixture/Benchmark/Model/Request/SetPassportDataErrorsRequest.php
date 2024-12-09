<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\SetPassportDataErrorsResponse;
use Tests\Fixture\Benchmark\Model\Shared\PassportElementErrorDataField;
use Tests\Fixture\Benchmark\Model\Shared\PassportElementErrorFile;
use Tests\Fixture\Benchmark\Model\Shared\PassportElementErrorFiles;
use Tests\Fixture\Benchmark\Model\Shared\PassportElementErrorFrontSide;
use Tests\Fixture\Benchmark\Model\Shared\PassportElementErrorReverseSide;
use Tests\Fixture\Benchmark\Model\Shared\PassportElementErrorSelfie;
use Tests\Fixture\Benchmark\Model\Shared\PassportElementErrorTranslationFile;
use Tests\Fixture\Benchmark\Model\Shared\PassportElementErrorTranslationFiles;
use Tests\Fixture\Benchmark\Model\Shared\PassportElementErrorUnspecified;

/**
 * @api
 *
 * @implements RequestInterface<SetPassportDataErrorsResponse>
 */
final readonly class SetPassportDataErrorsRequest implements RequestInterface
{
    /**
     * @param PassportElementErrorDataField[]|PassportElementErrorFrontSide[]|PassportElementErrorReverseSide[]|PassportElementErrorSelfie[]|PassportElementErrorFile[]|PassportElementErrorFiles[]|PassportElementErrorTranslationFile[]|PassportElementErrorTranslationFiles[]|PassportElementErrorUnspecified[] $errors
     */
    public function __construct(
        public int $userId,
        public array $errors,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::SetPassportDataErrors;
    }

    public function getData(): array
    {
        return [
            'user_id' => $this->userId,
            'errors'  => array_map(
                static fn (
                    PassportElementErrorDataField|PassportElementErrorFrontSide|PassportElementErrorReverseSide|PassportElementErrorSelfie|PassportElementErrorFile|PassportElementErrorFiles|PassportElementErrorTranslationFile|PassportElementErrorTranslationFiles|PassportElementErrorUnspecified $type,
                ): array => $type->format(),
                $this->errors,
            ),
        ];
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
