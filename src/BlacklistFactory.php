<?php

declare(strict_types=1);

namespace Tactix;

use Symfony\Component\Yaml\Yaml;
use Tactix\Assert\Assert;

final class BlacklistFactory
{
    public static function default(): Blacklist
    {
        return new Blacklist(Blacklist::DEFAULT_DATA);
    }

    /**
     * Load Blacklist from resources/config/tactix.yaml.
     */
    public static function load(): Blacklist
    {
        $path = dirname(__DIR__).'/resources/config/tactix.yaml';

        Assert::that(file_exists($path), sprintf('No file "%s"!', $path));

        return self::fromYamlFile($path);
    }

    /** @param non-empty-string $path */
    public static function fromYamlFile(string $path): Blacklist
    {
        $yaml = Yaml::parseFile($path);
        Assert::that(is_array($yaml), sprintf('Invalid YAML content "%s"!', $path));

        /** @var array{tactix: array{blacklist: array<string, array<string>>}} $yaml */
        return self::fromYaml($yaml);
    }

    /** @param array{tactix: array{blacklist: array<string, array<string>>}} $yaml */
    public static function fromYaml(array $yaml): Blacklist
    {
        return self::fromData($yaml['tactix']['blacklist']);
    }

    /** @param array<string, array<string>> $data */
    public static function fromData(array $data): Blacklist
    {
        Assert::that(!empty($data), 'Argument "$data" should not be empty!');

        return new Blacklist($data);
    }
}
