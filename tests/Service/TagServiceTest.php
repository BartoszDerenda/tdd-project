<?php
/**
 * Tags service tests.
 */

namespace App\Tests\Service;

use App\Entity\Tags;
use App\Service\TagsService;
use App\Service\TagsServiceInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class TagsServiceTest.
 */
class TagsServiceTest extends KernelTestCase
{
    /**
     * Tags repository.
     */
    private ?EntityManagerInterface $entityManager;

    /**
     * Tags service.
     */
    private ?TagsServiceInterface $categoryService;

    /**
     * Set up test.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function setUp(): void
    {
        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        $this->categoryService = $container->get(TagsService::class);
    }

    /**
     * Test save.
     *
     * @throws ORMException
     */
    public function testSave(): void
    {
        // given
        $expectedTags = new Tags();
        $expectedTags->setTitle('Test Tags');

        // when
        $this->categoryService->save($expectedTags);

        // then
        $expectedTagsId = $expectedTags->getId();
        $resultTags = $this->entityManager->createQueryBuilder()
            ->select('category')
            ->from(Tags::class, 'category')
            ->where('category.id = :id')
            ->setParameter(':id', $expectedTagsId, Types::INTEGER)
            ->getQuery()
            ->getSingleResult();

        $this->assertEquals($expectedTags, $resultTags);
    }

    /**
     * Test delete.
     *
     * @throws OptimisticLockException|ORMException
     */
    public function testDelete(): void
    {
        // given
        $categoryToDelete = new Tags();
        $categoryToDelete->setTitle('Test Tags');
        $this->entityManager->persist($categoryToDelete);
        $this->entityManager->flush();
        $deletedTagsId = $categoryToDelete->getId();

        // when
        $this->categoryService->delete($categoryToDelete);

        // then
        $resultTags = $this->entityManager->createQueryBuilder()
            ->select('category')
            ->from(Tags::class, 'category')
            ->where('category.id = :id')
            ->setParameter(':id', $deletedTagsId, Types::INTEGER)
            ->getQuery()
            ->getOneOrNullResult();

        $this->assertNull($resultTags);
    }

    /**
     * Test find by id.
     *
     * @throws ORMException
     */

    /**
    public function testFindById(): void
    {
        // given
        $expectedTags = new Tags();
        $expectedTags->setTitle('Test Tags');
        $this->entityManager->persist($expectedTags);
        $this->entityManager->flush();
        $expectedTagsId = $expectedTags->getId();

        // when
        $resultTags = $this->categoryService->findOneById($expectedTagsId);

        // then
        $this->assertEquals($expectedTags, $resultTags);
    }
    */

    /**
     * Test pagination empty list.
     */
    public function testGetPaginatedList(): void
    {
        // given
        $page = 1;
        $dataSetSize = 3;
        $expectedResultSize = 3;

        $counter = 0;
        while ($counter < $dataSetSize) {
            $category = new Tags();
            $category->setTitle('Test Tags #'.$counter);
            $this->categoryService->save($category);

            ++$counter;
        }

        // when
        $result = $this->categoryService->getPaginatedList($page);

        // then
        $this->assertEquals($expectedResultSize, $result->count());
    }

    // other tests for paginated list
}
