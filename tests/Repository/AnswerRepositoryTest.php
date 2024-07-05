<?php

/**
 * Answer repository tests.
 */

namespace App\Tests\Repository;

use App\Entity\Answer;
use App\Entity\Category;
use App\Entity\Question;
use App\Entity\User;
use App\Repository\AnswerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class AnswerRepositoryTest.
 */
class AnswerRepositoryTest extends KernelTestCase
{
    /**
     * Entity manager.
     */
    private ?EntityManagerInterface $entityManager;

    /**
     * Answer service.
     */
    private ?AnswerRepository $answerRepository;

    /**
     * Set up test.
     *
     * @throws \Exception
     */
    protected function setUp(): void
    {
        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        $this->answerRepository = $container->get(AnswerRepository::class);
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

        $user = new User();
        $user->setNickname('test_user');
        $user->setEmail('test@example.com');
        $user->setPassword('testowo');

        $question = new Question();
        $question->setTitle('Test title');
        $question->setComment('Test comment');
        $question->setAuthor($user);
        $question->setCategory($category);

        $this->entityManager->persist($user);
        $this->entityManager->persist($question);
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $answer = new Answer();
        $answer->setQuestion($question);
        $answer->setComment('Test answer');
        $answer->setAuthor($user);
        $answer->setCreatedAt(new \DateTimeImmutable());
        $answer->setUpdatedAt(new \DateTimeImmutable());
        $answer->setBestAnswer(true);

        $this->entityManager->persist($answer);
        $this->entityManager->flush();

        // when
        $expectedAnswer = $this->answerRepository->findOneById($answer->getId());

        $this->assertNotNull($expectedAnswer);
        $this->assertSame('Test answer', $expectedAnswer->getComment());
    }

    /**
     * Test Award.
     */
    public function testAward(): void
    {
        // given
        $category = new Category();
        $category->setTitle('test_category');
        $category->setSlug('test_category_slug');

        $user = new User();
        $user->setNickname('test_user');
        $user->setEmail('test@example.com');
        $user->setPassword('testowo');

        $question = new Question();
        $question->setTitle('Test title');
        $question->setComment('Test comment');
        $question->setAuthor($user);
        $question->setCategory($category);

        $this->entityManager->persist($user);
        $this->entityManager->persist($question);
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $answer = new Answer();
        $answer->setQuestion($question);
        $answer->setComment('Test answer');
        $answer->setAuthor($user);
        $answer->setCreatedAt(new \DateTimeImmutable());
        $answer->setUpdatedAt(new \DateTimeImmutable());
        $answer->setBestAnswer(false); // Answer flagged as not the best one

        $this->entityManager->persist($answer);
        $this->entityManager->flush();

        // when
        $this->answerRepository->award($answer);

        // then
        $this->assertTrue(true, $answer->isBestAnswer());
    }

    /**
     * Test Deaward.
     */
    public function testDeaward(): void
    {
        // given
        $category = new Category();
        $category->setTitle('test_category');
        $category->setSlug('test_category_slug');

        $user = new User();
        $user->setNickname('test_user');
        $user->setEmail('test@example.com');
        $user->setPassword('testowo');

        $question = new Question();
        $question->setTitle('Test title');
        $question->setComment('Test comment');
        $question->setAuthor($user);
        $question->setCategory($category);

        $this->entityManager->persist($user);
        $this->entityManager->persist($question);
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $answer = new Answer();
        $answer->setQuestion($question);
        $answer->setComment('Test answer');
        $answer->setAuthor($user);
        $answer->setCreatedAt(new \DateTimeImmutable());
        $answer->setUpdatedAt(new \DateTimeImmutable());
        $answer->setBestAnswer(true); // Answer flagged as (one of) the best one

        $this->entityManager->persist($answer);
        $this->entityManager->flush();

        // when
        $this->answerRepository->deaward($answer);

        // then
        $this->assertFalse(false, $answer->isBestAnswer());
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
