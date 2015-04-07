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

/**
 *
 * Multiple selection checkboxes
 *
 */
class Checkboxes extends \Simplify\Form\Element\Base\MultipleSelection
{

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Element::onRender()
   */
  public function onRender(\Simplify\Form\Action $action, $data, $index)
  {
    $params = array(
        'formAction' => 'services', 
        'serviceName' => $this->getName(), 
        'serviceAction' => 'toggle', 
        \Simplify\Form::ID => $data[\Simplify\Form::ID]
    );

    $this->set('ajaxUrl', \Simplify\URL::make(null, $params)->format(\Simplify\URL::JSON));
    $this->set('jsName', str_replace(array('[', ']'), array('\\\[', '\\\]'), $this->getInputName($index) . '[]'));
    $this->set('useAjax', $action->show(\Simplify\Form::ACTION_LIST));

    return parent::onRender($action, $data, $index);
  }

}
