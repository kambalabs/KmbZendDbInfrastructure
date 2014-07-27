<?php
namespace KmbZendDbInfrastructureTest\Model;

use KmbDomain\Model\User;
use KmbDomain\Model\UserInterface;
use KmbZendDbInfrastructure\Model\UserHydrator;

class UserHydratorTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canExtract()
    {
        $user = new User('jdoe', 'John DOE', 'jdoe@gmail.com', UserInterface::ROLE_ROOT);
        $user->setId(1);
        $hydrator = new UserHydrator();

        $this->assertEquals([
            'id' => 1,
            'login' => 'jdoe',
            'name' => 'John DOE',
            'email' => 'jdoe@gmail.com',
            'role' => UserInterface::ROLE_ROOT,
        ], $hydrator->extract($user));
    }

    /** @test */
    public function canExtractWithoutId()
    {
        $user = new User('jdoe', 'John DOE', 'jdoe@gmail.com', UserInterface::ROLE_ROOT);
        $hydrator = new UserHydrator();

        $this->assertEquals([
            'login' => 'jdoe',
            'name' => 'John DOE',
            'email' => 'jdoe@gmail.com',
            'role' => UserInterface::ROLE_ROOT,
        ], $hydrator->extract($user));
    }

    /** @test */
    public function canHydrate()
    {
        $hydrator = new UserHydrator();
        $user = new User();

        $hydratedUser = $hydrator->hydrate([
            'id' => 1,
            'login' => 'jdoe',
            'name' => 'John DOE',
            'email' => 'jdoe@gmail.com',
            'role' => UserInterface::ROLE_ROOT
        ], $user);

        $this->assertEquals(1, $hydratedUser->getId());
        $this->assertEquals('jdoe', $hydratedUser->getLogin());
        $this->assertEquals('John DOE', $hydratedUser->getName());
        $this->assertEquals('jdoe@gmail.com', $hydratedUser->getEmail());
        $this->assertEquals(UserInterface::ROLE_ROOT, $hydratedUser->getRole());
    }
}
