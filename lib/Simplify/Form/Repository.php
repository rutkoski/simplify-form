<?php

/**
 * SimplifyPHP Framework
 *
 * This file is part of SimplifyPHP Framework.
 *
 * SimplifyPHP Framework is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * SimplifyPHP Framework is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Rodrigo Rutkoski Rodrigues <rutkoski@gmail.com>
 */

namespace Simplify\Form;

use Simplify;
use Simplify\Pager;
use Simplify\Db\RepositoryInterface;

/**
 *
 * Form repository
 *
 */
class Repository implements RepositoryInterface
{

  /**
   *
   * @var string
   */
  public $table;

  /**
   *
   * @var string
   */
  public $pk;

  /**
   *
   * @param string $table repository table
   * @param string $pk repository primary key
   */
  public function __construct($table = null, $pk = null)
  {
    $this->table = $table;
    $this->pk = $pk;
  }

  /**
   * Get a pager
   *
   * @param array $params query parameters
   * @return Pager
   */
  public function findPager($params = null)
  {
    $limit = $params['limit'];
    $offset = $params['offset'];

    return new Pager($this->findCount($params), $limit, $offset);
  }

  /**
   * (non-PHPdoc)
   * @see RepositoryInterface::find()
   */
  public function find($id = null, $params = null)
  {
    $query = Simplify::db()->query()->from($this->table);

    $query->limit(1);

    if ($id) {
      $query->where("$this->pk = :$this->pk");
      $params['data'][$this->pk] = $id;
    }

    $result = $query->setParams($params)->execute()->fetchRow();

    return $result;
  }

  /**
   * (non-PHPdoc)
   * @see RepositoryInterface::findAll()
   */
  public function findAll($params = null)
  {
    $query = Simplify::db()->query()->from($this->table)->setParams($params);

    $result = $query->execute()->fetchAll();

    return $result;
  }

  /**
   * (non-PHPdoc)
   * @see RepositoryInterface::findCount()
   */
  public function findCount($params = null)
  {
    $query = Simplify::db()->query()->setParams($params)->from($this->table)->select(false)->limit(
      false)->offset(false)->select("COUNT($this->pk)");
    $result = $query->execute()->fetchOne();
    return intval($result);
  }

  /**
   * (non-PHPdoc)
   * @see RepositoryInterface::delete()
   */
  public function delete($id = null, $params = array())
  {
    $result = Simplify::db()->delete($this->table, "$this->pk = ?")->execute($id);

    return $result->numRows();
  }

  /**
   * (non-PHPdoc)
   * @see RepositoryInterface::deleteAll()
   */
  public function deleteAll($params = null)
  {
    $result = Simplify::db()->delete($this->table)->setParams($params)->execute();

    return $result->numRows();
  }

  /**
   * (non-PHPdoc)
   * @see RepositoryInterface::save()
   */
  public function save(&$data)
  {
    $id = sy_get_param($data, $this->pk);

    if (empty($id)) {
      return $this->insert($data);
    }
    else {
      return $this->update($data);
    }
  }

  /**
   * Insert a new item in the repository
   *
   * @param array $data item data
   */
  public function insert(&$data)
  {
    Simplify::db()->insert($this->table, $data)->execute($data);

    $data[$this->pk] = Simplify::db()->lastInsertId();
  }

  /**
   * Update an existing item in the repository
   *
   * @param array $data item data
   */
  public function update(&$data)
  {
    $result = 0;

    if (count($data) > 1) {
      $result = Simplify::db()->update($this->table, $data, "$this->pk = :$this->pk")->execute($data)->numRows();
    }
  }

}
