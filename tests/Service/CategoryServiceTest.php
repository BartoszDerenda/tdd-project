<?php

/**
 * Category service tests.
 */

namespace App\Tests\Service;

use App\Entity\Category;
use App\Entity\Question;
use App\Entity\User;
use App\Service\CategoryService;
use App\Service\CategoryServiceInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class CategoryServiceTest.
 */
class CategoryServiceTest extends KernelTestCase
{
    /**
     * Category repository.
     */
    private ?EntityManagerInterface $entityManager;

    /**
     * Category service.
     */
    private ?CategoryServiceInterface $categoryService;

    /**
     * Set up test.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface|\Exception
     */
    public function setUp(): void
    {
        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        $this->categoryService = $container->get(CategoryService::class);
    }

    /**
     * Test save.
     *
     * @throws ORMException
     */
    public function testSave(): void
    {
        // given
        $expectedCategory = new Category();
        $expectedCategory->setTitle('Test Category');
        $expectedCategory->setSlug('test_slug');
        $expectedCategory->setCreatedAt(new \DateTimeImmutable());
        $expectedCategory->setUpdatedAt(new \DateTimeImmutable());

        // when
        $this->categoryService->save($expectedCategory);

        // then
        $expectedCategoryId = $expectedCategory->getId();
        $resultCategory = $this->entityManager->createQueryBuilder()
            ->select('category')
            ->from(Category::class, 'category')
            ->where('category.id = :id')
            ->setParameter(':id', $expectedCategoryId, Types::INTEGER)
            ->getQuery()
            ->getSingleResult();

        $this->assertEquals($expectedCategory, $resultCategory);
    }

    /**
     * Test delete.
     *
     * @throws ORMException
     */
    public function testDelete(): void
    {
        // given
        $categoryToDelete = new Category();
        $categoryToDelete->setTitle('Test Category');
        $this->entityManager->persist($categoryToDelete);
        $this->entityManager->flush();
        $deletedCategoryId = $categoryToDelete->getId();

        // when
        $this->categoryService->delete($categoryToDelete);

        // then
        $resultCategory = $this->entityManager->createQueryBuilder()
            ->select('category')
            ->from(Category::class, 'category')
            ->where('category.id = :id')
            ->setParameter(':id', $deletedCategoryId, Types::INTEGER)
            ->getQuery()
            ->getOneOrNullResult();

        $this->assertNull($resultCategory);
    }

    /**
     * Test can Category be deleted? (False).
     */
    public function testCanBeDeleted(): void
    {
        // given
        $category = new Category();
        $category->setTitle('Test Category');

        $user = new User();
        $user->setNickname('test_user');
        $user->setEmail('test@example.com');
        $user->setPassword('testowo');

        $question = new Question();
        $question->setTitle('Test title');
        $question->setComment('Test comment');
        $question->setAuthor($user);
        $question->setCategory($category);

        $this->entityManager->persist($category);
        $this->entityManager->persist($user);
        $this->entityManager->persist($question);
        $this->entityManager->flush();

        // when
        $expectedFalse = $this->categoryService->canBeDeleted($category);

        // then
        $this->assertFalse(false, $expectedFalse);
    }

    /**
     * Test find by id.
     */
    public function testFindById(): void
    {
        // given
        $expectedCategory = new Category();
        $expectedCategory->setTitle('Test Category');
        $this->entityManager->persist($expectedCategory);
        $this->entityManager->flush();
        $expectedCategoryId = $expectedCategory->getId();

        // when
        $resultCategory = $this->categoryService->findOneById($expectedCategoryId);

        // then
        $this->assertEquals($expectedCategory, $resultCategory);
    }

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
            $category = new Category();
            $category->setTitle('Test Category #'.$counter);
            $this->categoryService->save($category);

            ++$counter;
        }

        // when
        $result = $this->categoryService->getPaginatedList($page);

        // then
        $this->assertEquals($expectedResultSize, $result->count());
    }

    /**
     * Reset the environment.
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        // Clear the entity manager to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
