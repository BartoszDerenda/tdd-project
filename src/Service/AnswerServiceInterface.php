<?php
/**
 * Answer service interface.
 */

namespace App\Service;

use App\Entity\Answer;
use App\Entity\Question;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface AnswerServiceInterface.
 */
interface AnswerServiceInterface
{
    /**
     * Get paginated list.
     *
     * @param int      $page     Page number
     * @param Question $question Question entity
     *
     * @return PaginationInterface Pagination interface
     */
    public function getPaginatedList(int $page, Question $question): PaginationInterface;

    /**
     * Save entity.
     *
     * @param Answer $answer Answer entity
     */
    public function save(Answer $answer): void;

    /**
     * Delete entity.
     *
     * @param Answer $answer Answer entity
     */
    public function delete(Answer $answer): void;

    /**
     * Award entity.
     *
     * @param Answer $answer Answer entity
     */
    public function award(Answer $answer): void;

    /**
     * Deaward entity.
     *
     * @param Answer $answer Answer entity
     */
    public function deaward(Answer $answer): void;
}
