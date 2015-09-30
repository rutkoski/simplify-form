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
namespace Simplify\Form\Element;

use Simplify\Db\QueryParameters;

/**
 * Form date and time element
 */
class DatetimeRange extends \Simplify\Form\Element
{

    /**
     *
     * @var string
     */
    public $beginField;

    /**
     *
     * @var string
     */
    public $endField;

    /**
     * Default range in seconds
     *
     * @var int
     */
    public $defaultRange = 3600;

    /**
     *
     * @return string
     */
    public function getBeginField()
    {
        if (empty($this->beginField)) {
            $this->beginField = $this->getFieldName() . '_begin';
        }
        return $this->beginField;
    }

    /**
     *
     * @return string
     */
    public function getEndField()
    {
        if (empty($this->endField)) {
            $this->endField = $this->getFieldName() . '_end';
        }
        return $this->endField;
    }

    /**
     * (non-PHPdoc)
     *
     * @see Component::getValue()
     */
    public function getValue($data)
    {
        return array(
            'begin' => sy_get_param($data, $this->getBeginField(), $this->getDefaultValue()),
            'end' => sy_get_param($data, $this->getEndField(), $this->getDefaultValue())
        );
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Simplify\Form\Element::onRender()
     */
    public function onRender(\Simplify\Form\Action $action, $data, $index)
    {
        $value = $this->getValue($data);
        
        $this->set('formatedBeginValue', \Simplify\Form\DateTime::datetime($value['begin']));
        $this->set('formatedEndValue', \Simplify\Form\DateTime::datetime($value['end']));
        $this->set('defaultRange', $this->defaultRange);
        
        return parent::onRender($action, $data, $index);
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Simplify\Form\Element::onPostData()
     */
    public function onPostData(\Simplify\Form\Action $action, &$data, $post)
    {
        $begin = $post[$this->getName()]['begin'];
        $end = $post[$this->getName()]['end'];
        
        $data[$this->getBeginField()] = \Simplify\Form\DateTime::database($begin);
        $data[$this->getEndField()] = \Simplify\Form\DateTime::database($end);
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Simplify\Form\Element::getDisplayValue()
     */
    public function getDisplayValue(\Simplify\Form\Action $action, $data, $index)
    {
        $value = $this->getValue($data);
        
        return \Simplify\Form\DateTime::datetime($value['begin']) . ' - ' . \Simplify\Form\DateTime::datetime($value['end']);
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Simplify\Form\Component::getDefaultValue()
     */
    public function getDefaultValue()
    {
        if (empty($this->defaultValue)) {
            $this->defaultValue = 'now';
        }
        return \Simplify\Form\DateTime::database($this->defaultValue);
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Simplify\Form\Element::onInjectQueryParams()
     */
    public function onInjectQueryParams(\Simplify\Form\Action $action, &$params)
    {
        $params[QueryParameters::SELECT][] = $this->getBeginField();
        $params[QueryParameters::SELECT][] = $this->getEndField();
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Simplify\Form\Element::onLoadData()
     */
    public function onLoadData(\Simplify\Form\Action $action, &$data, $row)
    {
        $data[$this->getBeginField()] = sy_get_param($row, $this->getBeginField());
        $data[$this->getEndField()] = sy_get_param($row, $this->getEndField());
    }

    /**
     * (non-PHPdoc)
     *
     * @see Component::onCollectTableData()
     */
    public function onCollectTableData(\Simplify\Form\Action $action, &$row, $data)
    {
        $row[$this->getBeginField()] = sy_get_param($data, $this->getBeginField());
        $row[$this->getEndField()] = sy_get_param($data, $this->getEndField());
    }
}
