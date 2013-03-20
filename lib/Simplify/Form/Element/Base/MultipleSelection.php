<?php

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

  public function onExecute(Simplify_Form_Action $action)
  {
    parent::onExecute($action);
  }

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

  public function toggleValue($pid, $fid)
  {
    $t = $this->getTable();
    $pk = $this->getPrimaryKey();
    $fk = $this->getForeignKey();
    $at = $this->getAssociationTable();
    $apk = $this->getAssociationPrimaryKey();
    $afk = $this->getAssociationForeignKey();

    $data = array($apk => $pid, $afk => $fid);

    $found = s::db()->query()->from($at)->select("COUNT(*)")
      ->where("{$apk} = :{$apk} AND {$afk} = :{$afk}")->execute($data)->fetchOne();

    if (empty($found)) {
      s::db()->insert($at, $data)->execute($data);
    } else {
      s::db()->delete($at, "{$apk} = :{$apk} AND {$afk} = :{$afk}")->execute($data);
    }

    return ! $found;
  }

  public function onRender(Simplify_Form_Action $action, $row, $index)
  {
    $this->set('options', $this->getOptions($row));

    return parent::onRender($action, $row, $index);
  }

  public function onPostData(&$row, $data)
  {
    $row[$this->getName()] = (array) sy_get_param($data, $this->getName());
  }

  public function onCollectTableData(&$row, $data)
  {
  }

  public function onSave($row)
  {
    $id = $row[Simplify_Form::ID];

    $options = $this->getOptions($row);

    $a = $options[1];

    $b = $row[$this->getName()];

    $add = array_diff($b, $a);
    $rem = array_diff($a, $b);

    $at = $this->getAssociationTable();
    $apk = $this->getAssociationPrimaryKey();
    $afk = $this->getAssociationForeignKey();

    foreach ($add as $_id) {
      $data = array(
        $apk => $id,
        $afk => $_id
      );

      s::db()->insert($at, $data)->execute($data);
    }

    foreach ($rem as $_id) {
      $data = array(
        $apk => $id,
        $afk => $_id
      );

      s::db()->delete($at, "{$apk} = :{$apk} AND {$afk} = :{$afk}")->execute($data);
    }
  }

  public function onInjectQueryParams(&$params)
  {
  }

  public function getDisplayValue(Simplify_Form_Action $action, $data, $index)
  {
    return $this->onRender($action, $data, $index)->render();
  }

  public function onLoadData(&$row, $data, $index)
  {
  }

  public function getOptions($row)
  {
    $t = $this->getTable();
    $pk = $this->getPrimaryKey();
    $fk = $this->getForeignKey();
    $at = $this->getAssociationTable();
    $apk = $this->getAssociationPrimaryKey();
    $afk = $this->getAssociationForeignKey();

    $data = s::db()->query()
      ->select("{$t}.{$fk}")
      ->select("{$t}.{$this->labelField}")
      ->select("{$at}.{$afk} AS checked")
      ->from($t)
      ->leftJoin("{$at} ON ({$t}.{$fk} = {$at}.{$afk} AND {$at}.{$apk} = :{$pk})")
      ->execute(array($pk => sy_get_param($row, Simplify_Form::ID)))
      ->fetchAll();

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
