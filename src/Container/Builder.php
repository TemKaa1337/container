<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Container;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use ReflectionException;
use SplFileInfo;
use Symfony\Component\Yaml\Yaml;
use Temkaa\SimpleContainer\Enum\Config\Structure;
use Temkaa\SimpleContainer\Factory\Config\ConfigFactory;
use Temkaa\SimpleContainer\Model\Container\Config;
use Temkaa\SimpleContainer\Provider\Config\ValidatorProvider;
use Temkaa\SimpleContainer\Validator\Config\FileInfoValidator;
use Temkaa\SimpleContainer\Validator\Config\ValidatorInterface;

/**
 * @psalm-api
 */
final class Builder
{
    /**
     * @var Config[]
     */
    private array $configs = [];

    /**
     * @var ValidatorInterface[] $validators
     */
    private array $validators;

    public function __construct()
    {
        $this->validators = (new ValidatorProvider())->provide();
    }

    public function add(SplFileInfo $file): self
    {
        (new FileInfoValidator())->validate($file);

        $config = Yaml::parseFile($file->getRealPath(), Yaml::PARSE_CUSTOM_TAGS);
        $config[Structure::File->value] = $file;

        foreach ($this->validators as $validator) {
            $validator->validate($config);
        }

        $this->configs[] = (new ConfigFactory($config, $file))->create();

        return $this;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function compile(): ContainerInterface
    {
        return (new Compiler($this->configs))->compile();
    }
}
