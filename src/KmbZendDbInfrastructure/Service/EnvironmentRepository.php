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

use GtnPersistBase\Model\AggregateRootInterface;
use GtnPersistZendDb\Infrastructure\ZendDb;
use KmbDomain\Model\EnvironmentInterface;
use KmbDomain\Model\EnvironmentRepositoryInterface;
use KmbDomain\Model\UserInterface;
use Zend\Db\Adapter\Driver\StatementInterface;
use Zend\Db\Exception\ExceptionInterface;
use Zend\Db\Sql\Predicate\Predicate;
use Zend\Db\Sql\Select;

class EnvironmentRepository extends ZendDb\Repository implements EnvironmentRepositoryInterface
{
    /** @var string */
    protected $pathsTableName;

    /** @var array */
    protected $allRoots;

    /**
     * @param AggregateRootInterface $aggregateRoot
     * @return \GtnPersistBase\Model\RepositoryInterface
     * @throws \Zend\Db\Exception\ExceptionInterface
     */
    public function add(AggregateRootInterface $aggregateRoot)
    {
        $connection = $this->getDbAdapter()->getDriver()->getConnection()->beginTransaction();
        try {
            parent::add($aggregateRoot);
            $this->addPaths($aggregateRoot);
            $connection->commit();
        } catch (ExceptionInterface $e) {
            $connection->rollback();
            throw $e;
        }
        return $this;
    }

    /**
     * @param AggregateRootInterface $aggregateRoot
     * @return \GtnPersistBase\Model\RepositoryInterface
     * @throws \Zend\Db\Exception\ExceptionInterface
     */
    public function update(AggregateRootInterface $aggregateRoot)
    {
        /** @var EnvironmentInterface $aggregateRoot */
        $data = array_map(
            function (UserInterface $user) use ($aggregateRoot) {
                return [
                    'environment_id' => $aggregateRoot->getId(),
                    'user_id' => $user->getId(),
                ];
            }, $aggregateRoot->getUsers()
        );
        $connection = $this->getDbAdapter()->getDriver()->getConnection()->beginTransaction();
        try {
            if ($aggregateRoot->isDefault()) {
                $initDefault = $this->getMasterSql()->update($this->getTableName())->set([
                    'isdefault' => 0
                ]);
                $this->performWrite($initDefault);
            }

            parent::update($aggregateRoot);

            $delete = $this->getMasterSql()->delete('environments_users');
            $delete->where->equalTo('environment_id', $aggregateRoot->getId());
            $this->performWrite($delete);

            foreach ($data as $datum) {
                $insert = $this->getMasterSql()->insert('environments_users')->values($datum);
                $this->performWrite($insert);
            }

            $this->movePaths($aggregateRoot);
            $connection->commit();
        } catch (ExceptionInterface $e) {
            $connection->rollback();
            throw $e;
        }
        return $this;
    }

    /**
     * @param AggregateRootInterface $aggregateRoot
     * @return \GtnPersistBase\Model\RepositoryInterface
     * @throws \Zend\Db\Exception\ExceptionInterface
     */
    public function remove(AggregateRootInterface $aggregateRoot)
    {
        $connection = $this->getDbAdapter()->getDriver()->getConnection()->beginTransaction();
        try {
            parent::remove($aggregateRoot);
            $this->removePaths($aggregateRoot);
            $connection->commit();
        } catch (ExceptionInterface $e) {
            $connection->rollback();
            throw $e;
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getAllRoots()
    {
        if ($this->allRoots !== null) {
            return $this->allRoots;
        }

        $select = $this->getSelect()
            ->join(
                ['root' => $this->getPathsTableName()],
                $this->getTableName() . '.id = root.descendant_id',
                [],
                Select::JOIN_LEFT
            )
            ->join(
                ['parent' => $this->getPathsTableName()],
                'root.descendant_id = parent.descendant_id AND parent.ancestor_id <> parent.descendant_id',
                [],
                Select::JOIN_LEFT
            );
        $select->where->isNull('parent.descendant_id');
        $this->allRoots = $this->hydrateAggregateRootsFromResult($this->performRead($select));
        return $this->allRoots;
    }

    /**
     * @return EnvironmentInterface
     */
    public function getDefault()
    {
        $criteria = new Predicate();
        return $this->getBy($criteria->equalTo('isdefault', 1));
    }

    /**
     * @param string $name
     * @return EnvironmentInterface
     */
    public function getRootByName($name)
    {
        $select = $this->getSelect()
            ->join(
                ['root' => $this->getPathsTableName()],
                $this->getTableName() . '.id = root.descendant_id',
                [],
                Select::JOIN_LEFT
            )
            ->join(
                ['parent' => $this->getPathsTableName()],
                'root.descendant_id = parent.descendant_id AND parent.ancestor_id <> parent.descendant_id',
                [],
                Select::JOIN_LEFT
            );
        $select->where->isNull('parent.descendant_id')->and->equalTo('name', $name);
        $aggregateRoots = $this->hydrateAggregateRootsFromResult($this->performRead($select));
        return empty($aggregateRoots) ? null : $aggregateRoots[0];
    }

    /**
     * @param EnvironmentInterface $environment
     * @return array
     */
    public function getAllChildren(EnvironmentInterface $environment)
    {
        $select = $this->getSelect()
            ->join(
                ['children' => $this->getPathsTableName()],
                $this->getTableName() . '.id = children.descendant_id',
                [],
                Select::JOIN_LEFT
            )
            ->join(
                ['parent' => $this->getPathsTableName()],
                'children.descendant_id = parent.descendant_id',
                ['parent_id' => 'ancestor_id'],
                Select::JOIN_LEFT
            );

        $select
            ->where
            ->equalTo('parent.length', 1)
            ->and
            ->equalTo('children.ancestor_id', $environment->getId())
            ->and
            ->notEqualTo($this->getTableName() . '.id', $environment->getId());

        $result = $this->performRead($select);

        $className = $this->getAggregateRootClass();
        $allChildrenGroupedByParentId = [];
        foreach ($result as $row) {
            $aggregateRoot = new $className;
            $this->getAggregateRootHydrator()->hydrate($row, $aggregateRoot);
            $allChildrenGroupedByParentId[$row['parent_id']][] = $this->aggregateRootProxyFactory->createProxy($aggregateRoot);
        }

        $children = [];
        if (array_key_exists($environment->getId(), $allChildrenGroupedByParentId)) {
            $children = $allChildrenGroupedByParentId[$environment->getId()];
            foreach ($children as $child) {
                $this->setAllChildren($child, $allChildrenGroupedByParentId);
            }
        }
        return $children;
    }

    /**
     * @param EnvironmentInterface $environment
     * @return EnvironmentInterface
     */
    public function getParent(EnvironmentInterface $environment)
    {
        $select = $this->getSelect()->join(
            ['parent' => $this->getPathsTableName()],
            $this->getTableName() . '.id = parent.ancestor_id',
            [],
            Select::JOIN_LEFT
        );

        $select
            ->where
            ->equalTo('parent.descendant_id', $environment->getId())
            ->and
            ->notEqualTo($this->getTableName() . '.id', $environment->getId());

        $result = $this->performRead($select->order('parent.length'));

        $className = $this->getAggregateRootClass();
        $parents = [];
        foreach ($result as $row) {
            $aggregateRoot = new $className;
            $this->getAggregateRootHydrator()->hydrate($row, $aggregateRoot);
            $parents[] = $this->aggregateRootProxyFactory->createProxy($aggregateRoot);
        }

        $parent = null;
        if (!empty($parents)) {
            $parent = array_shift($parents);
            $this->setAllParents($parent, $parents);
        }
        return $parent;
    }

    /**
     * @param UserInterface $user
     * @return EnvironmentInterface[]
     */
    public function getAllForUser(UserInterface $user)
    {
        $select = $this->getSelect()->join(
            'environments_users',
            $this->getTableName() . '.id = environments_users.environment_id',
            [],
            Select::JOIN_LEFT
        );
        $select->where->equalTo('environments_users.user_id', $user->getId());

        return $this->hydrateAggregateRootsFromResult($this->performRead($select));
    }

    /**
     * Set PathsTableName.
     *
     * @param string $pathsTableName
     * @return EnvironmentRepository
     */
    public function setPathsTableName($pathsTableName)
    {
        $this->pathsTableName = $pathsTableName;
        return $this;
    }

    /**
     * Get PathsTableName.
     *
     * @return string
     */
    public function getPathsTableName()
    {
        return $this->pathsTableName;
    }

    /**
     * @param AggregateRootInterface $aggregateRoot
     * @return EnvironmentRepository
     */
    protected function addPaths(AggregateRootInterface $aggregateRoot)
    {
        $id = $aggregateRoot->getId();
        /** @var StatementInterface $statement */
        $statement = $this->getDbAdapter()->query(
            'INSERT INTO ' . $this->getPathsTableName() . ' (ancestor_id, descendant_id, length) ' .
            "SELECT $id, $id, 0 UNION ALL " .
            "SELECT ancestor_id, $id, length+1 FROM " . $this->getPathsTableName() . ' WHERE descendant_id = ?'
        );

        $parentId = $aggregateRoot->hasParent() ? $aggregateRoot->getParent()->getId() : 0;
        $statement->execute([$parentId]);

        return $this;
    }

    /**
     * @param AggregateRootInterface $aggregateRoot
     * @return EnvironmentRepository
     */
    protected function removePaths(AggregateRootInterface $aggregateRoot)
    {
        /** @var StatementInterface $statement */
        $statement = $this->getDbAdapter()->query(
            'DELETE FROM ' . $this->getPathsTableName() . ' ' .
            'WHERE descendant_id IN ' .
            '(SELECT * FROM ' .
            '(SELECT descendant_id FROM ' . $this->getPathsTableName() . ' WHERE ancestor_id = ?)' .
            'AS tmp)'
        );
        $statement->execute([$aggregateRoot->getId()]);

        return $this;
    }

    /**
     * @param AggregateRootInterface $aggregateRoot
     * @return EnvironmentRepository
     */
    protected function movePaths(AggregateRootInterface $aggregateRoot)
    {
        /** Awful hack to support both MySQL and SQLite ... */
        if ($this->getDbAdapter()->getDriver()->getDatabasePlatformName() == 'Mysql') {
            /** @var StatementInterface $statement */
            $statement = $this->getDbAdapter()->query(
                'DELETE a FROM ' . $this->getPathsTableName() . ' AS a ' .
                'JOIN ' . $this->getPathsTableName() . ' AS d ON a.descendant_id = d.descendant_id ' .
                'LEFT JOIN ' . $this->getPathsTableName() . ' AS x ON x.ancestor_id = d.ancestor_id ' .
                'AND x.descendant_id = a.ancestor_id ' .
                'WHERE d.ancestor_id = ? AND x.ancestor_id IS NULL'
            );
            $statement->execute([$aggregateRoot->getId()]);
        } else {
            /** @var StatementInterface $statement */
            $statement = $this->getDbAdapter()->query(
                'DELETE FROM ' . $this->getPathsTableName() . ' ' .
                'WHERE descendant_id IN ' .
                '(SELECT descendant_id FROM ' . $this->getPathsTableName() . ' WHERE ancestor_id = ?) ' .
                'AND ancestor_id NOT IN ' .
                '(SELECT descendant_id FROM ' . $this->getPathsTableName() . ' WHERE ancestor_id = ?)'
            );
            $statement->execute([$aggregateRoot->getId(), $aggregateRoot->getId()]);
        }

        /** @var StatementInterface $statement */
        $statement = $this->getDbAdapter()->query(
            'INSERT INTO ' . $this->getPathsTableName() . ' (ancestor_id, descendant_id, length) ' .
            'SELECT supertree.ancestor_id, subtree.descendant_id, supertree.length + subtree.length + 1 ' .
            'FROM ' . $this->getPathsTableName() . ' AS supertree CROSS JOIN ' . $this->getPathsTableName() . ' AS subtree ' .
            'WHERE subtree.ancestor_id = ? AND supertree.descendant_id = ?'
        );

        $id = $aggregateRoot->getId();
        $parentId = $aggregateRoot->hasParent() ? $aggregateRoot->getParent()->getId() : 0;
        $statement->execute([$id, $parentId]);

        return $this;
    }

    /**
     * @param EnvironmentInterface $environment
     * @param array                $allChildrenGroupedByParentId
     */
    protected function setAllChildren($environment, $allChildrenGroupedByParentId)
    {
        if (array_key_exists($environment->getId(), $allChildrenGroupedByParentId)) {
            foreach ($allChildrenGroupedByParentId[$environment->getId()] as $child) {
                $this->setAllChildren($child, $allChildrenGroupedByParentId);
                $environment->addChild($child);
            }
        }
    }

    /**
     * @param EnvironmentInterface $environment
     * @param array                $parents
     */
    protected function setAllParents($environment, $parents)
    {
        if (empty($parents)) {
            return;
        }
        $parent = array_shift($parents);
        $environment->setParent($parent);
        $this->setAllParents($parent, $parents);
    }
}
