<?php

/**
 * Question repository tests.
 */

namespace App\Tests\Repository;

use App\Entity\Category;
use App\Entity\Question;
use App\Entity\User;
use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class QuestionRepositoryTest.
 */
class QuestionRepositoryTest extends KernelTestCase
{
    /**
     * Entity manager.
     */
    private ?EntityManagerInterface $entityManager;

    /**
     * Question service.
     */
    private ?QuestionRepository $questionRepository;

    /**
     * Set up test.
     *
     * @throws \Exception
     */
    protected function setUp(): void
    {
        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        $this->questionRepository = $container->get(QuestionRepository::class);
    }

    /**
     * Test findOneById.
     */
    public function testFindOneById(): void
    {
        // given
        $category = new Category();
        $category->setTitle('test_category');

        $author = new User();
        $author->setNickname('test_user');
        $author->setEmail('test@example.com');
        $author->setPassword('testowo');

        $question = new Question();
        $question->setTitle('Test title');
        $question->setComment('Test comment');
        $question->setAuthor($author);
        $question->setCategory($category);

        $this->entityManager->persist($author);
        $this->entityManager->persist($category);
        $this->entityManager->persist($question);
        $this->entityManager->flush();

        // when
        $expectedQuestion = $this->questionRepository->findOneById($question->getId());

        // then
        $this->assertNotNull($expectedQuestion);
        $this->assertSame('Test title', $expectedQuestion->getTitle());
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
