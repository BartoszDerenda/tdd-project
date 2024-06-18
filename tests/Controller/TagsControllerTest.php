<?php
/**
 * Tags controller tests.
 */

namespace App\Tests\Controller;

use App\Entity\Tags;
use App\Entity\Enum\UserRole;
use App\Entity\Question;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

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
    }

    /**
     * Test '/tags' route for admin.
     * This route is available for admin.
     *
     * @return void
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
     * Test '/tags/{tags_id}/edit' route for admin.
     * This route is available for admin.
     *
     * @return void
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
     * Test '/tags/{tags_id}/delete' route for admin.
     * This route is available for admin.
     *
     * @return void
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
