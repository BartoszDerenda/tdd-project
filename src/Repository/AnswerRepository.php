<?php
/**
 * Answer repository.
 */

namespace App\Repository;

use App\Entity\Answer;
use App\Entity\Question;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class AnswerRepository.
 *
 * @extends ServiceEntityRepository<Answer>
 *
 * @psalm-suppress LessSpecificImplementedReturnType
 *
 * @method Answer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Answer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Answer[]    findAll()
 * @method Answer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnswerRepository extends ServiceEntityRepository
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
    public const PAGINATOR_ITEMS_PER_PAGE = 10;

    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry Manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Answer::class);
    }

    /**
     * Query all records.
     *
     * @param Question $question Question entity
     *
     * @return QueryBuilder Query builder
     */
    public function queryAnswersForQuestion(Question $question): QueryBuilder
    {
        return $this->getOrCreateQueryBuilder()
            ->select(
                'partial answer.{id, question, createdAt, updatedAt, comment, image, bestAnswer}',
            )
            ->where('answer.question = :question_id')
            ->setParameter('question_id', $question)
            ->orderBy('answer.createdAt', 'ASC');
    }

    /**
     * Save entity.
     *
     * @param Answer $answer Answer entity
     */
    public function save(Answer $answer): void
    {
        $this->_em->persist($answer);
        $this->_em->flush();
    }

    /**
     * Delete entity.
     *
     * @param Answer $answer Answer entity
     */
    public function delete(Answer $answer): void
    {
        $this->_em->remove($answer);
        $this->_em->flush();
    }

    /**
     * Add award flag to the entity.
     *
     * @param Answer $answer Answer entity
     *
     * @return void Void
     */
    public function award(Answer $answer): void
    {
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder
            ->update('App\Entity\Answer', 'a')
            ->set('a.bestAnswer', 1)
            ->where('a.id = :id')
            ->setParameter(':id', $answer)
            ->getQuery()
            ->execute();
    }

    /**
     * Remove award flag from entity.
     *
     * @param Answer $answer Answer entity
     *
     * @return void Void
     */
    public function deaward(Answer $answer): void
    {
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder
            ->update('App\Entity\Answer', 'a')
            ->set('a.bestAnswer', 0)
            ->where('a.id = :id')
            ->setParameter(':id', $answer)
            ->getQuery()
            ->execute();
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
        return $queryBuilder ?? $this->createQueryBuilder('answer');
    }
}
