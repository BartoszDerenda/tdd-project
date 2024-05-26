<?php
/**
 * Answer service.
 */

namespace App\Service;

use App\Entity\Answer;
use App\Entity\Question;
use App\Repository\AnswerRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class AnswerService.
 */
class AnswerService implements AnswerServiceInterface
{
    /**
     * Answer repository.
     */
    private AnswerRepository $answerRepository;

    /**
     * Paginator.
     */
    private PaginatorInterface $paginator;

    /**
     * Constructor.
     *
     * @param AnswerRepository   $answerRepository Answer repository
     * @param PaginatorInterface $paginator        Paginator
     */
    public function __construct(AnswerRepository $answerRepository, PaginatorInterface $paginator)
    {
        $this->answerRepository = $answerRepository;
        $this->paginator = $paginator;
    }

    /**
     * Get paginated list.
     *
     * @param int      $page     Page number
     * @param Question $question Question entity
     *
     * @return PaginationInterface Pagination interface
     */
    public function getPaginatedList(int $page, Question $question): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->answerRepository->queryAnswersForQuestion($question),
            $page,
            AnswerRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Save entity.
     *
     * @param Answer $answer Answer entity
     */
    public function save(Answer $answer): void
    {
        $this->answerRepository->save($answer);
    }

    /**
     * Delete entity.
     *
     * @param Answer $answer Answer entity
     */
    public function delete(Answer $answer): void
    {
        $this->answerRepository->delete($answer);
    }

    /**
     * Award entity.
     *
     * @param Answer $answer Answer entity
     */
    public function award(Answer $answer): void
    {
        $this->answerRepository->award($answer);
    }

    /**
     * Deaward entity.
     *
     * @param Answer $answer Answer entity
     */
    public function deaward(Answer $answer): void
    {
        $this->answerRepository->deaward($answer);
    }
}
