<?php

namespace App\Serializer;

use ApiPlatform\Serializer\SerializerContextBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use App\Entity\Book;

final class AdminGroupsContextBuilder implements SerializerContextBuilderInterface
{
    private $decorated;
    private $authorizationChecker;

    public function __construct(SerializerContextBuilderInterface $decorated, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->decorated = $decorated;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);
        // $resourceClass = $context['resource_class'] ?? null;

        // if ($resourceClass === Book::class && isset($context['groups']) && $this->authorizationChecker->isGranted('ROLE_ADMIN') && false === $normalization) {
        //     $context['groups'][] = 'admin:input';
        // }

        $context['groups'] = $context['groups'] ?? [];

        $isAdmin = $this->authorizationChecker->isGranted('ROLE_ADMIN');

        if ( $isAdmin ) {
            $context['groups'][] = $normalization ? 'admin:read' : 'admin:write';
        }

        $context['groups'] = array_unique($context['groups']);

        return $context;
    }
}