<?php

class Simplify_Form_RepositorySimplify_FormRepositoryMptt extends Simplify_Form_Repository implements Simplify_Db_SortableInterface
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

  public function __construct($table = null, $pk = null, $parent = null, $left = null, $right = null)
  {
    parent::__construct($table, $pk);

    $this->parent = $parent;
    $this->left = $left;
    $this->right = $right;
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Datarepository::findAll()
   */
  public function findAll($params = null)
  {
    $data = sy_get_param($params, 'data');

    $from = $this->mptt()->query()->alias('a');

    $result = s::db()
      ->query()
      ->from($from)
      ->setParams($params)
      ->select('depth')
      ->select($this->parent)
      ->execute($data)
      ->fetchAll();

    return $result;
  }

  /**
   * Insert one row.
   *
   * @param array $data
   */
  public function insert(&$data)
  {
    return $this->mptt()->append($data, sy_get_param($data, $this->parent, 0));
  }

  /**
   * Update one row.
   *
   * @param array $data
   * @return number
   */
  public function update(&$data)
  {
    $result = 0;

    if (count($data) > 1) {
      $row = $this->find($data[$this->pk]);

      $result = s::db()->update($this->table, $data, "$this->pk = :$this->pk")->execute($data)->numRows();

      if ($row[$this->parent] != $data[$this->parent]) {
        $this->mptt()->move($data[$this->pk], $data[$this->parent], MPTT::LAST_CHILD);
      }
    }

    return $result;
  }

  public function moveTo($id, $position)
  {
    if (! is_numeric($position) && ! in_array($position, array('top', 'up', 'down', 'bottom', 'first', 'left', 'right', 'last', 'previous', 'next'))) {
      throw new Exception("Invalid index <b>$position</b>");
    }

    $row = s::db()->query()->from($this->table)->select($this->parent)->select($this->left)->select($this->right)->where("{$this->pk} = ?")->execute($id)->fetchRow();

    if (empty($row)) {
      throw new Exception("Record not found");
    }

    $parent = $row[$this->parent];
    $oldleft = $row[$this->left];
    $oldright = $row[$this->right];
    $oldwidth = $oldright - $oldleft + 1;

    $branch = s::db()->query()->select($this->pk)->from($this->table)->where("$this->left BETWEEN $oldleft AND $oldright")->execute()->fetchCol();
    $branch = implode(', ', $branch);

    $q = s::db()->query()->from($this->table)->select($this->left)->select($this->right)->where("$this->parent = $parent");

    if (is_numeric($position)) {
      $pos = s::db()
        ->query()
        ->from($this->table)
        ->select("COUNT({$this->pk})")
        ->where("{$this->parent} = {$parent}")
        ->where("{$this->left} < {$oldleft}")
        ->execute()->fetchOne();

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

    if (! empty($data)) {
      $olddir = - $dir;

      $sql = "
        UPDATE {$this->table}
        SET {$this->left} = {$this->left} + :width, {$this->right} = {$this->right} + :width
        WHERE {$this->left} BETWEEN :left AND :right
      ";

      s::db()->query($sql)->execute(array('width' => $olddir * $oldwidth, 'left' => $newleft, 'right' => $newright));

      $sql = "
        UPDATE {$this->table}
        SET {$this->left} = {$this->left} + :width, {$this->right} = {$this->right} + :width
        WHERE {$this->pk} IN ({$branch})
      ";

      s::db()->query($sql)->execute(array('width' => $dir * $width));
    }
  }

  /**
   *
   * @return MPTT
   */
  public function mptt()
  {
    return MPTT::getInstance($this->table, $this->pk, $this->parent, $this->left, $this->right);
  }

}
