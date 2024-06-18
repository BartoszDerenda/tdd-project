<?php
/**
 * Question service tests.
 */

namespace App\Tests\Service;

use App\Entity\Category;
use App\Entity\Question;
use App\Entity\User;
use App\Service\QuestionService;
use App\Service\QuestionServiceInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class QuestionServiceTest.
 */
class QuestionServiceTest extends KernelTestCase
{
    /**
     * Question repository.
     */
    private ?EntityManagerInterface $entityManager;

    /**
     * Question service.
     */
    private ?QuestionServiceInterface $questionService;

    /**
     * Set up test.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface|Exception
     */
    public function setUp(): void
    {
        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        $this->questionService = $container->get(QuestionService::class);
    }

    /**
     * Test save.
     *
     * @throws ORMException
     */
    public function testSave(): void
    {
        // given
        $category = new Category();
        $category->setTitle('test_category');
        $category->setSlug('test_category_slug');

        $user = new User();
        $user->setNickname('test_user');
        $user->setEmail('test@example.com');
        $user->setPassword('testowo');

        $this->entityManager->persist($user);
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $expectedQuestion = new Question();
        $expectedQuestion->setTitle('Test title');
        $expectedQuestion->setComment('Test comment');
        $expectedQuestion->setCreatedAt(new \DateTimeImmutable());
        $expectedQuestion->setUpdatedAt(new \DateTimeImmutable());
        $expectedQuestion->setImage('randomimg.png');
        $expectedQuestion->setAuthor($user);
        $expectedQuestion->setCategory($category);

        // when
        $this->questionService->save($expectedQuestion);

        // then
        $expectedQuestionId = $expectedQuestion->getId();
        $resultQuestion = $this->entityManager->createQueryBuilder()
            ->select('question')
            ->from(Question::class, 'question')
            ->where('question.id = :id')
            ->setParameter(':id', $expectedQuestionId, Types::INTEGER)
            ->getQuery()
            ->getSingleResult();

        $this->assertEquals($expectedQuestion, $resultQuestion);
    }

    /**
     * Test delete.
     *
     * @throws ORMException
     */
    public function testDelete(): void
    {
        // given
        $category = new Category();
        $category->setTitle('test_category');
        $category->setSlug('test_category_slug');

        $user = new User();
        $user->setNickname('test_user');
        $user->setEmail('test@example.com');
        $user->setPassword('testowo');

        $this->entityManager->persist($user);
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $questionToDelete = new Question();
        $questionToDelete->setTitle('Test title');
        $questionToDelete->setComment('Test comment');
        $questionToDelete->setAuthor($user);
        $questionToDelete->setCategory($category);

        $this->entityManager->persist($questionToDelete);
        $this->entityManager->flush();

        $deletedQuestionId = $questionToDelete->getId();

        // when
        $this->questionService->delete($questionToDelete);

        // then
        $resultQuestion = $this->entityManager->createQueryBuilder()
            ->select('question')
            ->from(Question::class, 'question')
            ->where('question.id = :id')
            ->setParameter(':id', $deletedQuestionId, Types::INTEGER)
            ->getQuery()
            ->getOneOrNullResult();

        $this->assertNull($resultQuestion);
    }

    /**
     * Test find by id.
     *
     * @return void
     */
    public function testFindById(): void
    {
        // given
        $category = new Category();
        $category->setTitle('test_category');
        $category->setSlug('test_category_slug');

        $user = new User();
        $user->setNickname('test_user');
        $user->setEmail('test@example.com');
        $user->setPassword('testowo');

        $this->entityManager->persist($user);
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $expectedQuestion = new Question();
        $expectedQuestion->setTitle('Test title');
        $expectedQuestion->setComment('Test comment');
        $expectedQuestion->setAuthor($user);
        $expectedQuestion->setCategory($category);

        $this->entityManager->persist($expectedQuestion);
        $this->entityManager->flush();

        $expectedQuestionId = $expectedQuestion->getId();

        // when
        $resultQuestion = $this->questionService->findOneById($expectedQuestionId);

        // then
        $this->assertEquals($expectedQuestion, $resultQuestion);
    }

    /**
     * Test pagination empty list.
     *
     * @return void
     */
    public function testGetPaginatedList(): void
    {
        // given
        $page = 1;
        $dataSetSize = 3;
        $expectedResultSize = 3;

        $category = new Category();
        $category->setTitle('test_category');
        $category->setSlug('test_category_slug');

        $user = new User();
        $user->setNickname('test_user');
        $user->setEmail('test@example.com');
        $user->setPassword('testowo');

        $this->entityManager->persist($user);
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $counter = 0;
        while ($counter < $dataSetSize) {
            $question = new Question();
            $question->setTitle('Test Question #'.$counter);
            $question->setComment('Test comment');
            $question->setAuthor($user);
            $question->setCategory($category);

            $this->questionService->save($question);

            ++$counter;
        }

        // when
        $result = $this->questionService->getPaginatedList($page);

        // then
        $this->assertEquals($expectedResultSize, $result->count());
    }

    /**
     * Test paginated list for category.
     *
     * @return void
     */
    public function testQueryByCategory(): void
    {
        // given
        $page = 1;
        $dataSetSize = 3;
        $expectedResultSize = 3;

        $category = new Category();
        $category->setTitle('test_category');
        $category->setSlug('test_category_slug');

        $user = new User();
        $user->setNickname('test_user');
        $user->setEmail('test@example.com');
        $user->setPassword('testowo');

        $this->entityManager->persist($user);
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $counter = 0;
        while ($counter < $dataSetSize) {
            $question = new Question();
            $question->setTitle('Test Question #'.$counter);
            $question->setComment('Test comment');
            $question->setAuthor($user);
            $question->setCategory($category);

            $this->questionService->save($question);

            ++$counter;
        }

        // when
        $result = $this->questionService->queryByCategory($page, $category);

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
