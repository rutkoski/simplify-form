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

namespace Simplify\Form\Repository;

/**
 *
 * Sortable repository
 *
 */
class Sortable extends \Simplify\Form\Repository implements \Simplify\Db\SortableInterface
{

  /**
   * Sort column
   *
   * @var string
   */
  public $sortColumn;

  /**
   * Sort direction
   *
   * @var string
   */
  public $sortDirection;

  /**
   * Sort group column
   *
   * @var string
   */
  public $sortGroupColumn;

  /**
   * Constructor
   *
   * @param string $table repository table
   * @param string $pk repository primary keys
   * @param string $sort sort column
   * @param string $direction sort direction
   */
  public function __construct($table, $pk, $sortColumn, $sortDirection = \Simplify\Db\QueryObject::ORDER_ASC)
  {
    parent::__construct($table, $pk);

    $this->sortColumn = $sortColumn;
    $this->sortDirection = $sortDirection;
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Repository::findAll()
   */
  public function findAll($params = null)
  {
    $params[\Simplify\Db\QueryParameters::ORDER_BY][] = array($this->sortColumn, $this->sortDirection);
    return parent::findAll($params);
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Repository::insert()
   */
  public function insert(&$data)
  {
    $table = $this->table;
    $pk = $this->pk;
    $sort = $this->sortColumn;

    if (isset($data[$sort])) {
      $new = $data[$sort];

      \Simplify::db()->query("UPDATE $table SET $sort = $sort + 1 WHERE {$this->filter()} AND $sort >= $new")->execute();
    }
    else {
      $max = \Simplify::db()->query()->from($table)->select("MAX($sort)")->where($this->filter())->execute()->fetchOne();

      $data[$sort] = $max + 1;
    }

    return parent::insert($data);
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Repository::update()
   */
  public function update(&$data)
  {
    $table = $this->table;
    $pk = $this->pk;
    $sort = $this->sortColumn;

    if (isset($data[$sort])) {
      $id = $data[$pk];

      $old = \Simplify::db()->query()->from($table)->select("$sort")->where($this->filter())->where("$pk = ?")->execute($id)->fetchOne();

      $new = $data[$sort];

      if ($new > $old) {
        \Simplify::db()->query("UPDATE $table SET $sort = $sort - 1 WHERE {$this->filter()} AND $sort BETWEEN $old AND $new")->execute();
      }
      elseif ($new < $old) {
        \Simplify::db()->query("UPDATE $table SET $sort = $sort + 1 WHERE {$this->filter()} AND $sort BETWEEN $new AND $old")->execute();
      }
    }

    return parent::update($data);
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Repository::delete()
   */
  public function delete($id = null, $params = array())
  {
    $table = $this->table;
    $pk = $this->pk;
    $sort = $this->sortColumn;

    $old = \Simplify::db()->query()->from($table)->select("$sort")->where($this->filter())->where("$pk = ?")->execute($id)->fetchOne();

    \Simplify::db()->query("UPDATE $table SET $sort = $sort - 1 WHERE {$this->filter()} AND $sort > $old")->execute();

    return parent::delete($id, $params);
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Repository::deleteAll()
   */
  public function deleteAll($params = null)
  {
    $rows = 0;

    $data = \Simplify::db()->query()->from($this->table)->setParams($params)->select(false)->select($this->pk)->execute()->fetchCol();

    foreach ($data as $id) {
      $rows += $this->delete($id);
    }

    return $rows;
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Db\SortableInterface::moveTo()
   */
  public function moveTo($id, $position)
  {
    if (!is_numeric($position) &&
       !in_array($position, array('top', 'up', 'down', 'bottom', 'first', 'left', 'right', 'last', 'previous', 'next'))) {
      return;
    }

    $pos = \Simplify::db()->query()->from($this->table)->select("IFNULL($this->sortColumn, 0)")->where($this->filter())->where(
      "{$this->pk} = :{$this->pk}")->execute(array($this->pk => $id))->fetchOne();

    $q = \Simplify::db()->query()->from($this->table)->select($this->pk)->where($this->filter());

    if (is_numeric($position)) {
      if ($pos > $position) {
        $data = $q->where("IFNULL($this->sortColumn, 0) BETWEEN $position AND $pos")->where("$this->pk != $id")->execute()->fetchCol();
        $dif = -1;
        $dis = -count($data);
      }
      else {
        $data = $q->where("IFNULL($this->sortColumn, 0) BETWEEN $pos AND $position")->where("$this->pk != $id")->execute()->fetchCol();
        $dif = 1;
        $dis = count($data);
      }
    }
    else {
      switch ($position) {
        case 'top' :
        case 'first' :
          $data = $q->where("IFNULL($this->sortColumn, 0) <= $pos AND $this->pk != $id")->execute()->fetchCol();
          $dif = -1;
          $dis = -count($data);
          break;

        case 'up' :
        case 'left' :
        case 'previous' :
          $dif = -1;
          $dis = -1;
          $data = $q->where(
            "(IFNULL($this->sortColumn, 0) = $pos - 1 OR IFNULL($this->sortColumn, 0) = $pos) AND $this->pk != $id")->orderBy(
            "IFNULL($this->sortColumn, 0) DESC")->execute()->fetchCol();
          break;

        case 'down' :
        case 'right' :
        case 'next' :
          $dif = 1;
          $dis = 1;
          $data = $q->where(
            "(IFNULL($this->sortColumn, 0) = $pos + 1 OR IFNULL($this->sortColumn, 0) = $pos) AND $this->pk != $id")->orderBy(
            "IFNULL($this->sortColumn, 0) ASC")->execute()->fetchCol();
          break;

        case 'bottom' :
        case 'last' :
          $data = $q->where("IFNULL($this->sortColumn, 0) >= $pos AND $this->pk != $id")->execute()->fetchCol();
          $dif = 1;
          $dis = count($data);
          break;
      }
    }

    if (!empty($data)) {
      $sql = "UPDATE $this->table SET $this->sortColumn = GREATEST(0, IFNULL($this->sortColumn, 0) - $dif) WHERE {$this->filter()} AND $this->pk IN (" .
         implode(', ', $data) . ")";
      \Simplify::db()->query($sql)->execute();
    }

    if ($pos + $dis != $pos) {
      $sql = "UPDATE $this->table SET $this->sortColumn = GREATEST(0, IFNULL($this->sortColumn, 0) + $dis) WHERE {$this->filter()} AND $this->pk = $id";
      \Simplify::db()->query($sql)->execute();
    }
  }

  /**
   *
   * @return string
   */
  protected function filter()
  {
    if ($this->sortGroupColumn) {
      if (! \Simplify::request()->get()->has($this->sortGroupColumn)) {
        throw new \Exception("Missing sort group column value");
      }

      $value = \Simplify::request()->get($this->sortGroupColumn);
      $value = \Simplify::db()->quote($value);

      $filter = " {$this->sortGroupColumn} = {$value} ";

      return $filter;
    }

    return ' TRUE ';
  }

}
