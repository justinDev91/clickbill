<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    private const IS_NOT_DELETED = 'product.isDeleted = false';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * Get all active products
     * 
     * @return Product[] Array of all products not deleted
     */
    public function getAllActiveProducts($company): array
    {
        return $this->createQueryBuilder('product')
            ->andWhere(self::IS_NOT_DELETED)
            ->andWhere('product.company = :company')
            ->setParameter('company', $company)
            ->getQuery()
            ->getResult();
    }


    public function searchProductsByNameOrDescription($term, $company): ?array
    {
        return $this->createQueryBuilder('product')
            ->andWhere(self::IS_NOT_DELETED)
            ->andWhere('product.name LIKE :searchTerm
            OR product.description LIKE :searchTerm')
            ->andWhere('product.company = :company')
            ->setParameter('searchTerm', '%' . $term . '%')
            ->setParameter('company', $company)
            ->getQuery()
            ->getResult();
    }

    /**
     * Filters product by category 
     *
     * @param string $category to filter by.
     * @return array|null An array of product matching the specified category.
     */
    public function filterProductsByCategory($category, $company): ?array
    {
        return $this->createQueryBuilder('product')
            ->join('product.category', 'category')
            ->andWhere('category.name = :category')
            ->andWhere('product.company = :company')
            ->andWhere(self::IS_NOT_DELETED)
            ->setParameter('category', $category)
            ->setParameter('company', $company)
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Product[] Returns an array of Product objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Product
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
