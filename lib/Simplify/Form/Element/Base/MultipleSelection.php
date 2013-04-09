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

/**
 *
 * Base class for miltiple selection elements
 *
 */
abstract class Simplify_Form_Element_Base_MultipleSelection extends Simplify_Form_Element
{

  /**
   *
   * @var string
   */
  public $associationTable;

  /**
   *
   * @var string
   */
  public $associationPrimaryKey;

  /**
   *
   * @var string
   */
  public $associationForeignKey;

  /**
   *
   * @var string
   */
  public $table;

  /**
   *
   * @var string
   */
  public $primaryKey;

  /**
   *
   * @var string
   */
  public $foreignKey;

  /**
   *
   * @var string
   */
  public $labelField;

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::onExecuteServices()
   */
  public function onExecuteServices(Simplify_Form_Action $action, $serviceAction)
  {
    parent::onExecuteServices($action, $serviceAction);

    switch ($serviceAction) {
      case 'toggle' :
        $pid = $this->form->getId();
        $fid = s::request()->post($this->getName());

        $this->set('value', $this->toggleValue($pid, $fid));

        break;
    }

    return $this->getView();
  }

  /**
   *
   * @param unknown_type $pid
   * @param unknown_type $fid
   * @return boolean
   */
  public function toggleValue($pid, $fid)
  {
    $t = $this->getTable();
    $pk = $this->getPrimaryKey();
    $fk = $this->getForeignKey();
    $at = $this->getAssociationTable();
    $apk = $this->getAssociationPrimaryKey();
    $afk = $this->getAssociationForeignKey();

    $data = array($apk => $pid, $afk => $fid);

    $found = s::db()->query()->from($at)->select("COUNT(*)")->where("{$apk} = :{$apk} AND {$afk} = :{$afk}")->execute(
      $data)->fetchOne();

    if (empty($found)) {
      s::db()->insert($at, $data)->execute($data);
    }
    else {
      s::db()->delete($at, "{$apk} = :{$apk} AND {$afk} = :{$afk}")->execute($data);
    }

    return !$found;
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Element::onRender()
   */
  public function onRender(Simplify_Form_Action $action, $data, $index)
  {
    $this->set('options', $this->getOptions($data));

    return parent::onRender($action, $data, $index);
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::onPostData()
   */
  public function onPostData(Simplify_Form_Action $action, &$data, $post)
  {
    $data[$this->getName()] = (array) sy_get_param($post, $this->getName());
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Element::onCollectTableData()
   */
  public function onCollectTableData(&$row, $data)
  {
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::onSave()
   */
  public function onSave(Simplify_Form_Action $action, &$data)
  {
    $id = $data[Simplify_Form::ID];

    $options = $this->getOptions($data);

    $a = $options[1];

    $b = $data[$this->getName()];

    $add = array_diff($b, $a);
    $rem = array_diff($a, $b);

    $at = $this->getAssociationTable();
    $apk = $this->getAssociationPrimaryKey();
    $afk = $this->getAssociationForeignKey();

    foreach ($add as $_id) {
      $data = array($apk => $id, $afk => $_id);

      s::db()->insert($at, $data)->execute($data);
    }

    foreach ($rem as $_id) {
      $data = array($apk => $id, $afk => $_id);

      s::db()->delete($at, "{$apk} = :{$apk} AND {$afk} = :{$afk}")->execute($data);
    }
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Element::onInjectQueryParams()
   */
  public function onInjectQueryParams(Simplify_Form_Action $action, &$params)
  {
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Element::getDisplayValue()
   */
  public function getDisplayValue(Simplify_Form_Action $action, $data, $index)
  {
    return $this->onRender($action, $data, $index)->render();
  }

  /**
   *
   * @param array $data
   * @return multitype:multitype:Ambigous <unknown, ArrayAccess>  multitype:unknown
   */
  public function getOptions($data)
  {
    $t = $this->getTable();
    $pk = $this->getPrimaryKey();
    $fk = $this->getForeignKey();
    $at = $this->getAssociationTable();
    $apk = $this->getAssociationPrimaryKey();
    $afk = $this->getAssociationForeignKey();

    $q = s::db()->query()->select("{$t}.{$fk}")->select("{$t}.{$this->labelField}")->select("{$at}.{$afk} AS checked")->from(
      $t)->leftJoin("{$at} ON ({$t}.{$fk} = {$at}.{$afk} AND {$at}.{$apk} = :{$pk})");

    $data = $q->execute(array($pk => $data[Simplify_Form::ID]))->fetchAll();

    $options = sy_array_to_options($data, $fk, $this->labelField);

    $checked = array();
    foreach ($data as $row) {
      if ($row['checked']) {
        $checked[] = $row[$fk];
      }
    }

    return array($options, $checked);
  }

  public function getTable()
  {
    return $this->table;
  }

  public function getPrimaryKey()
  {
    if (empty($this->primaryKey)) {
      $this->primaryKey = $this->form->getPrimaryKey();
    }

    return $this->primaryKey;
  }

  public function getForeignKey()
  {
    if (empty($this->foreignKey)) {
      $this->foreignKey = Inflector::singularize($this->getTable()) . '_id';
    }

    return $this->foreignKey;
  }

  public function getAssociationTable()
  {
    if (empty($this->associationTable)) {
      $this->associationTable = implode('_', array($this->form->getTable(), $this->getTable()));
    }

    return $this->associationTable;
  }

  public function getAssociationPrimaryKey()
  {
    if (empty($this->associationPrimaryKey)) {
      $this->associationPrimaryKey = $this->getPrimaryKey();
    }

    return $this->associationPrimaryKey;
  }

  public function getAssociationForeignKey()
  {
    if (empty($this->associationForeignKey)) {
      $this->associationForeignKey = $this->getForeignKey();
    }

    return $this->associationForeignKey;
  }

}
