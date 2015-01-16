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
 * MPTT form repository
 *
 */
class Mptt extends \Simplify\Form\Repository implements \Simplify\Db\SortableInterface
{

  /**
   *
   * @var string
   */
  public $parent;

  /**
   *
   * @var string
   */
  public $left;

  /**
   *
   * @var string
   */
  public $right;

  /**
   *
   * @param unknown_type $table
   * @param unknown_type $pk
   * @param unknown_type $parent
   * @param unknown_type $left
   * @param unknown_type $right
   */
  public function __construct($table = null, $pk = null, $parent = null, $left = null, $right = null)
  {
    parent::__construct($table, $pk);

    $this->parent = $parent;
    $this->left = $left;
    $this->right = $right;
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Repository::findAll()
   */
  public function findAll($params = null)
  {
    $from = $this->mptt()->query()->alias('a');

    $result = \Simplify::db()->query()->from($from)->setParams($params)->select('depth')->select($this->parent)->execute()->fetchAll();

    return $result;
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Repository::insert()
   */
  public function insert(&$data)
  {
    return $this->mptt()->append($data, sy_get_param($data, $this->parent, 0));
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Repository::update()
   */
  public function update(&$data)
  {
    $row = $this->find($data[$this->pk]);

    if ($row[$this->parent] != $data[$this->parent]) {
      $this->mptt()->move($data[$this->pk], $data[$this->parent], \Simplify\Db\MPTT::LAST_CHILD);
    }

    return parent::update($data);
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Repository::delete()
   */
  public function delete($id = null, $params = array())
  {
    $data = $this->find($id, $params);

    if (empty($data)) {
      return 0;
    }

    $this->mptt()->remove($id);

    return 1;
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Repository::deleteAll()
   */
  public function deleteAll($params = null)
  {
    $params[\Simplify\Db\QueryParameters::SELECT][] = $this->pk;

    $data = $this->findAll($params);

    if (empty($data)) {
      return 0;
    }

    $rows = 0;

    foreach ($data as $row) {
      $this->mptt()->remove($row[$this->pk]);
    }

    return 1;
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Db\SortableInterface::moveTo()
   */
  public function moveTo($id, $position)
  {
    if (!is_numeric($position) &&
       !in_array($position, array('top', 'up', 'down', 'bottom', 'first', 'left', 'right', 'last', 'previous', 'next'))) {
      throw new \Exception("Invalid index <b>$position</b>");
    }

    $row = \Simplify::db()->query()->from($this->table)->select($this->parent)->select($this->left)->select($this->right)->where(
      "{$this->pk} = ?")->execute($id)->fetchRow();

    if (empty($row)) {
      throw new \Exception("Record not found");
    }

    $parent = $row[$this->parent];
    $oldleft = $row[$this->left];
    $oldright = $row[$this->right];
    $oldwidth = $oldright - $oldleft + 1;

    $branch = \Simplify::db()->query()->select($this->pk)->from($this->table)->where(
      "$this->left BETWEEN $oldleft AND $oldright")->execute()->fetchCol();
    $branch = implode(', ', $branch);

    $q = \Simplify::db()->query()->from($this->table)->select($this->left)->select($this->right)->where(
      "$this->parent = $parent");

    if (is_numeric($position)) {
      $pos = \Simplify::db()->query()->from($this->table)->select("COUNT({$this->pk})")->where("{$this->parent} = {$parent}")->where(
        "{$this->left} < {$oldleft}")->execute()->fetchOne();

      if ($position == $pos) {
        return;
      }

      $data = $q->orderBy($this->left)->offset($position)->limit(1)->execute()->fetchRow();

      if ($position > $pos) {
        $newleft = $oldright + 1;
        $newright = $data[$this->right];

        $width = $newright - $newleft + 1;

        $dir = 1;
      }
      else {
        $newleft = $data[$this->left];
        $newright = $oldleft - 1;

        $width = $oldleft - $newleft;

        $dir = -1;
      }
    }
    else {
      switch ($position) {
        case 'top' :
        case 'first' :
          $data = $q->where("{$this->left} < {$oldleft}")->orderBy($this->left)->limit(1)->execute()->fetchRow();

          $newleft = $data[$this->left];
          $newright = $oldleft - 1;

          $width = $oldleft - $newleft;

          $dir = -1;

          break;

        case 'up' :
        case 'left' :
        case 'previous' :
          $data = $q->where("{$this->left} < {$oldleft}")->orderBy("{$this->left} DESC")->limit(1)->execute()->fetchRow();

          $newleft = $data[$this->left];
          $newright = $oldleft - 1;

          $width = $oldleft - $newleft;

          $dir = -1;

          break;

        case 'down' :
        case 'right' :
        case 'next' :
          $data = $q->where("{$this->left} > {$oldleft}")->orderBy($this->left)->limit(1)->execute()->fetchRow();

          $newleft = $data[$this->left];
          $newright = $data[$this->right];

          $width = $newright - $newleft + 1;

          $dir = 1;

          break;

        case 'bottom' :
        case 'last' :
          $data = $q->where("{$this->left} > {$oldleft}")->orderBy("$this->left DESC")->limit(1)->execute()->fetchRow();

          $newleft = $oldright + 1;
          $newright = $data[$this->right];

          $width = $newright - $newleft + 1;

          $dir = 1;

          break;
      }
    }

    if (!empty($data)) {
      $olddir = -$dir;

      $sql = "
        UPDATE {$this->table}
        SET {$this->left} = {$this->left} + :width, {$this->right} = {$this->right} + :width
        WHERE {$this->left} BETWEEN :left AND :right
      ";

      \Simplify::db()->query($sql)->execute(array('width' => $olddir * $oldwidth, 'left' => $newleft, 'right' => $newright));

      $sql = "
        UPDATE {$this->table}
        SET {$this->left} = {$this->left} + :width, {$this->right} = {$this->right} + :width
        WHERE {$this->pk} IN ({$branch})
      ";

      \Simplify::db()->query($sql)->execute(array('width' => $dir * $width));
    }
  }

  /**
   * 
   * @return \Simplify\Db\Mptt
   */
  public function mptt()
  {
    return \Simplify\Db\MPTT::getInstance($this->table, $this->pk, $this->parent, $this->left, $this->right);
  }

  public function createRepository()
  {
    $create = '
      CREATE TABLE `%1$s` (
      	`%2$s` MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
      	`%3$s` MEDIUMINT(8) UNSIGNED NOT NULL,
      	`%4$s` MEDIUMINT(8) UNSIGNED NOT NULL,
      	`%5$s` MEDIUMINT(8) UNSIGNED NOT NULL,
      	PRIMARY KEY (`%2$s`),
      	INDEX `tree_parent_id` (`%3$s`),
      	INDEX `tree_left_id` (`%4$s`),
      	INDEX `tree_right_id` (`%5$s`)
      )
      COLLATE=\'utf8_general_ci\'
      ENGINE=InnoDB
    ';

    $sql = sprintf($create, $this->table, $this->pk, $this->parent, $this->left, $this->right);

    if (\Simplify::db()->query($sql)->executeRaw() === false) {
      throw new \Simplify\Db\DatabaseException('Could not create table');
    }
  }

}
