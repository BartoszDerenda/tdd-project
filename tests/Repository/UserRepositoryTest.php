<?php

namespace App\Tests\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class UserRepositoryTest.
 */
class UserRepositoryTest extends KernelTestCase
{
    /**
     * Entity manager.
     */
    private ?EntityManagerInterface $entityManager;

    /**
     * User service.
     */
    private ?UserRepository $userRepository;

    /**
     * Set up test.
     *
     * @throws Exception
     */
    protected function setUp(): void
    {
        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        $this->userRepository = $container->get(UserRepository::class);
    }

    /**
     * Test FindOneById.
     *
     * @return void
     */
    public function testFindOneById(): void{

        // given
        $user = new User();
        $user->setNickname('test_user');
        $user->setEmail('test@example.com');
        $user->setPassword('testowo');

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // when
        $expectedUser = $this->userRepository->findOneById($user->getId());

        $this->assertNotNull($expectedUser);
        $this->assertSame('test_user', $expectedUser->getNickname());
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