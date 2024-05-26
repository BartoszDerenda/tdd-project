<?php
/**
 * Tags service.
 */

namespace App\Service;

use App\Entity\Tags;
use App\Repository\TagsRepository;
use Doctrine\ORM\NonUniqueResultException;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class TagsService.
 */
class TagsService implements TagsServiceInterface
{
    /**
     * Tags repository.
     */
    private TagsRepository $tagsRepository;

    /**
     * Paginator.
     */
    private PaginatorInterface $paginator;

    /**
     * Constructor.
     *
     * @param TagsRepository     $tagsRepository Tags repository
     * @param PaginatorInterface $paginator      Paginator
     */
    public function __construct(TagsRepository $tagsRepository, PaginatorInterface $paginator)
    {
        $this->tagsRepository = $tagsRepository;
        $this->paginator = $paginator;
    }

    /**
     * Get paginated list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->tagsRepository->queryAll(),
            $page,
            TagsRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Save entity.
     *
     * @param Tags $tags Tags entity
     */
    public function save(Tags $tags): void
    {
        $this->tagsRepository->save($tags);
    }

    /**
     * Delete entity.
     *
     * @param Tags $tags Tags entity
     */
    public function delete(Tags $tags): void
    {
        $this->tagsRepository->delete($tags);
    }

    /**
     * Find by title.
     *
     * @param string $title Tag title
     *
     * @return Tags|null Tag entity
     *
     * @throws NonUniqueResultException
     */
    public function findOneByTitle(string $title): ?Tags
    {
        return $this->tagsRepository->findOneByTitle($title);
    }
}
