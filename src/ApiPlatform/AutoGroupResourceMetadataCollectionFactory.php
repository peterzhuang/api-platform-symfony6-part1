<?php

namespace App\ApiPlatform;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operations;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\ResourceMetadataCollection;

class AutoGroupResourceMetadataCollectionFactory implements ResourceMetadataCollectionFactoryInterface
{
    private $decorated;

    public function __construct(ResourceMetadataCollectionFactoryInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function create(string $resourceClass): ResourceMetadataCollection
    {
        $resourceMetadataCollection = $this->decorated->create($resourceClass);

        foreach($resourceMetadataCollection as $key => $resourceMetadata) {
            if ($resourceMetadata->getOperations()) {
                $resourceMetadata = $resourceMetadata->withOperations($this->getTransformedOperations($resourceMetadata->getOperations(), $resourceMetadata));
            }

            $resourceMetadataCollection[$key] = $resourceMetadata;
        }
        return $resourceMetadataCollection;
    }

    private function getTransformedOperations(Operations|array $operations, ApiResource $resourceMetadata)
    {
        foreach ($operations as $key => $operation) {
            $isCollection = $operation instanceof CollectionOperationInterface;;

            $operation = $operation->withNormalizationContext(['groups' => array_unique(array_merge(
                $operation->getNormalizationContext()['groups'] ?? [],
                $this->getDefaultGroups($resourceMetadata->getShortName(), true, $isCollection, $operation->getMethod())
            ))]);

            $operation = $operation->withDenormalizationContext(['groups' => array_unique(array_merge(
                $operation->getDenormalizationContext()['groups'] ?? [],
                $this->getDefaultGroups($resourceMetadata->getShortName(), false, $isCollection, $operation->getMethod())
            ))]);

            $operations instanceof Operations ? $operations->add($key, $operation) : $operations[$key] = $operation;
        }

        return $operations;

    }


    private function getDefaultGroups(string $shortName, bool $normalization, bool $isCollection, string $method)
    {
        $shortName = strtolower($shortName);
        $readOrWrite = $normalization ? 'read' : 'write';
        $itemOrCollection = $isCollection ? 'collection' : 'item';

        return [
            // {shortName}:{read/write}
            // e.g. user:read
            sprintf('%s:%s', $shortName, $readOrWrite),
            // {shortName}:{item/collection}:{read/write}
            // e.g. user:collection:read
            sprintf('%s:%s:%s', $shortName, $itemOrCollection, $readOrWrite),
            // {shortName}:{item/collection}:{operationName}
            // e.g. user:collection:get
            sprintf('%s:%s:%s', $shortName, $itemOrCollection, strtolower($method)),
        ];
    }
}