<?php

namespace App\ApiPlatform;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\CheeseListing;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

class CheeseListingIsPublishedExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(private Security $security)
    {
    }

    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        if ($resourceClass !== CheeseListing::class) {
            return;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        if (!$this->security->getUser()) {
            // anonymous user only see published
            $queryBuilder->andWhere(sprintf('%s.isPublished = :isPublished', $rootAlias))
                ->setParameter('isPublished', true);
        } else {
            // logged in user can see their owned resouce(published or unpublished) and other user's published resource
            $queryBuilder->andWhere(sprintf('
                    %s.isPublished = :isPublished
                    OR %s.owner = :owner',
                $rootAlias, $rootAlias
            ))
        ->setParameter('isPublished', true)
        ->setParameter('owner', $this->security->getUser());
        }
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context = []): void
    {
        // if ($resourceClass !== CheeseListing::class) {
        //     return;
        // }

        // if ($this->security->isGranted('ROLE_ADMIN')) {
        //     return;
        // }

        // $rootAlias = $queryBuilder->getRootAliases()[0];
        // $queryBuilder->andWhere(sprintf('%s.isPublished = :isPublished', $rootAlias))
        //     ->setParameter('isPublished', true);
        $this->addWhere($queryBuilder, $resourceClass);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, Operation $operation = null, array $context = []): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }
}
