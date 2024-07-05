<?php

/**
 * Category repository tests.
 */

namespace App\Tests\Repository;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class CategoryRepositoryTest.
 */
class CategoryRepositoryTest extends KernelTestCase
{
    /**
     * Entity manager.
     */
    private ?EntityManagerInterface $entityManager;

    /**
     * Category service.
     */
    private ?CategoryRepository $categoryRepository;

    /**
     * Set up test.
     *
     * @throws \Exception
     */
    protected function setUp(): void
    {
        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        $this->categoryRepository = $container->get(CategoryRepository::class);
    }

    /**
     * Test FindOneById.
     */
    public function testFindOneById(): void
    {
        // given
        $category = new Category();
        $category->setTitle('test_category');
        $category->setSlug('test_category_slug');

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        // when
        $expectedCategory = $this->categoryRepository->findOneById($category->getId());

        $this->assertNotNull($expectedCategory);
        $this->assertSame('test_category', $expectedCategory->getTitle());
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
