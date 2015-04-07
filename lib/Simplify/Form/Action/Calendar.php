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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Rodrigo Rutkoski Rodrigues <rutkoski@gmail.com>
 */
namespace Simplify\Form\Action;

use Simplify\Form\Action\Base\FormBase;
use Simplify;
use Simplify\Db\QueryParameters;
use Simplify\Form;
use Simplify\Form\Action;
use Simplify\Menu;
use Simplify\MenuItem;

/**
 * Form action list
 */
class Calendar extends Action
{

  public $startDate;

  public $endData;

  public $titleField = 'title';

  public $startDateField = 'start';

  public $endDateField = 'end';

  public $allDayField = false;

  /**
   *
   * @var int
   */
  protected $actionMask = Form::ACTION_LIST;

  public function setFields($title, $start, $end, $allDay = false)
  {
    $this->titleField = $title;
    $this->startDateField = $start;
    $this->endDateField = $end;
    $this->allDayField = $allDay;
  }

  public function getStartDate()
  {
    if (empty($this->startDate)) {
      $this->startDate = date('Y-m-d', \Simplify::request()->get('start', mktime(0, 0, 0, date('m'), 1)));
    }
    
    return $this->startDate;
  }

  public function getEndDate()
  {
    if (empty($this->endDate)) {
      $this->endDate = date('Y-m-d', \Simplify::request()->get('end', mktime(0, 0, 0, date('m') + 1, - 1)));
    }
    
    return $this->endDate;
  }

  /**
   * (non-PHPdoc)
   *
   * @see Simplify\Form\Action::onExecute()
   */
  public function onExecute()
  {
    parent::onExecute();
    
    $calendarAction = \Simplify::request()->get('calendarAction');
    
    switch ($calendarAction) {
      case 'data' :
        $this->onLoadData();
        
        \Simplify::response()->output($this);
        
        exit();
      
      case 'create' :
      case 'edit' :
        $this->onLoadData();
        
        if (\Simplify::request()->method('post')) {
          $this->onPostData();
          $this->onValidate();
          $this->onSave();
        }
        
        break;
      
      case 'delete' :
        $this->onLoadData();
        $this->onValidate();
        $this->onDelete();
    }
  }

  /**
   * (non-PHPdoc)
   *
   * @see Simplify\Form\Action::onRender()
   */
  public function onRender()
  {
    $calendarAction = \Simplify::request()->get('calendarAction');
    
    switch ($calendarAction) {
      case 'create' :
      case 'edit' :
        return $this->renderForm();
      
      default :
        return $this->renderCalendar();
    }
  }

  protected function renderCalendar()
  {
    $options = array();
    $options['dataUrl'] = $this->url()->set('calendarAction', 'data')->format('json')->build();
    
    $options['createUrl'] = $this->url()->set('calendarAction', 'create')->build();
    
    $this->set('calendarOptions', json_encode($options));
    
    return parent::onRender();
  }

  protected function renderForm()
  {
    $elements = $this->getElements();
    
    $data = array();
    foreach ($this->formData as $index => $row) {
      $line = array();
      $line['name'] = Form::ID . "[]";
      $line[Form::ID] = $row[Form::ID];
      $line['elements'] = array();
      $line['index'] = $index;
      
      $elements->rewind();
      
      while ($elements->valid()) {
        $element = $elements->current();
        $element->onRenderControls($this, $line, $this->formData[$index], $index);
        $elements->next();
      }
      
      $data[] = $line;
    }
    
    $this->set('data', $data);
    
    $this->set('showForm', true);
    
    $calendarAction = \Simplify::request()->get('calendarAction');
    
    $this->set('saveUrl', $this->url()->set('calendarAction', $calendarAction)->format('json')->build());
    
    return parent::onRender();
  }

  /**
   * (non-PHPdoc)
   *
   * @see Simplify\Form\Action::onCreateMenu()
   */
  public function onCreateMenu(Menu $menu, Action $action)
  {
    if ($action !== $this) {
      $menu->getItemByName('main')->addItem(new MenuItem($this->getName(), $this->getTitle(), null, $this->url()));
    }
  }

  /**
   * (non-PHPdoc)
   *
   * @see \Simplify\Form\Action::onInjectQueryParams()
   */
  public function onInjectQueryParams(&$params)
  {
    $calendarAction = \Simplify::request()->get('calendarAction');
    
    switch ($calendarAction) {
      case 'create' :
      case 'edit' :
      case 'delete' :
        
        parent::onInjectQueryParams($params);
        
        break;
      
      default :
        $params[QueryParameters::SELECT][] = "{$this->titleField} AS title";
        $params[QueryParameters::SELECT][] = "{$this->startDateField} AS start";
        $params[QueryParameters::SELECT][] = "{$this->endDateField} AS end";
        
        if ($this->allDayField) {
          $params[QueryParameters::SELECT][] = "{$this->allDayField} AS allDay";
        }
        
        $params[QueryParameters::WHERE][] = array(
            "{$this->startDateField} BETWEEN :start AND :end",
            "{$this->endDateField} BETWEEN :start AND :end"
        );
        
        $params[QueryParameters::DATA]['start'] = $this->getStartDate();
        $params[QueryParameters::DATA]['end'] = $this->getEndDate();
    }
  }

  protected function onLoadData()
  {
    $calendarAction = \Simplify::request()->get('calendarAction');
    
    switch ($calendarAction) {
      case 'create' :
        $this->formData = array(
            array(
                Form::ID => null
            )
        );
        break;
      
      default :
        parent::onLoadData();
    }
  }

  /**
   * (non-PHPdoc)
   *
   * @see \Simplify\Form\Action::onExtractData()
   */
  protected function onExtractData(&$data, $row)
  {
    $calendarAction = \Simplify::request()->get('calendarAction');
    
    if ($calendarAction == 'data') {
      $data['title'] = $row['title'];
      $data['start'] = $row['start'];
      $data['end'] = $row['end'];
      $data['allDay'] = sy_get_param($row, 'allDay');
    }
    
    $data['editUrl'] = $this->url()->format('html')->set('calendarAction', 'edit')->set(Form::ID, $data[Form::ID])->build();
    $data['deleteUrl'] = $this->url()->format('html')->set('calendarAction', 'delete')->set(Form::ID, $data[Form::ID])->build();
  }

}
