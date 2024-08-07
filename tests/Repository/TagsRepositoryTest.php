<?php

/**
 * Tags repository tests.
 */

namespace App\Tests\Repository;

use App\Entity\Tags;
use App\Repository\TagsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class TagsRepositoryTest.
 */
class TagsRepositoryTest extends KernelTestCase
{
    /**
     * Entity manager.
     */
    private ?EntityManagerInterface $entityManager;

    /**
     * Tags service.
     */
    private ?TagsRepository $tagsRepository;

    /**
     * Set up test.
     *
     * @throws \Exception
     */
    protected function setUp(): void
    {
        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        $this->tagsRepository = $container->get(TagsRepository::class);
    }

    /**
     * Test FindOneById.
     */
    public function testFindOneById(): void
    {
        // given
        $tags = new Tags();
        $tags->setTitle('test_tags');

        $this->entityManager->persist($tags);
        $this->entityManager->flush();

        // when
        $expectedTags = $this->tagsRepository->findOneById($tags->getId());

        $this->assertNotNull($expectedTags);
        $this->assertSame('test_tags', $expectedTags->getTitle());
    }

    /**
     * Test Add.
     */
    public function testAdd(): void
    {
        // given
        $tags = new Tags();
        $tags->setTitle('test_tags');

        $this->entityManager->persist($tags);
        $this->entityManager->flush();

        // when
        $this->tagsRepository->add($tags, true);

        // then
        $expectedTags = $this->tagsRepository->findOneById($tags->getId());
        $this->assertNotNull($expectedTags);
        $this->assertSame('test_tags', $expectedTags->getTitle());
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
