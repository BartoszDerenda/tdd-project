<?php
/**
 * Question controller tests.
 */

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\Enum\UserRole;
use App\Entity\Question;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * class QuestionControllerTest.
 */
class QuestionControllerTest extends WebTestCase
{
    /**
     * Test route.
     *
     * @const string
     */
    public const TEST_ROUTE = '/question';

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
     * @throws \Exception
     */
    public function setUp(): void
    {
        $this->httpClient = static::createClient();
        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        $this->translator = $container->get(TranslatorInterface::class);
    }

    /**
     * Test '/question/{question_id}' route for non-authorized user.
     * This route is available for non-authorized users, authorized users, admins.
     */
    public function testQuestionShowRouteNonAuthorizedUser(): void
    {
        // Setup
        $category = new Category();
        $category->setTitle('test_category');

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
        $this->entityManager->persist($category);
        $this->entityManager->persist($question);
        $this->entityManager->flush();

        $questionId = $question->getId();

        // When
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$questionId);
        $resultHttpStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // Then
        $this->assertEquals(200, $resultHttpStatusCode);
    }

    /**
     * Test '/question/category/{category_id}' route for non-authorized user.
     * This route is available for non-authorized users, authorized users, admins.
     */
    public function testQuestionShowByCategoryNonAuthorizedUser(): void
    {
        // Setup
        $category = new Category();
        $category->setTitle('test_category');

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
        $this->entityManager->persist($category);
        $this->entityManager->persist($question);
        $this->entityManager->flush();

        $categoryId = $category->getId();

        // When
        $this->httpClient->request('GET', self::TEST_ROUTE.'/category/'.$categoryId);
        $resultHttpStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // Then
        $this->assertEquals(200, $resultHttpStatusCode);
    }

    /**
     * Test '/question/create' route for non-authorized user.
     * This route is available for non-authorized users, authorized users, admins.
     */
    public function testQuestionCreateRouteNonAuthorizedUser(): void
    {
        // When
        $this->httpClient->request('GET', self::TEST_ROUTE.'/create');
        $resultHttpStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // Then
        $this->assertEquals(200, $resultHttpStatusCode);
    }

    /**
     * Test the response if creation of a question was successful.
     * This route is available for unauthorized users, authorized users and admins.
     *
     * @throws \Exception
     */
    public function testQuestionCreateResponseSuccess(): void
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

        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $this->httpClient->loginUser($adminUser);

        $crawler = $this->httpClient->request('GET', self::TEST_ROUTE.'/create');

        $saveButton = $this->translator->trans('action.save');
        $form = $crawler->selectButton($saveButton)->form();
        $form['question[title]'] = 'Test Title';
        $form['question[comment]'] = 'Test Comment';
        $form['question[category]'] = $category->getId();
        $form['question[tags]'] = 'test_tags';

        // When
        $this->httpClient->submit($form);
        $response = $this->httpClient->getResponse();

        // Then
        $this->assertTrue($response->isRedirect());
        $this->assertEquals('/question', $response->headers->get('Location'));

        $this->httpClient->followRedirect();

        $successMessage = $this->translator->trans('message.success');
        $this->assertSelectorTextContains('.alert.alert-success[role="alert"]', $successMessage);
    }

    /**
     * Test '/question/{question_id}/edit' route for the author of the question.
     * This route is available for question's author.
     *
     * @throws \Exception
     */
    public function testQuestionEditRouteAuthor(): void
    {
        // Setup
        $category = new Category();
        $category->setTitle('test_category');

        $authorUser = $this->createUser([UserRole::ROLE_USER->value]);

        $question = new Question();
        $question->setTitle('Test title');
        $question->setComment('Test comment');
        $question->setAuthor($authorUser);
        $question->setCategory($category);

        $this->entityManager->persist($category);
        $this->entityManager->persist($question);
        $this->entityManager->flush();

        $questionId = $question->getId();
        $this->httpClient->loginUser($authorUser);

        // When
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$questionId.'/edit');
        $resultHttpStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // Then
        $this->assertEquals(200, $resultHttpStatusCode);
    }

    /**
     * Test '/question/{question_id}/edit' route for a user that is not the author of the question.
     * This route is available for question's author.
     *
     * @throws \Exception
     */
    public function testQuestionEditRouteNotAuthor(): void
    {
        // Setup
        $category = new Category();
        $category->setTitle('test_category');

        $author = new User();
        $author->setNickname('test_user');
        $author->setEmail('test@example.com');
        $author->setPassword('testowo');

        $user = $this->createUser([UserRole::ROLE_USER->value, UserRole::ROLE_ADMIN->value]);

        $question = new Question();
        $question->setTitle('Test title');
        $question->setComment('Test comment');
        $question->setAuthor($author);
        $question->setCategory($category);

        $this->entityManager->persist($author);
        $this->entityManager->persist($category);
        $this->entityManager->persist($question);
        $this->entityManager->flush();

        $questionId = $question->getId();
        $this->httpClient->loginUser($user);

        // When
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$questionId.'/edit');
        $resultHttpStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // Then
        $this->assertEquals(403, $resultHttpStatusCode);
    }

    /**
     * Test the response if edit of a question was successful.
     * This route is available for question's author.
     *
     * @throws \Exception
     */
    public function testQuestionEditResponseSuccess(): void
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
        $this->httpClient->loginUser($questionAuthor);

        $crawler = $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$questionId.'/edit');

        $editButton = $this->translator->trans('action.edit');
        $form = $crawler->selectButton($editButton)->form();
        $form['question[title]'] = 'Test Title';
        $form['question[comment]'] = 'Test Comment';
        $form['question[category]'] = $category->getId();
        $form['question[tags]'] = 'test_tags';

        // When
        $this->httpClient->submit($form);
        $response = $this->httpClient->getResponse();

        // Then
        $this->assertTrue($response->isRedirect());
        $this->assertEquals('/question/'.$questionId, $response->headers->get('Location'));

        $this->httpClient->followRedirect();

        $successMessage = $this->translator->trans('message.success');
        $this->assertSelectorTextContains('.alert.alert-success[role="alert"]', $successMessage);
    }

    /**
     * Test '/question/{question_id}/delete' route for the author of the question.
     * This route is available for question's author, admins.
     *
     * @throws \Exception
     */
    public function testQuestionDeleteRouteAuthor(): void
    {
        // Setup
        $category = new Category();
        $category->setTitle('test_category');

        $authorUser = $this->createUser([UserRole::ROLE_USER->value]);

        $question = new Question();
        $question->setTitle('Test title');
        $question->setComment('Test comment');
        $question->setAuthor($authorUser);
        $question->setCategory($category);

        $this->entityManager->persist($category);
        $this->entityManager->persist($question);
        $this->entityManager->flush();

        $questionId = $question->getId();
        $this->httpClient->loginUser($authorUser);

        // When
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$questionId.'/delete');
        $resultHttpStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // Then
        $this->assertEquals(200, $resultHttpStatusCode);
    }

    /**
     * Test '/question/{question_id}/delete' route for an admin.
     * This route is available for question's author, admins.
     *
     * @throws \Exception
     */
    public function testQuestionDeleteRouteAdmin(): void
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

        $this->entityManager->persist($author);
        $this->entityManager->persist($category);
        $this->entityManager->persist($question);
        $this->entityManager->flush();

        $questionId = $question->getId();
        $this->httpClient->loginUser($adminUser);

        // When
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$questionId.'/delete');
        $resultHttpStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // Then
        $this->assertEquals(200, $resultHttpStatusCode);
    }

    /**
     * Test '/question/{question_id}/delete' route for an authorized user that is not the author of the question.
     * This route is available for question's author, admin.
     *
     * @throws \Exception
     */
    public function testQuestionDeleteRouteNotAuthor(): void
    {
        // Setup
        $category = new Category();
        $category->setTitle('test_category');

        $author = new User();
        $author->setNickname('test_user');
        $author->setEmail('test@example.com');
        $author->setPassword('testowo');

        $user = $this->createUser([UserRole::ROLE_USER->value]);

        $question = new Question();
        $question->setTitle('Test title');
        $question->setComment('Test comment');
        $question->setAuthor($author);
        $question->setCategory($category);

        $this->entityManager->persist($author);
        $this->entityManager->persist($category);
        $this->entityManager->persist($question);
        $this->entityManager->flush();

        $questionId = $question->getId();
        $this->httpClient->loginUser($user);

        // When
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$questionId.'/delete');
        $resultHttpStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // Then
        $this->assertEquals(403, $resultHttpStatusCode);
    }

    /**
     * Test the response if delete of a question was successful.
     * This route is available for question's author.
     *
     * @throws \Exception
     */
    public function testQuestionDeleteResponseSuccess(): void
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
        $this->httpClient->loginUser($questionAuthor);

        $crawler = $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$questionId.'/delete');

        $deleteButton = $this->translator->trans('action.delete');
        $form = $crawler->selectButton($deleteButton)->form();

        // When
        $this->httpClient->submit($form);
        $response = $this->httpClient->getResponse();

        // Then
        $this->assertTrue($response->isRedirect());
        $this->assertEquals('/question', $response->headers->get('Location'));

        $this->httpClient->followRedirect();

        $successMessage = $this->translator->trans('message.success');
        $this->assertSelectorTextContains('.alert.alert-success[role="alert"]', $successMessage);
    }

    /**
     * Create user.
     *
     * @param array $roles User roles
     *
     * @return User User entity
     *
     * @throws \Exception
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
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        // Clear the entity manager to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
