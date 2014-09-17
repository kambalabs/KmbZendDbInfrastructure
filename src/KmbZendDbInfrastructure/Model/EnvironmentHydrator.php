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
namespace KmbZendDbInfrastructure\Model;

use KmbDomain\Model\EnvironmentInterface;
use Zend\Stdlib\Hydrator\HydratorInterface;

class EnvironmentHydrator implements HydratorInterface
{
    /**
     * Extract values from an object
     *
     * @param  EnvironmentInterface $object
     * @return array
     */
    public function extract($object)
    {
        $data = [];
        if ($object->getId() !== null) {
            $data['id'] = $object->getId();
        }
        $data['name'] = $object->getName();
        $data['isdefault'] = $object->isDefault() ? 1 : 0;
        return $data;
    }

    /**
     * Hydrate $object with the provided $data.
     *
     * @param  array  $data
     * @param  EnvironmentInterface $object
     * @return EnvironmentInterface
     */
    public function hydrate(array $data, $object)
    {
        $object->setId(isset($data['e.id']) ? $data['e.id'] : $data['id']);
        $object->setName(isset($data['e.name']) ? $data['e.name'] : $data['name']);
        $object->setDefault(isset($data['e.isdefault']) ? $data['e.isdefault'] == 1 : $data['isdefault'] == 1);
        return $object;
    }
}
