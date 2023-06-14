<?php

namespace App\Service;

use App\Entity\Product;
use App\Entity\ProductProperty;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class ProductValidationService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function validate(Product $product, $data, $flash): bool
    {
        if ($data==null) {
            return false;
        }
        try {
            $properties = array_key_exists("properties", $data) ? $data['properties'] : [];
            $category = array_key_exists("category", $data) ? $data['category'] : throw new Exception();
            $owner = array_key_exists("owner", $data) ? $data['owner'] : throw new Exception();
            $sku = array_key_exists("sku", $data) ? $data['sku'] : throw new Exception();
        } catch (Exception) {
            return false;
        }

        $valid = $this->validateProduct($product, $properties, $sku, $category, $owner);
        foreach ($valid as $msg) {
            $flash('verify_product', $msg);
        }
        return empty($valid);
    }

    public function validateProduct(Product $product, $properties, $sku, $category_id, $owner_id): array
    {
        $productRepository = $this->entityManager->getRepository(Product::class);
        $productPropertyRepository = $this->entityManager->getRepository(ProductProperty::class);
        $errors = [];

        // Check for product with the same SKU and properties
        $withSameSkuAndProperties = $productRepository->withSameSkuAndProperties($sku, $properties);
        if ($product->getId()) {
            $withSameSkuAndProperties = array_filter($withSameSkuAndProperties, function ($p) use ($product) {
                return $p->getId() !== $product->getId();
            });
        }
        if (!empty($withSameSkuAndProperties)) {
            $errors[] = 'Product with the same SKU and properties already exists.';
        }

        // Check for duplicate property codes
        $propertyCodes = [];
        $properties = $this->entityManager->getRepository(ProductProperty::class)->findBy(['id' => $properties]);
        foreach ($properties as $property) {
            $code = $property->getCode();
            if (in_array($code, $propertyCodes)) {
                $errors[] = 'Duplicate property codes found in the product.';
                break;
            }
            $propertyCodes[] = $code;
        }


        // Checking a product with the same SKU in different categories
        $withDifferenceCategories = $productRepository->createQueryBuilder('p')
            ->where('p.sku = :sku')
            ->andWhere('p.category != :category_id')
            ->setParameter('sku', $sku)
            ->setParameter('category_id', $category_id)
            ->getQuery()
            ->getResult();

        if (!empty($withDifferenceCategories)) {
            $errors[] = 'Duplicate SKU in different categories.';
        }

        // Checking a product with the same SKU from different owners
        $withDifferenceUsers = $productRepository->createQueryBuilder('p')
            ->where('p.sku = :sku')
            ->andWhere('p.owner != :owner_id')
            ->setParameter('sku', $sku)
            ->setParameter('owner_id', $owner_id)
            ->getQuery()
            ->getResult();

        if (!empty($withDifferenceUsers)) {
            $errors[] = 'Duplicate SKU among different users.';
        }

        // Check if properties belong to the selected category
        $propertiesCount = $productPropertyRepository->createQueryBuilder('pp')
            ->select('COUNT(pp.id) AS count')
            ->where('pp.id IN (:properties)')
            ->andWhere('pp.category = :category_id')
            ->setParameter('properties', $properties)
            ->setParameter('category_id', $category_id)
            ->getQuery()
            ->getSingleScalarResult();

        if ($propertiesCount !== count($properties)) {
            $errors[] = 'One or more product properties do not belong to the selected category.';
        }

        return $errors;
    }
}
