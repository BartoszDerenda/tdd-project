<?php
/**
 * Answer service tests.
 */

namespace App\Tests\Service;

use App\Entity\Answer;
use App\Entity\Category;
use App\Entity\Question;
use App\Entity\User;
use App\Service\AnswerService;
use App\Service\AnswerServiceInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class AnswerServiceTest.
 */
class AnswerServiceTest extends KernelTestCase
{
    /**
     * Entity manager.
     */
    private ?EntityManagerInterface $entityManager;

    /**
     * Answer service.
     */
    private ?AnswerServiceInterface $answerService;

    /**
     * Set up test.
     *
     * @throws Exception
     */
    public function setUp(): void
    {
        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        $this->answerService = $container->get(AnswerService::class);
    }

    /**
     * Test save.
     *
     * @throws ORMException
     * @throws Exception
     */
    public function testSave(): void
    {
        //setup
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

        // given
        $expectedAnswer = new Answer();
        $expectedAnswer->setQuestion($question);
        $expectedAnswer->setComment('Test answer');
        $expectedAnswer->setAuthor($user);
        $expectedAnswer->setCreatedAt(new \DateTimeImmutable());
        $expectedAnswer->setUpdatedAt(new \DateTimeImmutable());
        $expectedAnswer->setBestAnswer(True);

        // when
        $this->answerService->save($expectedAnswer);

        // then
        $expectedAnswerId = $expectedAnswer->getId();
        $resultAnswer = $this->entityManager->createQueryBuilder()
            ->select('answer')
            ->from(Answer::class, 'answer')
            ->where('answer.id = :id')
            ->setParameter(':id', $expectedAnswerId, Types::INTEGER)
            ->getQuery()
            ->getSingleResult();

        $this->assertEquals($expectedAnswer, $resultAnswer);
    }

    /**
     * Test delete.
     *
     * @throws ORMException
     */
    public function testDelete(): void
    {
        //setup
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

        // given
        $answerToDelete = new Answer();
        $answerToDelete->setQuestion($question);
        $answerToDelete->setComment('Test answer');
        $answerToDelete->setAuthor($user);
        $answerToDelete->setCreatedAt(new \DateTimeImmutable());
        $answerToDelete->setUpdatedAt(new \DateTimeImmutable());
        $answerToDelete->setBestAnswer(True);

        $this->entityManager->persist($answerToDelete);
        $this->entityManager->flush();

        $deletedAnswerId = $answerToDelete->getId();

        // when
        $this->answerService->delete($answerToDelete);

        // then
        $resultAnswer = $this->entityManager->createQueryBuilder()
            ->select('answer')
            ->from(Answer::class, 'answer')
            ->where('answer.id = :id')
            ->setParameter(':id', $deletedAnswerId, Types::INTEGER)
            ->getQuery()
            ->getOneOrNullResult();

        $this->assertNull($resultAnswer);
    }

    /**
     * Test find by id.
     *
     */
    public function testFindById(): void
    {
        //setup
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

        // given
        $expectedAnswer = new Answer();
        $expectedAnswer->setQuestion($question);
        $expectedAnswer->setComment('Test answer');
        $expectedAnswer->setAuthor($user);
        $expectedAnswer->setCreatedAt(new \DateTimeImmutable());
        $expectedAnswer->setUpdatedAt(new \DateTimeImmutable());
        $expectedAnswer->setBestAnswer(True);

        $this->entityManager->persist($expectedAnswer);
        $this->entityManager->flush();

        $expectedAnswerId = $expectedAnswer->getId();

        // when
        $resultAnswer = $this->answerService->findOneById($expectedAnswerId);

        // then
        $this->assertEquals($expectedAnswer, $resultAnswer);
    }

    /**
     * Test pagination empty list.
     */
    public function testGetPaginatedList(): void
    {
        //setup
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

        // given
        $page = 1;
        $dataSetSize = 3;
        $expectedResultSize = 3;

        $counter = 0;
        while ($counter < $dataSetSize) {

            $answer = new Answer();
            $answer->setQuestion($question);
            $answer->setComment('Test answer#'.$counter);
            $answer->setAuthor($user);
            $answer->setCreatedAt(new \DateTimeImmutable());
            $answer->setUpdatedAt(new \DateTimeImmutable());
            $answer->setBestAnswer(True);

            $this->answerService->save($answer);

            ++$counter;
        }

        // when
        $result = $this->answerService->getPaginatedList($page, $question);

        // then
        $this->assertEquals($expectedResultSize, $result->count());
    }

}