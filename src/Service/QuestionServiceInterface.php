<?php
/**
 * Question service interface.
 */

namespace App\Service;

use App\Entity\Category;
use App\Entity\Question;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface QuestionServiceInterface.
 */
interface QuestionServiceInterface
{
    /**
     * Get paginated list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page): PaginationInterface;

    /**
     * Get paginated list for category.
     *
     * @param int      $page     Page number
     * @param Category $category Category entity
     *
     * @return PaginationInterface Pagination interface
     */
    public function queryByCategory(int $page, Category $category): PaginationInterface;

    /**
     * Find one by id.
     *
     * @param int $id Id
     */
    public function findOneById(int $id): ?Question;

    /**
     * Save entity.
     *
     * @param Question $question Question entity
     */
    public function save(Question $question): void;

    /**
     * Delete entity.
     *
     * @param Question $question Question entity
     */
    public function delete(Question $question): void;
}
