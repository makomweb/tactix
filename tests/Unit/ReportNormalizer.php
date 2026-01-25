<?php

declare(strict_types=1);

namespace Tactix\Tests\Unit;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Tactix\Command\Report;

final class ReportNormalizer implements NormalizerInterface
{
    /**
     * @return array<string, mixed>
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        assert($object instanceof Report);

        return [
            'aggregateRoots' => $object->aggregateRoots,
            'entities' => $object->entities,
            'factories' => $object->factories,
            'repositories' => $object->repositories,
            'services' => $object->services,
            'valueObjects' => $object->valueObjects,
            'interfaces' => $object->interfaces,
            'exceptions' => $object->exceptions,
            'uncategorized' => $object->uncategorized,
        ];
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Report;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Report::class => true,
        ];
    }
}
