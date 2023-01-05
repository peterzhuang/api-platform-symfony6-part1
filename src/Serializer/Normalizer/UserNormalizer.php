<?php

namespace App\Serializer\Normalizer;

use App\Entity\User;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class UserNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface, NormalizerAwareInterface
{
    // public function __construct(
    //     #[Autowire(service: ObjectNormalizer::class)]
    //     private NormalizerInterface $normalizer
    // )
    // {
    // }

    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'USER_NORMALIZER_ALREADY_CALLED';

    /**
     * @param User $object
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        if ($this->userIsOwner($object)){
            $context['groups'][] = 'owner:read';
        }

        $context[self::ALREADY_CALLED] = true;

        $data = $this->normalizer->normalize($object, $format, $context);

        // TODO: add, edit, or delete some data

        return $data;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof User;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return false;
    }

    private function userIsOwner(User $user): bool
    {
        // /** @var User|null $authenticatedUser */
        // $authenticatedUser = $this->security->getUser();

        // if (!$authenticatedUser) {
        //     return false;
        // }

        // return $authenticatedUser->getEmail() === $user->getEmail();
        return mt_rand(0, 10) > 5;
    }
}
