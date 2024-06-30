<?php

/**
 * User service tests.
 */

namespace App\Tests\Service;

use App\Entity\Category;
use App\Entity\User;
use App\Service\UserService;
use App\Service\UserServiceInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class UserServiceTest.
 */
class UserServiceTest extends KernelTestCase
{
    /**
     * User repository.
     */
    private ?EntityManagerInterface $entityManager;

    /**
     * User service.
     */
    private ?UserServiceInterface $userService;

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
        $this->userService = $container->get(UserService::class);
    }

    /**
     * Test save.
     *
     * @throws ORMException
     */
    public function testSave(): void
    {
        // given
        $expectedUser = new User();
        $expectedUser->setNickname('test_user');
        $expectedUser->setEmail('test@example.com');
        $expectedUser->setPassword('testowo');
        $expectedUser->setRoles(['ROLE_USER', 'ROLE_ADMIN']);

        // when
        $this->userService->save($expectedUser);

        // then
        $expectedUserId = $expectedUser->getId();
        $resultUser = $this->entityManager->createQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->where('user.id = :id')
            ->setParameter(':id', $expectedUserId, Types::INTEGER)
            ->getQuery()
            ->getSingleResult();

        $this->assertEquals($expectedUser, $resultUser);
    }

    /**
     * Test delete.
     *
     * @throws ORMException
     */
    public function testDelete(): void
    {
        // given
        $userToDelete = new User();
        $userToDelete->setNickname('test_user');
        $userToDelete->setEmail('test@example.com');
        $userToDelete->setPassword('testowo');

        $this->entityManager->persist($userToDelete);
        $this->entityManager->flush();

        $deletedUserId = $userToDelete->getId();

        // when
        $this->userService->delete($userToDelete);

        // then
        $resultUser = $this->entityManager->createQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->where('user.id = :id')
            ->setParameter(':id', $deletedUserId, Types::INTEGER)
            ->getQuery()
            ->getOneOrNullResult();

        $this->assertNull($resultUser);
    }

    /**
     * Test find by id.
     *
     * @return void
     */
    public function testFindById(): void
    {
        // given
        $expectedUser = new User();
        $expectedUser->setNickname('test_user');
        $expectedUser->setEmail('test@example.com');
        $expectedUser->setPassword('testowo');

        $this->entityManager->persist($expectedUser);
        $this->entityManager->flush();

        $expectedUserId = $expectedUser->getId();

        // when
        $resultUser = $this->userService->findOneById($expectedUserId);

        // then
        $this->assertEquals($expectedUser, $resultUser);
    }

    /**
     * Test pagination empty list.
     */
    public function testGetPaginatedList(): void
    {
        // given
        $page = 1;
        $dataSetSize = 3;
        $expectedResultSize = 3;

        $counter = 0;
        while ($counter < $dataSetSize) {
            $user = new User();
            $user->setNickname('test_user #'.$counter);
            $user->setEmail('test'.$counter.'@example.com');
            $user->setPassword('testowo');

            $this->userService->save($user);

            ++$counter;
        }

        // when
        $result = $this->userService->getPaginatedList($page);

        // then
        $this->assertEquals($expectedResultSize, $result->count());
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
