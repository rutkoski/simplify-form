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
 * Form password element with confirmation
 *
 */
class Password extends \Simplify\Form\Element
{

  /**
   *
   * @var unknown_type
   */
  public $hashCallback = array('\Simplify\Password', 'hash');

  /**
   * Password is required for existing records
   * If false, it is only required for new records
   *
   * @var boolean
   */
  public $required = true;

  /**
   *
   * @var boolean
   */
  public $askForConfirmation = true;

  /**
   *
   * @var boolean
   */
  public $matchOriginal = false;

  /**
   *
   * @var boolean
   */
  public $exists = null;
  
  /**
   *
   * @param string $name
   * @param string $label
   */
  public function __construct($name, $label = null)
  {
    parent::__construct($name, $label);

    $this->remove = \Simplify\Form::ACTION_VIEW ^ \Simplify\Form::ACTION_LIST;
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Component::getLabel()
   */
  public function getLabel()
  {
    if (empty($this->label)) {
      $this->label = $this->matchOriginal ? __('Senha atual') : __('Senha');
    }
    return $this->label;
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Element::onRenderControls()
   */
  public function onRenderControls(\Simplify\Form\Action $action, &$line, $data, $index)
  {
    $element = array();

    if ($this->matchOriginal) {
      $label = __('Current password');
    } else {
      $label = $action->show(\Simplify\Form::ACTION_CREATE) || $this->exists === false ? __('Senha') : __('Nova senha');
    }

    $this->set('inputNameSufix', '[a]');
    
    $element['id'] = $this->getElementId($index);
    $element['name'] = $this->getInputName($index);
    $element['class'] = $this->getElementClass();
    $element['label'] = $label;
    $element['controls'] = $this->onRender($action, $data, $index)->render();

    $element['state'] = $this->state;
    $element['stateMessage'] = $this->stateMessage;

    $line['elements'][$this->getName() . '_a'] = $element;

    if ($this->askForConfirmation) {
      $element = array();

      $this->set('inputNameSufix', '[b]');
      
      $element['id'] = $this->getElementId($index);
      $element['name'] = $this->getInputName($index);
      $element['class'] = $this->getElementClass();
      $element['label'] = __('Confirmação de senha');
      $element['controls'] = $this->onRender($action, $data, $index)->render();

      $element['state'] = $this->state;

      $line['elements'][$this->getName() . '_b'] = $element;
    }
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Element::onRender()
   */
  public function onRender(\Simplify\Form\Action $action, $data, $index)
  {
    $this->set('askForConfirmation', $this->askForConfirmation);

    return parent::onRender($action, $data, $index);
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Element::getDisplayValue()
   */
  public function getDisplayValue(\Simplify\Form\Action $action, $row, $index)
  {
    return '';
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Element::onPostData()
   */
  public function onPostData(\Simplify\Form\Action $action, &$data, $post)
  {
    $a = sy_get_param(sy_get_param($post, $this->getName(), array()), 'a');
    $b = sy_get_param(sy_get_param($post, $this->getName(), array()), 'b');
    $c = sy_get_param(sy_get_param($post, $this->getName(), array()), 'c');

    $empty = '';
    $exists = (!empty($data[\Simplify\Form::ID])) || $this->exists === true;
    $required = ($this->required && !$exists);

    if ($this->askForConfirmation && $a != $b) {
      $this->errors['_'][] = $this->getError('match', __('As senhas não conferem'));
    }
    elseif ($required && $a == $empty) {
      $this->errors['_'][] = $this->getError('empty', __('Informe a senha'));
    }
    elseif ($this->matchOriginal) {
      if ($a != $data[$this->getName()]) {
        $this->errors['_'][] = $this->getError('original', __('Senha incorreta'));
      }
    }
    elseif ($a != $empty) {
      $data[$this->getName()] = $this->hash($a);
    }
    else {
      unset($data[$this->getName()]);
    }
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Element::onValidate()
   */
  public function onValidate(\Simplify\Form\Action $action, $data)
  {
    parent::onValidate($action, $data);

    $rule = new \Simplify\Validation\Callback(array($this, 'validate'));
    $rule->validate($this->getValue($data));
  }

  /**
   * Throws validation exception for validation that failed during onPostData
   *
   * @param string $value
   * @throws \Simplify\ValidationException
   */
  public function validate($value)
  {
    if (!empty($this->errors['_'])) {
      $error = array_shift($this->errors['_']);

      if (!empty($error)) {
        throw new \Simplify\ValidationException($error);
      }
    }
  }

  /**
   * Hash the password
   *
   * @param string $s
   * @return string
   */
  protected function hash($s)
  {
    return $this->hashCallback ? call_user_func($this->hashCallback, $s) : $s;
  }
  
}
