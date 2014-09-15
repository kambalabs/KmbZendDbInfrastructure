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

use KmbDomain\Model\RevisionInterface;
use Zend\Stdlib\Hydrator\HydratorInterface;

class RevisionHydrator implements HydratorInterface
{
    const SQL_FORMAT = 'Y-m-d H:i:s';

    /**
     * Extract values from an object
     *
     * @param  RevisionInterface $object
     * @return array
     */
    public function extract($object)
    {
        $data = [
            'environment_id' => $object->getEnvironment()->getId(),
            'updated_at' => $object->getUpdatedAt() !== null ? $object->getUpdatedAt()->format(self::SQL_FORMAT) : null,
            'updated_by' => $object->getUpdatedBy(),
            'released_at' => $object->getReleasedAt() !== null ? $object->getReleasedAt()->format(self::SQL_FORMAT) : null,
            'released_by' => $object->getReleasedBy(),
            'comment' => $object->getComment()
        ];
        if ($object->getId()) {
            $data['id'] = $object->getId();
        }
        return $data;
    }

    /**
     * Hydrate $object with the provided $data.
     *
     * @param  array  $data
     * @param  RevisionInterface $object
     * @return RevisionInterface
     */
    public function hydrate(array $data, $object)
    {
        $object->setId($data['id']);
        $object->setUpdatedAt(isset($data['updated_at']) ? new \DateTime($data['updated_at']) : null);
        $object->setUpdatedBy($data['updated_by']);
        $object->setReleasedAt(isset($data['released_at']) ? new \DateTime($data['released_at']) : null);
        $object->setReleasedBy($data['released_by']);
        $object->setComment($data['comment']);
        return $object;
    }
}
