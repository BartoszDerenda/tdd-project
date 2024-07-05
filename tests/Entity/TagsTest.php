<?php

/**
 * Tags entity tests.
 */

namespace App\Tests\Entity;

use App\Entity\Tags;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class CategoryEntityTest.
 */
class TagsTest extends KernelTestCase
{
    /**
     * Entity manager.
     */
    private ?EntityManagerInterface $entityManager;

    /**
     * Set up test.
     *
     * @throws \Exception
     */
    protected function setUp(): void
    {
        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
    }

    /**
     * Test Entity.
     */
    public function testTagsEntity(): void
    {
        // given
        $tags = new Tags();
        $tags->setTitle('test_category');
        $tags->setSlug('test_category_slug');
        $tags->setCreatedAt(new \DateTimeImmutable());
        $tags->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($tags);
        $this->entityManager->flush();

        // when
        $expectedTags = new Tags();
        $expectedTags->setTitle($tags->getTitle());
        $expectedTags->setSlug($tags->getSlug());
        $expectedTags->setCreatedAt($tags->getCreatedAt());
        $expectedTags->setUpdatedAt($tags->getUpdatedAt());

        $this->entityManager->persist($expectedTags);
        $this->entityManager->flush();

        // then
        $this->assertFalse($expectedTags->getId() === $tags->getId()); // Ids are unique
        $this->assertSame($expectedTags->getTitle(), $tags->getTitle());
        $this->assertFalse($expectedTags->getSlug() === $tags->getSlug()); // Slugs are unique
        $this->assertSame($expectedTags->getCreatedAt(), $tags->getCreatedAt());
        $this->assertSame($expectedTags->getUpdatedAt(), $tags->getUpdatedAt());
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
