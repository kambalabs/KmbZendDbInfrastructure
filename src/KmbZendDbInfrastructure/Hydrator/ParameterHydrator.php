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

use KmbDomain\Model\ParameterInterface;
use Zend\Stdlib\Hydrator\HydratorInterface;

class ParameterHydrator implements HydratorInterface
{
    /**
     * Extract values from an object
     *
     * @param  ParameterInterface $object
     * @return array
     */
    public function extract($object)
    {
        $data = [
            'puppet_class_id' => $object->getClass()->getId(),
            'name' => $object->getName(),
            'parent_id' => $object->hasParent() ? $object->getParent()->getId() : null,
        ];
        if ($object->getId()) {
            $data['id'] = $object->getId();
        }
        return $data;
    }

    /**
     * Hydrate $object with the provided $data.
     *
     * @param  array              $data
     * @param  ParameterInterface $object
     * @return ParameterInterface
     */
    public function hydrate(array $data, $object)
    {
        $object->setId($this->getData('id', $data));
        $object->setName($this->getData('name', $data));
        return $object;
    }

    /**
     * @param string $key
     * @param array  $data
     * @return mixed
     */
    protected function getData($key, $data)
    {
        if (isset($data['p.' . $key])) {
            return $data['p.' . $key];
        }
        if (isset($data[$key])) {
            return $data[$key];
        }
        return null;
    }
}
