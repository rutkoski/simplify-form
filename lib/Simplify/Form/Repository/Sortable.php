<?php

class Simplify_Form_Repository_Sortable extends Simplify_Form_Repository implements Simplify_Db_SortableInterface
{

  public $sort;

  public $direction;

  public function __construct($table, $pk, $sort, $direction = Simplify_Db_QueryObject::ORDER_ASC)
  {
    parent::__construct($table, $pk);

    $this->sort = $sort;
    $this->direction = $direction;
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Datarepository::findAll()
   */
  public function findAll($params = null)
  {
    $params['orderBy'][] = array($this->sort, $this->direction);
    return parent::findAll($params);
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Datarepository::insert()
   */
  public function insert(&$data)
  {
    $table = $this->table;
    $pk = $this->pk;
    $sort = $this->sort;

    if (isset($data[$sort])) {
      $new = $data[$sort];

      s::db()->query("UPDATE $table SET $sort = $sort + 1 WHERE {$this->filter()} AND $sort >= $new")->execute();
    }
    else {
      $max = s::db()->query()->from($table)->select("MAX($sort)")->where($this->filter())->execute()->fetchOne();

      $data[$sort] = $max + 1;
    }

    return parent::insert($data);
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Datarepository::update()
   */
  public function update(&$data)
  {
    $table = $this->table;
    $pk = $this->pk;
    $sort = $this->sort;

    if (isset($data[$sort])) {
      $id = $data[$pk];

      $old = s::db()->query()->from($table)->select("$sort")->where($this->filter())->where("$pk = ?")->execute($id)->fetchOne();

      $new = $data[$sort];

      if ($new > $old) {
        s::db()->query("UPDATE $table SET $sort = $sort - 1 WHERE {$this->filter()} AND $sort BETWEEN $old AND $new")->execute();
      }
      elseif ($new < $old) {
        s::db()->query("UPDATE $table SET $sort = $sort + 1 WHERE {$this->filter()} AND $sort BETWEEN $new AND $old")->execute();
      }
    }

    return parent::update($data);
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Datarepository::delete()
   */
  public function delete($id, $params = array())
  {
    $table = $this->table;
    $pk = $this->pk;
    $sort = $this->sort;

    $old = s::db()->query()->from($table)->select("$sort")->where($this->filter())->where("$pk = ?")->execute($id)->fetchOne();

    s::db()->query("UPDATE $table SET $sort = $sort - 1 WHERE {$this->filter()} AND $sort > $old")->execute();

    return parent::delete($id, $params);
  }

  public function moveTo($id, $position)
  {
    if (! is_numeric($position) && ! in_array($position, array('top', 'up', 'down', 'bottom', 'first', 'left', 'right', 'last', 'previous', 'next'))) {
      return;
    }

    $pos = s::db()->query()->from($this->table)->select($this->sort)->where($this->filter())->where("{$this->pk} = :{$this->pk}")->execute(array($this->pk => $id))->fetchOne();

    $q = s::db()->query()->from($this->table)->select($this->pk)->where($this->filter());

    if (is_numeric($position)) {
      if ($pos > $position) {
        $data = $q->where("$this->sort BETWEEN $position AND $pos")->where("$this->pk != $id")->execute()->fetchCol();
        $dif = -1;
        $dis = - count($data);
      }
      else {
        $data = $q->where("$this->sort BETWEEN $pos AND $position")->where("$this->pk != $id")->execute()->fetchCol();
        $dif = 1;
        $dis = count($data);
      }
    }
    else {
      switch ($position) {
        case 'top' :
        case 'first' :
          $data = $q->where("$this->sort <= $pos AND $this->pk != $id")->execute()->fetchCol();
          $dif = -1;
          $dis = - count($data);
          break;

        case 'up' :
        case 'left' :
        case 'previous' :
          $dif = -1;
          $dis = -1;
          $data = $q->where("($this->sort = $pos - 1 OR $this->sort = $pos) AND $this->pk != $id")->orderBy("$this->sort DESC")->execute()->fetchCol();
          break;

        case 'down' :
        case 'right' :
        case 'next' :
          $dif = 1;
          $dis = 1;
          $data = $q->where("($this->sort = $pos + 1 OR $this->sort = $pos) AND $this->pk != $id")->orderBy("$this->sort ASC")->execute()->fetchCol();
          break;

        case 'bottom' :
        case 'last' :
          $data = $q->where("$this->sort >= $pos AND $this->pk != $id")->execute()->fetchCol();
          $dif = 1;
          $dis = count($data);
          break;
      }
    }

    if (! empty($data)) {
      $sql = "UPDATE $this->table SET $this->sort = GREATEST(0, $this->sort - $dif) WHERE {$this->filter()} AND $this->pk IN (".implode(', ', $data).")";
      s::db()->query($sql)->execute();
    }

    if ($pos + $dis != $pos) {
      $sql = "UPDATE $this->table SET $this->sort = GREATEST(0, $this->sort + $dis) WHERE {$this->filter()} AND $this->pk = $id";
      s::db()->query($sql)->execute();
    }
  }

  protected function filter()
  {
    //if (! empty($this->repository->filter)) {
      //return $this->repository->filter;
    //}
    //else {
      return " TRUE ";
    //}
  }

}
