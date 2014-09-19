<?php
/**
 * @copyright Copyright (c) 2014 Orange Applications for Business
 * @link      http://github.com/kambalabs for the sources repositories
 *
 * This file is part of Kamba.
 *
 * Kamba is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * Kamba is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Kamba.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace KmbZendDbInfrastructure\Hydrator;

use KmbDomain\Model\UserInterface;
use Zend\Stdlib\Hydrator\HydratorInterface;

class UserHydrator implements HydratorInterface
{
    /**
     * Extract values from an object
     *
     * @param  UserInterface $object
     * @return array
     */
    public function extract($object)
    {
        $data = [
            'id' => $object->getId(),
            'login' => $object->getLogin(),
            'name' => $object->getName(),
            'email' => $object->getEmail(),
            'role' => $object->getRole(),
        ];
        if ($object->getId() === null) {
            unset($data['id']);
        }
        return $data;
    }

    /**
     * Hydrate $object with the provided $data.
     *
     * @param  array  $data
     * @param  UserInterface $object
     * @return UserInterface
     */
    public function hydrate(array $data, $object)
    {
        $object->setId($data['id']);
        $object->setLogin($data['login']);
        $object->setName($data['name']);
        $object->setEmail($data['email']);
        $object->setRole($data['role']);
        return $object;
    }
}
