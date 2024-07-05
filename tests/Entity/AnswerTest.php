<?php

/**
 * Answer entity tests.
 */

namespace App\Tests\Entity;

use App\Entity\Answer;
use App\Entity\Category;
use App\Entity\Question;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class AnswerEntityTest.
 */
class AnswerTest extends KernelTestCase
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
    public function testAnswerEntity(): void
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
        $expectedAnswer = new Answer();
        $expectedAnswer->setQuestion($answer->getQuestion());
        $expectedAnswer->setComment($answer->getComment());
        $expectedAnswer->setAuthor($answer->getAuthor());
        $expectedAnswer->setCreatedAt($answer->getCreatedAt());
        $expectedAnswer->setUpdatedAt($answer->getUpdatedAt());
        $expectedAnswer->setBestAnswer($answer->isBestAnswer());
        $this->entityManager->persist($expectedAnswer);
        $this->entityManager->flush();

        // then
        $this->assertFalse($expectedAnswer->getId() === $answer->getId()); // Ids are unique
        $this->assertSame($expectedAnswer->getQuestion(), $answer->getQuestion());
        $this->assertSame($expectedAnswer->getComment(), $answer->getComment());
        $this->assertSame($expectedAnswer->getAuthor(), $answer->getAuthor());
        $this->assertSame($expectedAnswer->getCreatedAt(), $answer->getCreatedAt());
        $this->assertSame($expectedAnswer->getUpdatedAt(), $answer->getUpdatedAt());
        $this->assertSame($expectedAnswer->isBestAnswer(), $answer->isBestAnswer());
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
