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
namespace Simplify\Form\Element;

use Simplify\Form\Action;

/**
 * Form element for currency
 */
class Currency extends \Simplify\Form\Element
{

    public $prefix = 'R$ ';

    public $suffix = '';
    
    public $decimalSeparator = ',';

    public $thousandsSeparator = '.';
    
    public $precision = 2;

    /**
     * Get the display value for the element.
     *
     * @param Action $action
     *            current action
     * @param array $data
     *            form data
     * @param mixed $index            
     * @return string the display value
     */
    public function getDisplayValue(Action $action, $data, $index)
    {
        $value = sy_get_param($data, $this->getName());
        $value = number_format(floatval($value), 2, $this->decimalSeparator, $this->thousandsSeparator);
        $value = $this->prefix . $value . $this->suffix;
        
        return $value;
    }

    /**
     * (non-PHPdoc)
     * 
     * @see Component::getValue()
     */
    public function getValue($data)
    {
        $value = sy_get_param($data, $this->getName(), $this->getDefaultValue());
        
        $value = number_format(floatval($value), 2);
        
        return $value;
    }

    /**
     * (non-PHPdoc)
     * 
     * @see \Simplify\Form\Element::onRender()
     */
    public function onRender(Action $action, $data, $index)
    {
        $this->set('prefix', $this->prefix);
        $this->set('suffix', $this->suffix);
        $this->set('decimalSeparator', $this->decimalSeparator);
        $this->set('thousandsSeparator', $this->thousandsSeparator);
        $this->set('precision', $this->precision);
        
        return parent::onRender($action, $data, $index);
    }

    /**
     * (non-PHPdoc)
     * 
     * @see \Simplify\Form\Element::onPostData()
     */
    public function onPostData(Action $action, &$data, $post)
    {
        $value = sy_get_param($post, $this->getName(), $this->getDefaultValue());
        $value = str_replace($this->thousandsSeparator, '', $value);
        $value = str_replace($this->decimalSeparator, '.', $value);
        $value = floatval($value);
        
        $data[$this->getName()] = $value;
    }
}
