<?php

// src/Form/DataTransformer/IssueToNumberTransformer.php
namespace App\Form\DataTransformer;

use App\Entity\Inventory;
use App\Entity\Sale;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ItemToStringTransformer implements DataTransformerInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * Transforms an object (issue) to a string (number).
     *
     * @param  Inventory|null $issue
     */
    public function transform($inventory): string
    {
        
        if (null === $inventory) {
            return '';
        }

        return $inventory->getItemDescription();
    }

    /**
     * Transforms a string (number) to an object (issue).
     *
     * @param  string $issueNumber
     * @throws TransformationFailedException if object (issue) is not found.
     */
    public function reverseTransform($inventory): ?Inventory
    {
        // no issue number? It's optional, so that's ok
        if (!$inventory) {
            return null;
        }

        $inventory = $this->entityManager
            ->getRepository(Inventory::class)
            // query for the issue with this id
            ->find($inventory)
        ;

        if (null === $inventory) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(sprintf(
                'An item with id "%s" does not exist!',
                $inventory
            ));
        }

        return $inventory;
    }
}