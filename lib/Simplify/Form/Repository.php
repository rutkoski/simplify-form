<?php

class Simplify_Form_Repository implements Simplify_Db_RepositoryInterface
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
   * Constructor.
   *
   * return void
   */
  public function __construct($table = null, $pk = null)
  {
    $this->table = $table;
    $this->pk = $pk;
  }

  /**
   *
   * @param int $offset
   * @param int $limit
   * @return Pager
   */
  public function findPager($params = null)
  {
    $limit = $params['limit'];
    $offset = $params['offset'];

    return new Simplify_Pager($this->findCount($params), $limit, $offset);
  }

  /**
   * (non-PHPdoc)
   * @see IRepository::find()
   */
  public function find($id = null, $params = null)
  {
    $query = s::db()->query()->setParams($params)->from($this->table)->where($this->filter())->where("$this->pk = :$this->pk")->limit(1);

    $data = (array) sy_get_param($params, 'data');
    $data[$this->pk] = $id;

    $result = $query->execute($data)->fetchRow();

    return $result;
  }

  /**
   * (non-PHPdoc)
   * @see IRepository::findAll()
   */
  public function findAll($params = null)
  {
    $query = s::db()->query()->from($this->table)->where($this->filter())->setParams($params);

    $result = $query->execute(sy_get_param($params, 'data'))->fetchAll();

    return $result;
  }

  /**
   * (non-PHPdoc)
   * @see IRepository::findCount()
   */
  public function findCount($params = null)
  {
    $query = s::db()->query()->setParams($params)->where($this->filter())->from($this->table)->select(false)->limit(false)->offset(false)->select("COUNT($this->pk)");
    $result = $query->execute(sy_get_param($params, 'data'))->fetchOne();
    return intval($result);
  }

  /**
   * (non-PHPdoc)
   * @see IRepository::delete()
   */
  public function delete($id = null, $params = array())
  {
    $result = s::db()->delete($this->table, "$this->pk = ?")->execute($id);

    return $result->numRows();
  }

  /**
   * (non-PHPdoc)
   * @see IRepository::deleteAll()
   */
  public function deleteAll($params = null)
  {
    $result = s::db()->delete($this->table)->setParams($params)->execute(sy_get_param($params, 'data'));

    return $result->numRows();
  }

  /**
   * (non-PHPdoc)
   * @see IRepository::save()
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
   * Insert one row.
   *
   * @param array $data
   */
  public function insert(&$data)
  {
    s::db()->insert($this->table, $data)->execute($data);

    $data[$this->pk] = s::db()->lastInsertId();
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
      $result = s::db()->update($this->table, $data, "$this->pk = :$this->pk")->execute($data)->numRows();
    }

    return $result;
  }

  protected function filter()
  {
    if (! empty($this->filter)) {
      return $this->filter;
    }
    else {
      return " TRUE ";
    }
  }

}
