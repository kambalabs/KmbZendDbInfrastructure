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
namespace KmbZendDbInfrastructure\Service;

use GtnPersistZendDb\Infrastructure\ZendDb\Repository;
use KmbDomain\Model\LogInterface;
use KmbDomain\Service\LogRepositoryInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Expression as SqlExpression;

class LogRepository extends Repository implements LogRepositoryInterface
{
    /**
     * @param string $search
     * @param int    $offset
     * @param int    $limit
     * @param array  $orderBy
     * @return LogInterface[] $logs, int $filteredCount
     */
    public function getAllPaginated($search, $offset, $limit, $orderBy)
    {
        $select = $this->getSelect()->columns([
            'size' => new SqlExpression('COUNT(*)')
        ]);
        if (!empty($search)) {
            $search = '%' . implode('%', explode(' ', trim($search))) . '%';
            $select->where->like('comment', $search)->or->like('created_by', $search);
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($this->performRead($select));
        $filteredCount = $resultSet->current()->size;

        $select = $this->getSelect();
        if (!empty($search)) {
            $select->where->like('comment', $search)->or->like('created_by', $search);
        }
        if ($offset !== null) {
            $select->offset(intval($offset));
        }
        if ($limit !== null) {
            $select->limit(intval($limit));
        }
        if (!empty($orderBy)) {
            foreach ($orderBy as $clause) {
                $select->order($clause['column'] . ' ' . $clause['dir']);
            }
        } else {
            $select->order('created_at desc');
        }

        return [$this->hydrateAggregateRootsFromResult($this->performRead($select)), $filteredCount];
    }
}
