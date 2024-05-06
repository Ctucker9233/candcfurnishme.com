<?php

// src/Form/DataTransformer/IssueToNumberTransformer.php
namespace App\Form\DataTransformer;

use App\Entity\Sale;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class SaleToStringTransformer implements DataTransformerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Transforms an object (sale) to a string (number).
     *
     * @param  Sale|null $sale
     */
    public function transform($sale): string
    {
        if (null === $sale) {
            return 0;
        }
        
        return $sale->getSaleAmount();
    }

    /**
     * Transforms a string (number) to an object (sale).
     *
     * @param  string $saleNumber
     * @throws TransformationFailedException if object (sale) is not found.
     */
    public function reverseTransform($saleNumber): ?Sale
    {
        // no issue number? It's optional, so that's ok
        if (!$saleNumber) {
            return null;
        }

        $sale = $this->entityManager
            ->getRepository(Sale::class)
            // query for the issue with this id
            ->find($saleNumber)
        ;

        if (null === $sale) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(sprintf(
                'A sale with saleAmount "%s" does not exist!',
                $saleNumber
            ));
        }

        return $sale;
    }

}