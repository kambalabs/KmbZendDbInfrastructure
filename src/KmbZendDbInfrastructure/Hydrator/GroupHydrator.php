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

use KmbDomain\Model\GroupInterface;
use Zend\Stdlib\Hydrator\HydratorInterface;

class GroupHydrator implements HydratorInterface
{
    /**
     * Extract values from an object
     *
     * @param  GroupInterface $object
     * @return array
     */
    public function extract($object)
    {
        $data = [
            'revision_id' => $object->getRevision()->getId(),
            'name' => $object->getName(),
            'ordering' => $object->getOrdering(),
            'include_pattern' => $object->getIncludePattern(),
            'exclude_pattern' => $object->getExcludePattern(),
        ];
        if ($object->getId()) {
            $data['id'] = $object->getId();
        }
        return $data;
    }

    /**
     * Hydrate $object with the provided $data.
     *
     * @param  array          $data
     * @param  GroupInterface $object
     * @return GroupInterface
     */
    public function hydrate(array $data, $object)
    {
        $object->setId($this->getData('id', $data));
        $object->setName($this->getData('name', $data));
        $object->setOrdering($this->getData('ordering', $data));
        $object->setIncludePattern($this->getData('include_pattern', $data));
        $object->setExcludePattern($this->getData('exclude_pattern', $data));
    }

    /**
     * @param string $key
     * @param array $data
     */
    protected function getData($key, $data)
    {
        if (isset($data['g.' . $key])) {
            return $data['g.' . $key];
        }
        return $data[$key];
    }
}
