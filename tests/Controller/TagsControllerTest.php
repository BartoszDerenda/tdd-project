<?php

/**
 * Tags controller tests.
 */

namespace App\Tests\Controller;

use App\Entity\Tags;
use App\Entity\Enum\UserRole;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * class TagsControllerTest.
 */
class TagsControllerTest extends WebTestCase
{
    /**
     * Test route.
     *
     * @const string
     */
    public const TEST_ROUTE = '/tags';

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
     * Test '/tags' route for admin.
     * This route is available for admin.
     *
     * @return void
     *
     * @throws Exception
     */
    public function testTagsRouteAdmin(): void
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
     * Test '/tags/{tags_id}' route for admin.
     * This route is available for admin.
     *
     * @return void
     *
     * @throws Exception
     */
    public function testTagsShowRouteAdmin(): void
    {
        // Setup
        $tags = new Tags();
        $tags->setTitle('test_tags');

        $this->entityManager->persist($tags);
        $this->entityManager->flush();

        $tagsId = $tags->getId();

        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $this->httpClient->loginUser($adminUser);

        // When
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$tagsId);
        $resultHttpStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // Then
        $this->assertEquals(200, $resultHttpStatusCode);
    }

    /**
     * Test '/tags/create' route for admin.
     * This route is available for admin.
     *
     * @return void
     *
     * @throws Exception
     */
    public function testTagsCreateRouteAdmin(): void
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
     * Test the response if creation of a tag was successful.
     * This route is available for admin.
     *
     * @return void
     *
     * @throws Exception
     */
    public function testTagCreateResponseSuccess(): void
    {
        // Setup
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $this->httpClient->loginUser($adminUser);

        $crawler = $this->httpClient->request('GET', self::TEST_ROUTE.'/create');

        $saveButton = $this->translator->trans('action.save');
        $form = $crawler->selectButton($saveButton)->form();
        $form['tags[title]'] = 'Test Tag';

        // When
        $this->httpClient->submit($form);
        $response = $this->httpClient->getResponse();

        // Then
        $this->assertTrue($response->isRedirect());
        $this->assertEquals('/tags', $response->headers->get('Location'));

        $this->httpClient->followRedirect();

        $successMessage = $this->translator->trans('message.success');
        $this->assertSelectorTextContains('.alert.alert-success[role="alert"]', $successMessage);
    }

    /**
     * Test '/tags/{tags_id}/edit' route for admin.
     * This route is available for admin.
     *
     * @return void
     *
     * @throws Exception
     */
    public function testTagsEditRouteAdmin(): void
    {
        // Setup
        $tags = new Tags();
        $tags->setTitle('test_tags');

        $this->entityManager->persist($tags);
        $this->entityManager->flush();

        $tagsId = $tags->getId();

        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $this->httpClient->loginUser($adminUser);

        // When
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$tagsId.'/edit');
        $resultHttpStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // Then
        $this->assertEquals(200, $resultHttpStatusCode);
    }

    /**
     * Test the response if edit of a tag was successful.
     * This route is available for admin.
     *
     * @return void
     *
     * @throws Exception
     */
    public function testTagEditResponseSuccess(): void
    {
        // Setup
        $tags = new Tags();
        $tags->setTitle('test_tag');

        $this->entityManager->persist($tags);
        $this->entityManager->flush();
        $tagsId = $tags->getId();

        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $this->httpClient->loginUser($adminUser);

        $crawler = $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$tagsId.'/edit');

        $editButton = $this->translator->trans('action.edit');
        $form = $crawler->selectButton($editButton)->form();

        // When
        $this->httpClient->submit($form);
        $response = $this->httpClient->getResponse();

        // Then
        $this->assertTrue($response->isRedirect());
        $this->assertEquals('/tags', $response->headers->get('Location'));

        $this->httpClient->followRedirect();

        $successMessage = $this->translator->trans('message.success');
        $this->assertSelectorTextContains('.alert.alert-success[role="alert"]', $successMessage);
    }

    /**
     * Test '/tags/{tags_id}/delete' route for admin.
     * This route is available for admin.
     *
     * @return void
     *
     * @throws Exception
     */
    public function testTagsDeleteRouteAdmin(): void
    {
        // Setup
        $tags = new Tags();
        $tags->setTitle('test_tags');

        $this->entityManager->persist($tags);
        $this->entityManager->flush();

        $tagsId = $tags->getId();

        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $this->httpClient->loginUser($adminUser);

        // When
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$tagsId.'/delete');
        $resultHttpStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // Then
        $this->assertEquals(200, $resultHttpStatusCode);
    }

    /**
     * Test the response if deletion of a tag was successful.
     * This route is available for admin.
     *
     * @return void
     *
     * @throws Exception
     */
    public function testTagDeleteResponseSuccess(): void
    {
        // Setup
        $tags = new Tags();
        $tags->setTitle('test_tag');

        $this->entityManager->persist($tags);
        $this->entityManager->flush();
        $tagsId = $tags->getId();

        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $this->httpClient->loginUser($adminUser);

        $crawler = $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$tagsId.'/delete');

        $deleteButton = $this->translator->trans('action.delete');
        $form = $crawler->selectButton($deleteButton)->form();

        // When
        $this->httpClient->submit($form);
        $response = $this->httpClient->getResponse();

        // Then
        $this->assertTrue($response->isRedirect());
        $this->assertEquals('/tags', $response->headers->get('Location'));

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
