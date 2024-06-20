<?php
/**
 * Answer controller tests.
 */

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\Enum\UserRole;
use App\Entity\Answer;
use App\Entity\Question;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * class AnswerControllerTest.
 */
class AnswerControllerTest extends WebTestCase
{
    /**
     * Test route.
     *
     * @const string
     */
    public const TEST_ROUTE = '/answer';

    /**
     * Test client.
     */
    private KernelBrowser $httpClient;

    /**
     * Translator.
     */
    private TranslatorInterface $translator;

    /**
     * Entity manager.
     */
    private ?EntityManagerInterface $entityManager;

    /**
     * Set up tests.
     *
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->httpClient = static::createClient();
        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        $this->translator = $container->get(TranslatorInterface::class);
    }

    /**
     * Test '/answer/{question_id}' route for non-authorized user.
     * This route is available for unauthorized users, authorized users and admins.
     *
     * @return void
     */
    public function testAnswerCreateRouteNonAuthorizedUser(): void
    {
        // Setup
        $category = new Category();
        $category->setTitle('test_category');

        $questionAuthor = new User();
        $questionAuthor->setNickname('test_user');
        $questionAuthor->setEmail('test@example.com');
        $questionAuthor->setPassword('testowo');

        $question = new Question();
        $question->setTitle('Test title');
        $question->setComment('Test comment');
        $question->setAuthor($questionAuthor);
        $question->setCategory($category);

        $this->entityManager->persist($questionAuthor);
        $this->entityManager->persist($question);
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $questionId = $question->getId();

        // When
        $this->httpClient->request('GET', self::TEST_ROUTE . '/' . $questionId);
        $resultHttpStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // Then
        $this->assertEquals(200, $resultHttpStatusCode);
    }

    /**
     * Test '/answer/{answer_id}/delete' route for the author of the answer.
     * This route is available for authors and admins.
     *
     * @return void
     * @throws Exception
     */
    public function testAnswerDeleteRouteAuthor(): void
    {
        // Setup
        $category = new Category();
        $category->setTitle('test_category');

        $questionAuthor = new User();
        $questionAuthor->setNickname('test_user');
        $questionAuthor->setEmail('test@example.com');
        $questionAuthor->setPassword('testowo');

        $answerAuthor = $this->createUser([UserRole::ROLE_USER->value]);

        $question = new Question();
        $question->setTitle('Test title');
        $question->setComment('Test comment');
        $question->setAuthor($questionAuthor);
        $question->setCategory($category);

        $answer = new Answer();
        $answer->setComment('Test comment');
        $answer->setAuthor($answerAuthor);
        $answer->setQuestion($question);

        $this->entityManager->persist($category);
        $this->entityManager->persist($questionAuthor);
        $this->entityManager->persist($question);
        $this->entityManager->persist($answer);
        $this->entityManager->flush();

        $answerId = $answer->getId();
        $this->httpClient->loginUser($answerAuthor);

        // When
        $this->httpClient->request('GET', self::TEST_ROUTE . '/' . $answerId . '/delete');
        $resultHttpStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // Then
        $this->assertEquals(200, $resultHttpStatusCode);
    }

    /**
     * Test '/answer/{answer_id}/delete' route for the admin.
     * This route is available for authors and admins.
     *
     * @return void
     * @throws Exception
     */
    public function testAnswerDeleteRouteAdmin(): void
    {
        // Setup
        $category = new Category();
        $category->setTitle('test_category');

        $author = new User();
        $author->setNickname('test_user');
        $author->setEmail('test@example.com');
        $author->setPassword('testowo');

        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value]);

        $question = new Question();
        $question->setTitle('Test title');
        $question->setComment('Test comment');
        $question->setAuthor($author);
        $question->setCategory($category);

        $answer = new Answer();
        $answer->setComment('Test comment');
        $answer->setAuthor($author);
        $answer->setQuestion($question);

        $this->entityManager->persist($category);
        $this->entityManager->persist($author);
        $this->entityManager->persist($question);
        $this->entityManager->persist($answer);
        $this->entityManager->flush();

        $answerId = $answer->getId();
        $this->httpClient->loginUser($adminUser);

        // When
        $this->httpClient->request('GET', self::TEST_ROUTE . '/' . $answerId . '/delete');
        $resultHttpStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // Then
        $this->assertEquals(200, $resultHttpStatusCode);
    }

    /**
     * Test '/answer/{answer_id}/edit' route for the author.
     * This route is available for the author.
     *
     * @return void
     * @throws Exception
     */
    public function testAnswerEditRouteAdmin(): void
    {
        // Setup
        $category = new Category();
        $category->setTitle('test_category');

        $author = new User();
        $author->setNickname('test_user');
        $author->setEmail('test@example.com');
        $author->setPassword('testowo');

        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value]);

        $question = new Question();
        $question->setTitle('Test title');
        $question->setComment('Test comment');
        $question->setAuthor($author);
        $question->setCategory($category);

        $answer = new Answer();
        $answer->setComment('Test comment');
        $answer->setAuthor($author);
        $answer->setQuestion($question);

        $this->entityManager->persist($category);
        $this->entityManager->persist($author);
        $this->entityManager->persist($question);
        $this->entityManager->persist($answer);
        $this->entityManager->flush();

        $answerId = $answer->getId();
        $this->httpClient->loginUser($adminUser);

        // When
        $this->httpClient->request('GET', self::TEST_ROUTE . '/' . $answerId . '/edit');
        $resultHttpStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // Then
        $this->assertEquals(403, $resultHttpStatusCode);
    }

    /**
     * Test '/answer/{answer_id}/edit' route for the admin.
     * This route is available for the author.
     *
     * @return void
     * @throws Exception
     */
    public function testAnswerEditRouteAuthor(): void
    {
        // Setup
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

        $answer = new Answer();
        $answer->setComment('Test comment');
        $answer->setAuthor($author);
        $answer->setQuestion($question);

        $this->entityManager->persist($category);
        $this->entityManager->persist($author);
        $this->entityManager->persist($question);
        $this->entityManager->persist($answer);
        $this->entityManager->flush();

        $answerId = $answer->getId();
        $this->httpClient->loginUser($author);

        // When
        $this->httpClient->request('GET', self::TEST_ROUTE . '/' . $answerId . '/edit');
        $resultHttpStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // Then
        $this->assertEquals(200, $resultHttpStatusCode);
    }

    /**
     * Test '/answer/{answer_id}/edit' route for the unauthorized user.
     * This route is available for the author.
     *
     * @return void
     * @throws Exception
     */
    public function testAnswerEditRouteNonAuthorizedUser(): void
    {
        // Setup
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

        $answer = new Answer();
        $answer->setComment('Test comment');
        $answer->setAuthor($author);
        $answer->setQuestion($question);

        $this->entityManager->persist($category);
        $this->entityManager->persist($author);
        $this->entityManager->persist($question);
        $this->entityManager->persist($answer);
        $this->entityManager->flush();

        $answerId = $answer->getId();

        // When
        $this->httpClient->request('GET', self::TEST_ROUTE . '/' . $answerId . '/edit');
        $resultHttpStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // Then
        $this->assertEquals(302, $resultHttpStatusCode);
    }

    /**
     * Test '/answer/{answer_id}/mark' route for the admin.
     * This route is available for question authors and admins.
     *
     * @return void
     * @throws Exception
     */
    public function testAnswerMarkRouteAdmin(): void
    {
        // Setup
        $category = new Category();
        $category->setTitle('test_category');

        $author = new User();
        $author->setNickname('test_user');
        $author->setEmail('test@example.com');
        $author->setPassword('testowo');

        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value]);

        $question = new Question();
        $question->setTitle('Test title');
        $question->setComment('Test comment');
        $question->setAuthor($author);
        $question->setCategory($category);

        $answer = new Answer();
        $answer->setComment('Test comment');
        $answer->setAuthor($author);
        $answer->setQuestion($question);

        $this->entityManager->persist($category);
        $this->entityManager->persist($author);
        $this->entityManager->persist($question);
        $this->entityManager->persist($answer);
        $this->entityManager->flush();

        $answerId = $answer->getId();
        $this->httpClient->loginUser($adminUser);

        // When
        $this->httpClient->request('GET', self::TEST_ROUTE . '/' . $answerId . '/mark');
        $resultHttpStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // Then
        $this->assertEquals(200, $resultHttpStatusCode);
    }

    /**
     * Test '/answer/{answer_id}/mark' route for the admin.
     * This route is available for question authors and admins.
     *
     * @return void
     * @throws Exception
     */
    public function testAnswerMarkRouteQuestionAuthor(): void
    {
        // Setup
        $category = new Category();
        $category->setTitle('test_category');

        $questionAuthor = new User();
        $questionAuthor->setNickname('test_user');
        $questionAuthor->setEmail('test@example.com');
        $questionAuthor->setPassword('testowo');

        $answerAuthor = $this->createUser([UserRole::ROLE_USER->value]);

        $question = new Question();
        $question->setTitle('Test title');
        $question->setComment('Test comment');
        $question->setAuthor($questionAuthor);
        $question->setCategory($category);

        $answer = new Answer();
        $answer->setComment('Test comment');
        $answer->setAuthor($answerAuthor);
        $answer->setQuestion($question);

        $this->entityManager->persist($category);
        $this->entityManager->persist($questionAuthor);
        $this->entityManager->persist($question);
        $this->entityManager->persist($answer);
        $this->entityManager->flush();

        $answerId = $answer->getId();
        $this->httpClient->loginUser($questionAuthor);

        // When
        $this->httpClient->request('GET', self::TEST_ROUTE . '/' . $answerId . '/mark');
        $resultHttpStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // Then
        $this->assertEquals(200, $resultHttpStatusCode);
    }

    /**
     * Create user.
     *
     * @param array $roles User roles
     *
     * @return User User entity
     * @throws Exception
     */
    private function createUser(array $roles): User
    {
        $passwordHasher = static::getContainer()->get('security.password_hasher');
        $user = new User();
        $user->setNickname('user');
        $user->setEmail('user@example.com');
        $user->setRoles($roles);
        $user->setPassword(
            $passwordHasher->hashPassword(
                $user,
                'p@55w0rd'
            )
        );
        $userRepository = static::getContainer()->get(UserRepository::class);
        $userRepository->save($user);

        return $user;
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
