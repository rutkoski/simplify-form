<?php

class Simplify_Form_Sortable extends Simplify_Form
{

  const ACTION_SORT = 'sort';

  /**
   *
   * @var string
   */
  public $sortField;

  /**
   *
   *
   */
  public function execute($action = null)
  {

    $Action = $this->getAction($action);

    /**
     *
     * order
     *
     */

    if ($Action->show(Simplify_Form::ACTION_LIST)) {
      $listAction = s::request()->get('listAction');

      if ($listAction == self::ACTION_SORT) {
        $id = s::request()->get(Simplify_Form::ID);
        $index = s::request()->get('index');

        $this->repository()->moveTo($id, $index);
      }
    }

    return parent::execute($action);
  }

  /**
   *
   * @return string
   */
  public function getSortField()
  {
    if (empty($this->sortField)) {
      $this->sortField = Inflector::singularize($this->getTable()) . '_order';
    }

    return $this->sortField;
  }

  public function onCreateItemMenu(Simplify_Menu $menu, Simplify_Form_Action $action, $row)
  {
    if ($action->show(Simplify_Form::ACTION_LIST)) {
      $moveMenu = new Simplify_Menu(null, null, Simplify_Menu::STYLE_DROPDOWN);
      $moveMenu->addItem(new Simplify_MenuItem('move_top', 'First', 'arrow_top', $this->url()->extend(null, array('listAction' => self::ACTION_SORT, 'index' => 'first', Simplify_Form::ID => $row[Simplify_Form::ID]))));
      $moveMenu->addItem(new Simplify_MenuItem('move_up', 'Previous', 'arrow_up', $this->url()->extend(null, array('listAction' => self::ACTION_SORT, 'index' => 'previous', Simplify_Form::ID => $row[Simplify_Form::ID]))));
      $moveMenu->addItem(new Simplify_MenuItem('move_down', 'Next', 'arrow_down', $this->url()->extend(null, array('listAction' => self::ACTION_SORT, 'index' => 'next', Simplify_Form::ID => $row[Simplify_Form::ID]))));
      $moveMenu->addItem(new Simplify_MenuItem('move_bottom', 'Last', 'arrow_bottom', $this->url()->extend(null, array('listAction' => self::ACTION_SORT, 'index' => 'last', Simplify_Form::ID => $row[Simplify_Form::ID]))));

      $moveItem = new Simplify_MenuItem('move', 'Move', null, null, $moveMenu);

      $menu->addItem(new Simplify_Menu('sortable', array($moveItem), Simplify_Menu::STYLE_BUTTON_GROUP));
    }

    parent::onCreateItemMenu($menu, $action, $row);
  }

  /**
   *
   * @return IRepository
   */
  public function repository(IRepository $repository = null)
  {
    if ($repository instanceof IRepository) {
      $this->repository = $repository;
    }

    if (empty($this->repository)) {
      $this->repository = new Simplify_Form_RepositorySortable($this->getTable(), $this->getPrimaryKey(), $this->getSortField());
    }

    return $this->repository;
  }

}
