<?php

namespace App\Repository;

use App\Entity\Media;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Media>
 */
class MediaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Media::class);
    }

    public function findAllPublished(): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.isPublished = :published')
            ->setParameter('published', true)
            ->orderBy('m.position', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
