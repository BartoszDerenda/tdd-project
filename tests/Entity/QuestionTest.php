<?php

/**
 * Question entity tests.
 */

namespace App\Tests\Entity;

use App\Entity\Answer;
use App\Entity\Category;
use App\Entity\Question;
use App\Entity\Tags;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class QuestionRepositoryTest.
 */
class QuestionTest extends KernelTestCase
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
    public function testQuestionEntity(): void
    {
        // given
        $category = new Category();
        $category->setTitle('test_category');

        $tags = new Tags();
        $tags->setTitle('test_tag');

        $author = new User();
        $author->setNickname('test_user');
        $author->setEmail('test@example.com');
        $author->setPassword('testowo');

        $question = new Question();
        $question->setTitle('Test title');
        $question->setComment('Test comment');
        $question->setUpdatedAt(new \DateTimeImmutable());
        $question->setCreatedAt(new \DateTimeImmutable());
        $question->addTag($tags);
        $question->setAuthor($author);
        $question->setCategory($category);

        $answer = new Answer();
        $answer->setQuestion($question);
        $answer->setComment('Test answer');
        $answer->setAuthor($author);
        $answer->setCreatedAt(new \DateTimeImmutable());
        $answer->setUpdatedAt(new \DateTimeImmutable());
        $answer->setBestAnswer(false);

        $this->entityManager->persist($category);
        $this->entityManager->persist($tags);
        $this->entityManager->persist($author);
        $this->entityManager->persist($question);
        $this->entityManager->flush();

        // when
        $expectedQuestion = new Question();
        $expectedQuestion->setTitle($question->getTitle());
        $expectedQuestion->setComment($question->getComment());
        $expectedQuestion->setCreatedAt($question->getCreatedAt());
        $expectedQuestion->setUpdatedAt($question->getUpdatedAt());
        $expectedQuestion->setAuthor($question->getAuthor());
        $expectedQuestion->setCategory($question->getCategory());
        $expectedQuestion->addTag($tags);
        $expectedQuestion->removeTag($tags);

        $this->entityManager->persist($expectedQuestion);
        $this->entityManager->flush();

        // then
        $this->assertFalse($expectedQuestion->getId() === $question->getId()); // Ids are unique
        $this->assertSame($expectedQuestion->getTitle(), $question->getTitle());
        $this->assertSame($expectedQuestion->getComment(), $question->getComment());
        $this->assertSame($expectedQuestion->getCreatedAt(), $question->getCreatedAt());
        $this->assertSame($expectedQuestion->getUpdatedAt(), $question->getUpdatedAt());
        $this->assertSame($expectedQuestion->getAuthor(), $question->getAuthor());
        $this->assertSame($expectedQuestion->getCategory(), $question->getCategory());
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
