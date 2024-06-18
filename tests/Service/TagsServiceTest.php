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
use Doctrine\ORM\ORMException;
use Exception;
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
    private ?TagsServiceInterface $tagsService;

    /**
     * Set up test.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface|Exception
     */
    public function setUp(): void
    {
        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        $this->tagsService = $container->get(TagsService::class);
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
        $expectedTags->setSlug('test_slug');
        $expectedTags->setCreatedAt(new \DateTimeImmutable());
        $expectedTags->setUpdatedAt(new \DateTimeImmutable());

        // when
        $this->tagsService->save($expectedTags);

        // then
        $expectedTagsId = $expectedTags->getId();
        $resultTags = $this->entityManager->createQueryBuilder()
            ->select('tags')
            ->from(Tags::class, 'tags')
            ->where('tags.id = :id')
            ->setParameter(':id', $expectedTagsId, Types::INTEGER)
            ->getQuery()
            ->getSingleResult();

        $this->assertEquals($expectedTags, $resultTags);
    }

    /**
     * Test delete.
     *
     * @throws ORMException
     */
    public function testDelete(): void
    {
        // given
        $tagsToDelete = new Tags();
        $tagsToDelete->setTitle('Test Tags');
        $this->entityManager->persist($tagsToDelete);
        $this->entityManager->flush();
        $deletedTagsId = $tagsToDelete->getId();

        // when
        $this->tagsService->delete($tagsToDelete);

        // then
        $resultTags = $this->entityManager->createQueryBuilder()
            ->select('tags')
            ->from(Tags::class, 'tags')
            ->where('tags.id = :id')
            ->setParameter(':id', $deletedTagsId, Types::INTEGER)
            ->getQuery()
            ->getOneOrNullResult();

        $this->assertNull($resultTags);
    }

    /**
     * Test find by id.
     *
     * @return void
     */
    public function testFindById(): void
    {
        // given
        $expectedTags = new Tags();
        $expectedTags->setTitle('Test Tags');
        $this->entityManager->persist($expectedTags);
        $this->entityManager->flush();
        $expectedTagsId = $expectedTags->getId();

        // when
        $resultTags = $this->tagsService->findOneById($expectedTagsId);

        // then
        $this->assertEquals($expectedTags, $resultTags);
    }

    /**
     * Test find one by title.
     *
     * @return void
     */
    public function testFindByTitle(): void
    {
        // given
        $expectedTags = new Tags();
        $expectedTags->setTitle('Test Tags');
        $this->entityManager->persist($expectedTags);
        $this->entityManager->flush();
        $expectedTagsTitle = $expectedTags->getTitle();

        // when
        $resultTags = $this->tagsService->findOneByTitle($expectedTagsTitle);

        // then
        $this->assertEquals($expectedTags, $resultTags);
    }

    /**
     * Test pagination empty list.
     *
     * @return void
     */
    public function testGetPaginatedList(): void
    {
        // given
        $page = 1;
        $dataSetSize = 3;
        $expectedResultSize = 3;

        $counter = 0;
        while ($counter < $dataSetSize) {
            $tags = new Tags();
            $tags->setTitle('Test Tags #'.$counter);
            $this->tagsService->save($tags);

            ++$counter;
        }

        // when
        $result = $this->tagsService->getPaginatedList($page);

        // then
        $this->assertEquals($expectedResultSize, $result->count());
    }

    /**
     * Reset the environment.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        // Clear the entity manager to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }

}
