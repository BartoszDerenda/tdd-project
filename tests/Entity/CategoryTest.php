<?php

/**
 * Category entity tests.
 */

namespace App\Tests\Entity;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class CategoryEntityTest.
 */
class CategoryTest extends KernelTestCase
{
    /**
     * Entity manager.
     */
    private ?EntityManagerInterface $entityManager;

    /**
     * Set up test.
     *
     * @throws Exception
     */
    protected function setUp(): void
    {
        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
    }

    /**
     * Test Entity.
     */
    public function testCategoryEntity(): void
    {
        // given
        $category = new Category();
        $category->setTitle('test_category');
        $category->setSlug('test_category_slug');
        $category->setCreatedAt(new \DateTimeImmutable());
        $category->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        // when
        $expectedCategory = new Category();
        $expectedCategory->setTitle($category->getTitle());
        $expectedCategory->setSlug($category->getSlug());
        $expectedCategory->setCreatedAt($category->getCreatedAt());
        $expectedCategory->setUpdatedAt($category->getUpdatedAt());

        $this->entityManager->persist($expectedCategory);
        $this->entityManager->flush();

        // then
        $this->assertFalse($expectedCategory->getId() === $category->getId()); // Ids are unique
        $this->assertSame($expectedCategory->getTitle(), $category->getTitle());
        $this->assertFalse($expectedCategory->getSlug() === $category->getSlug()); // Slugs are unique
        $this->assertSame($expectedCategory->getCreatedAt(), $category->getCreatedAt());
        $this->assertSame($expectedCategory->getUpdatedAt(), $category->getUpdatedAt());
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
