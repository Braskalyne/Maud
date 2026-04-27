<?php

namespace App\Repository;

use App\Entity\Gallery;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Gallery>
 */
class GalleryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Gallery::class);
    }

    public function findPublishedByCategory(string $category): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.category = :category')
            ->andWhere('g.isPublished = :published')
            ->setParameter('category', $category)
            ->setParameter('published', true)
            ->orderBy('g.position', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
