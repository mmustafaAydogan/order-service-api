<?php

namespace App\Serializer;

use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ApiProblemNormalizer implements NormalizerInterface
{
    public function normalize($exception, ?string $format = null, array $context = []): array
    {
        return [
            'error' => $exception->getMessage(),
            'code' => $exception->getStatusCode(),
        ];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof FlattenException;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [FlattenException::class => true];
    }
}
