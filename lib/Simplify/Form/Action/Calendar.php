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

use Simplify\Db\QueryParameters;
use Simplify\Form;
use Simplify\Form\Action;
use Simplify\Menu;
use Simplify\MenuItem;
use Simplify;

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
            case 'data':
                $this->onLoadData();
                $this->expandRecurringEvents();
                
                \Simplify::response()->output($this);
                
                exit();
            
            case 'create':
            case 'edit':
                $this->onLoadData();
                
                if (\Simplify::request()->method('post')) {
                    $this->onPostData();
                    $this->onValidate();
                    $this->onSave();
                }
                
                break;
            
            case 'delete':
                $this->onLoadData();
                $this->onValidate();
                $this->onDelete();
        }
    }

    protected function __deleteChildren()
    {
        $row = $this->formData[0];
        
        $id = sy_get_param($row, Form::ID);
        
        if (! empty($id)) {
            $params = array();
            $params[QueryParameters::WHERE][] = 'cal_parent_id = :id';
            $params[QueryParameters::DATA]['id'] = $id;
            
            $this->repository()->deleteAll($params);
        }
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Simplify\Form\Action::onSave()
     */
    protected function __onSave()
    {
        parent::onSave();
        
        $this->deleteChildren();
        
        $row = $this->formData[0];
        
        $id = $row[Form::ID];
        
        if ($row['cal_repeat'] != 'NEVER') {
            $beginTime = strtotime($row['cal_begin']);
            $endTime = strtotime($row['cal_begin']);
            $count = $row['cal_repeat_end'] == 'AFTER' ? $row['cal_repeat_end_times'] : 0;
            $limit = $row['cal_repeat_end'] == 'DATE' ? strtotime($row['cal_repeat_end_date']) : 0;
            
            $daysOfWeek = $row['repeat_days'];
            
            $events = array();
            
            $event = array();
            $event['cal_parent_id'] = $id;
            
            switch ($row['cal_repeat']) {
                case 'DAY':
                    $interval = 60 * 60 * 24;
                    
                    while (($row['cal_repeat_end'] == 'AFTER' && $count > 1) || $beginTime + $interval <= $limit) {
                        $beginTime += $interval;
                        $endTime += $interval;
                        
                        $w = date('w', $beginTime);
                        
                        if (in_array($w, $daysOfWeek) === false) {
                            continue;
                        }
                        
                        if ($row['cal_repeat_end'] == 'AFTER') {
                            $count --;
                        }
                        
                        $event['cal_begin'] = date('Y-m-d H:i:s', $beginTime);
                        $event['cal_end'] = date('Y-m-d H:i:s', $endTime);
                        
                        $events[] = $event;
                    }
                    
                    break;
                
                case 'MONTH':
                    $beginTime = mktime(date('H', $beginTime), date('i', $beginTime), date('s', $beginTime), intval(date('m', $beginTime)) + 1, date('d', $beginTime), date('Y', $beginTime));
                    $endTime = mktime(date('H', $endTime), date('i', $endTime), date('s', $endTime), intval(date('m', $endTime)) + 1, date('d', $endTime), date('Y', $endTime));
                    
                    while (($row['cal_repeat_end'] == 'AFTER' && $count > 1) || $beginTime <= $limit) {
                        
                        if ($row['cal_repeat_end'] == 'AFTER') {
                            $count --;
                        }
                        
                        $event['cal_begin'] = date('Y-m-d H:i:s', $beginTime);
                        $event['cal_end'] = date('Y-m-d H:i:s', $endTime);
                        
                        $events[] = $event;
                        
                        $beginTime = mktime(date('H', $beginTime), date('i', $beginTime), date('s', $beginTime), intval(date('m', $beginTime)) + 1, date('d', $beginTime), date('Y', $beginTime));
                        $endTime = mktime(date('H', $endTime), date('i', $endTime), date('s', $endTime), intval(date('m', $endTime)) + 1, date('d', $endTime), date('Y', $endTime));
                    }
                    
                    break;
                
                case 'YEAR':
                    $beginTime = mktime(date('H', $beginTime), date('i', $beginTime), date('s', $beginTime), date('m', $beginTime), date('d', $beginTime), intval(date('Y', $beginTime)) + 1);
                    $endTime = mktime(date('H', $endTime), date('i', $endTime), date('s', $endTime), date('m', $endTime), date('d', $endTime), intval(date('Y', $endTime)) + 1);
                    
                    while (($row['cal_repeat_end'] == 'AFTER' && $count > 1) || $beginTime <= $limit) {
                        
                        if ($row['cal_repeat_end'] == 'AFTER') {
                            $count --;
                        }
                        
                        $event['cal_begin'] = date('Y-m-d H:i:s', $beginTime);
                        $event['cal_end'] = date('Y-m-d H:i:s', $endTime);
                        
                        $events[] = $event;
                        
                        $beginTime = mktime(date('H', $beginTime), date('i', $beginTime), date('s', $beginTime), date('m', $beginTime), date('d', $beginTime), intval(date('Y', $beginTime)) + 1);
                        $endTime = mktime(date('H', $endTime), date('i', $endTime), date('s', $endTime), date('m', $endTime), date('d', $endTime), intval(date('Y', $endTime)) + 1);
                    }
                    
                    break;
            }
            
            foreach ($events as $event) {
                $this->repository()->insert($event);
            }
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
            case 'create':
            case 'edit':
                return $this->renderForm();
            
            default:
                return $this->renderCalendar();
        }
    }

    protected function renderCalendar()
    {
        $options = array();
        $options['dataUrl'] = $this->url()
            ->set('calendarAction', 'data')
            ->format('json')
            ->build();
        
        $options['createUrl'] = $this->url()
            ->set('calendarAction', 'create')
            ->set('calendarDate', '__startTime__')
            ->build();
        
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
        
        $this->set('saveUrl', $this->url()
            ->set('calendarAction', $calendarAction)
            ->format('json')
            ->build());
        
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
            $menu->getItemByName('main')->addItem(new MenuItem($this->getName(), $this->getTitle(), 'calendar', $this->url()));
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
            case 'create':
            case 'edit':
            case 'delete':
                
                parent::onInjectQueryParams($params);
                
                break;
            
            default:
                $params[QueryParameters::SELECT][] = "{$this->titleField} AS title";
                $params[QueryParameters::SELECT][] = "{$this->startDateField} AS start";
                $params[QueryParameters::SELECT][] = "{$this->endDateField} AS end";
                $params[QueryParameters::SELECT][] = "cal_repeat";
                $params[QueryParameters::SELECT][] = "cal_repeat_days";
                $params[QueryParameters::SELECT][] = "cal_repeat_end";
                
                // $t = $this->form->getTable();
                
                // $params[QueryParameters::FROM][] = false;
                // $params[QueryParameters::FROM][] = "{$t} AS node";
                // $params[QueryParameters::JOIN][] = array("{$t} AS parent ON (parent.cal_id = node.cal_parent_id)", "LEFT JOIN");
                
                // $params[QueryParameters::SELECT][] = array("cal_id", true);
                // $params[QueryParameters::SELECT][] = "node.cal_id";
                // $params[QueryParameters::SELECT][] = "IFNULL(node.{$this->titleField}, parent.{$this->titleField}) AS title";
                // $params[QueryParameters::SELECT][] = "node.{$this->startDateField} AS start";
                // $params[QueryParameters::SELECT][] = "node.{$this->endDateField} AS end";
                
                if ($this->allDayField) {
                    $params[QueryParameters::SELECT][] = "{$this->allDayField} AS allDay";
                }
                
                // $params[QueryParameters::WHERE][] = array(
                // "node.{$this->startDateField} BETWEEN :start AND :end",
                // "node.{$this->endDateField} BETWEEN :start AND :end"
                // );
                
                $params[QueryParameters::WHERE][] = array(
                    // "{$this->startDateField} BETWEEN :start AND :end",
                    // "{$this->endDateField} BETWEEN :start AND :end",
                    "{$this->startDateField} BETWEEN :start AND :end",
                    "{$this->endDateField} BETWEEN :start AND :end",
                    array(
                        "cal_repeat = 1",
                        "{$this->startDateField} <= :end",
                        "cal_repeat_end > :start"
                    )
                );
                
                $params[QueryParameters::DATA]['start'] = $this->getStartDate();
                $params[QueryParameters::DATA]['end'] = $this->getEndDate();
        }
    }

    protected function onLoadData()
    {
        $calendarAction = \Simplify::request()->get('calendarAction');
        
        switch ($calendarAction) {
            case 'create':
                $this->formData = array(
                    array(
                        Form::ID => null
                    )
                );
                break;
            
            default:
                parent::onLoadData();
        }
    }

    protected function expandRecurringEvents()
    {
        $recur = array();
        foreach ($this->formData as $row) {
            if (! empty($row['cal_repeat'])) {
                $recur[] = $row;
            }
        }
        
        $virtual = array();
        // _pre(\Simplify::db()->log());die;
        foreach ($recur as $event) {
            // _pre($event);
            
            // $begin = max(strtotime($event['start']), strtotime('last month'));
            // _pre(date('Y-m-d', strtotime('end of next month')));
            // _pre($begin);
            
            $begin = strtotime($event['start']);
            $end = strtotime($event['end']);
            
            $repeatEnd = strtotime($event['cal_repeat_end']);
            
            $time = $begin;
            $_time = $end;
            
            while ($time <= $repeatEnd) {
                if (in_array(intval(date('w', $time)), $event['cal_repeat_days'])) {
                    // _pre(date('d/m/Y H:i:s', $time) . ' - ' . date('d/m/Y H:i:s', $_time));
                    
                    $virtual[] = array_merge($event, array(
                        'start' => date('Y-m-d H:i:s', $time),
                        'end' => date('Y-m-d H:i:s', $_time),
                        'virtual' => true
                    ));
                }
                
                $time += (60 * 60 * 24);
                $_time += (60 * 60 * 24);
            }
            
            // _pre(date('Y-m-d', $begin), date('Y-m-d', $end));
        }
        // _pre($virtual);
        $this->formData = array_merge($this->formData, $virtual);
        // _pre($this->formData);
        // die;
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
        
        $data['editUrl'] = $this->url()
            ->format('html')
            ->set('calendarAction', 'edit')
            ->set(Form::ID, $data[Form::ID])
            ->build();
        $data['deleteUrl'] = $this->url()
            ->format('html')
            ->set('calendarAction', 'delete')
            ->set(Form::ID, $data[Form::ID])
            ->build();
    }
}
