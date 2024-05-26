<?php

/**
 * Tags repository.
 */

namespace App\Repository;

use App\Entity\Tags;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tags>
 *
 * @method Tags|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tags|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tags[]    findAll()
 * @method Tags[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TagsRepository extends ServiceEntityRepository
{
    /**
     * Items per page.
     *
     * Use constants to define configuration options that rarely change instead
     * of specifying them in configuration files.
     * See https://symfony.com/doc/current/best_practices.html#configuration
     *
     * @constant int
     */
    public const PAGINATOR_ITEMS_PER_PAGE = 8;

    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry Manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tags::class);
    }

    /**
     * Query all records.
     *
     * @return QueryBuilder Query builder
     */
    public function queryAll(): QueryBuilder
    {
        return $this->getOrCreateQueryBuilder()
            ->select(
                'partial tags.{id, createdAt, updatedAt, title}'
            )
            ->orderBy('tags.updatedAt', 'DESC');
    }

    /**
     * Create entity.
     *
     * @param Tags $entity Tags entity
     * @param bool $flush  Boolean
     *
     * @return void Void
     */
    public function add(Tags $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Save entity.
     *
     * @param Tags $tags Tags entity
     *
     * @return void Void
     */
    public function save(Tags $tags): void
    {
        $this->_em->persist($tags);
        $this->_em->flush();
    }

    /**
     * Delete entity.
     *
     * @param Tags $tags Tags entity
     *
     * @return void Void
     */
    public function delete(Tags $tags): void
    {
        $this->_em->remove($tags);
        $this->_em->flush();
    }

    /**
     * Find one by title.
     *
     * @param string $value Value
     *
     * @return Tags|null Tags
     *
     * @throws NonUniqueResultException Exception
     */
    public function findOneByTitle(string $value): ?Tags
    {
        return $this->createQueryBuilder('tags')
            ->andWhere('tags.title = :title')
            ->setParameter('title', $value)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get or create new query builder.
     *
     * @param QueryBuilder|null $queryBuilder Query builder
     *
     * @return QueryBuilder Query builder
     */
    private function getOrCreateQueryBuilder(QueryBuilder $queryBuilder = null): QueryBuilder
    {
        return $queryBuilder ?? $this->createQueryBuilder('tags');
    }
}
