<?php
/**
 * Category controller tests.
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
 * class CategoryControllerTest.
 */
class CategoryControllerTest extends WebTestCase
{
    /**
     * Test route.
     *
     * @const string
     */
    public const TEST_ROUTE = '/categories';

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
     * Test '/categories' route for admin.
     * This route is available for admin.
     *
     * @throws \Exception
     */
    public function testCategoryRouteAdmin(): void
    {
        // Setup
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $this->httpClient->loginUser($adminUser);

        // When
        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultHttpStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // Then
        $this->assertEquals(200, $resultHttpStatusCode);
    }

    /**
     * Test '/categories/{category_id}' route for admin.
     * This route is available for admin.
     *
     * @throws \Exception
     */
    public function testCategoryShowRouteAdmin(): void
    {
        // Setup
        $category = new Category();
        $category->setTitle('test_category');

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $categoryId = $category->getId();

        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $this->httpClient->loginUser($adminUser);

        // When
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$categoryId);
        $resultHttpStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // Then
        $this->assertEquals(200, $resultHttpStatusCode);
    }

    /**
     * Test '/categories/create' route for admin.
     * This route is available for admin.
     *
     * @throws \Exception
     */
    public function testCategoryCreateRouteAdmin(): void
    {
        // Setup
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $this->httpClient->loginUser($adminUser);

        // When
        $this->httpClient->request('GET', self::TEST_ROUTE.'/create');
        $resultHttpStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // Then
        $this->assertEquals(200, $resultHttpStatusCode);
    }

    /**
     * Test the response if creation of a category was successful.
     * This route is available for admin.
     *
     * @throws \Exception
     */
    public function testCategoryCreateResponseSuccess(): void
    {
        // Setup
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $this->httpClient->loginUser($adminUser);

        $crawler = $this->httpClient->request('GET', self::TEST_ROUTE.'/create');

        $saveButton = $this->translator->trans('action.save');
        $form = $crawler->selectButton($saveButton)->form();
        $form['category[title]'] = 'Test Category';

        // When
        $this->httpClient->submit($form);
        $response = $this->httpClient->getResponse();

        // Then
        $this->assertTrue($response->isRedirect());
        $this->assertEquals('/categories', $response->headers->get('Location'));

        $this->httpClient->followRedirect();

        $successMessage = $this->translator->trans('message.success');
        $this->assertSelectorTextContains('.alert.alert-success[role="alert"]', $successMessage);
    }

    /**
     * Test '/categories/{category_id}/edit' route for admin.
     * This route is available for admin.
     *
     * @throws \Exception
     */
    public function testCategoryEditRouteAdmin(): void
    {
        // Setup
        $category = new Category();
        $category->setTitle('test_category');

        $this->entityManager->persist($category);
        $this->entityManager->flush();
        $categoryId = $category->getId();

        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $this->httpClient->loginUser($adminUser);

        // When
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$categoryId.'/edit');
        $resultHttpStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // Then
        $this->assertEquals(200, $resultHttpStatusCode);
    }

    /**
     * Test the response if edit of a category was successful.
     * This route is available for admin.
     *
     * @throws \Exception
     */
    public function testCategoryEditResponseSuccess(): void
    {
        // Setup
        $category = new Category();
        $category->setTitle('test_category');

        $this->entityManager->persist($category);
        $this->entityManager->flush();
        $categoryId = $category->getId();

        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $this->httpClient->loginUser($adminUser);

        $crawler = $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$categoryId.'/edit');

        $editButton = $this->translator->trans('action.edit');
        $form = $crawler->selectButton($editButton)->form();
        $form['category[title]'] = 'Test Category';

        // When
        $this->httpClient->submit($form);
        $response = $this->httpClient->getResponse();

        // Then
        $this->assertTrue($response->isRedirect());
        $this->assertEquals('/categories', $response->headers->get('Location'));

        $this->httpClient->followRedirect();

        $successMessage = $this->translator->trans('message.success');
        $this->assertSelectorTextContains('.alert.alert-success[role="alert"]', $successMessage);
    }

    /**
     * Test '/categories/{category_id}/delete' route for admin.
     * This route is available for admin.
     *
     * @throws \Exception
     */
    public function testCategoryDeleteRouteAdmin(): void
    {
        // Setup
        $category = new Category();
        $category->setTitle('test_category');

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $categoryId = $category->getId();

        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $this->httpClient->loginUser($adminUser);

        // When
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$categoryId.'/delete');
        $resultHttpStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // Then
        $this->assertEquals(200, $resultHttpStatusCode);
    }

    /**
     * Test '/categories/{category_id}/delete' route for admin for categories in usage by questions.
     * This route is available for admin but should return 302 if the category is being used.
     *
     * @throws \Exception
     */
    public function testCategoryDeleteInUsageRouteAdmin(): void
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

        $this->entityManager->persist($author);
        $this->entityManager->persist($category);
        $this->entityManager->persist($question);
        $this->entityManager->flush();

        $categoryId = $category->getId();

        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $this->httpClient->loginUser($adminUser);

        // When
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$categoryId.'/delete');
        $resultHttpStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // Then
        $this->assertEquals(302, $resultHttpStatusCode);
    }

    /**
     * Test the response if deletion of a category was successful.
     * This route is available for admin.
     *
     * @throws \Exception
     */
    public function testCategoryDeleteResponseSuccess(): void
    {
        // Setup
        $category = new Category();
        $category->setTitle('test_category');

        $this->entityManager->persist($category);
        $this->entityManager->flush();
        $categoryId = $category->getId();

        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $this->httpClient->loginUser($adminUser);

        $crawler = $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$categoryId.'/delete');

        $deleteButton = $this->translator->trans('action.delete');
        $form = $crawler->selectButton($deleteButton)->form();

        // When
        $this->httpClient->submit($form);
        $response = $this->httpClient->getResponse();

        // Then
        $this->assertTrue($response->isRedirect());
        $this->assertEquals('/categories', $response->headers->get('Location'));

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
