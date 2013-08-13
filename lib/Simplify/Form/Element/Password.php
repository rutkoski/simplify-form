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

/**
 *
 * Form password element with confirmation
 *
 */
class Simplify_Form_Element_Password extends Simplify_Form_Element
{

  /**
   *
   * @var unknown_type
   */
  public $hashCallback = 'md5';

  /**
   * Password is required for existing records
   * If false, it is only required for new records
   *
   * @var boolean
   */
  public $required = false;

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
   * @param string $name
   * @param string $label
   */
  public function __construct($name, $label = null)
  {
    parent::__construct($name, $label);

    $this->remove = Simplify_Form::ACTION_VIEW ^ Simplify_Form::ACTION_LIST;
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::getLabel()
   */
  public function getLabel()
  {
    if (empty($this->label)) {
      $this->label = $this->matchOriginal ? 'Current password' : 'Password';
    }
    return $this->label;
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Element::onRenderControls()
   */
  public function onRenderControls(Simplify_Form_Action $action, &$line, $data, $index)
  {
    $element = array();

    if ($this->matchOriginal) {
      $label = __('Current password');
    } else {
      $label = $action->show(Simplify_Form::ACTION_CREATE) ? __('Password') : __('New password');
    }

    $element['id'] = $this->getElementId($index);
    $element['name'] = $this->getInputName($index);
    $element['class'] = $this->getElementClass();
    $element['label'] = $label;
    $element['controls'] = '<input type="password" name="'.$this->getInputName($index).'[a]" value="" />';

    $element['state'] = $this->state;
    $element['stateMessage'] = $this->stateMessage;

    $line['elements'][$this->getName() . '_a'] = $element;

    if ($this->askForConfirmation) {
      $element = array();

      $element['id'] = $this->getElementId($index);
      $element['name'] = $this->getInputName($index);
      $element['class'] = $this->getElementClass();
      $element['label'] = 'Repeat password';
      $element['controls'] = '<input type="password" name="'.$this->getInputName($index).'[b]" value="" />';

      $element['state'] = $this->state;

      $line['elements'][$this->getName() . '_b'] = $element;
    }
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::onRender()
   */
  public function onRender(Simplify_Form_Action $action, $data, $index)
  {
    $this->set('askForConfirmation', $this->askForConfirmation);

    return parent::onRender($action, $data, $index);
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Element::getDisplayValue()
   */
  public function getDisplayValue(Simplify_Form_Action $action, $row, $index)
  {
    return '';
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Element::onPostData()
   */
  public function onPostData(Simplify_Form_Action $action, &$data, $post)
  {
    $a = $this->hash(sy_get_param(sy_get_param($post, $this->getName(), array()), 'a'));
    $b = $this->hash(sy_get_param(sy_get_param($post, $this->getName(), array()), 'b'));
    $c = $this->hash(sy_get_param(sy_get_param($post, $this->getName(), array()), 'c'));

    $empty = $this->hash('');
    $exists = (!empty($data[Simplify_Form::ID]));
    $required = ($this->required || !$exists);

    if ($this->askForConfirmation && $a != $b) {
      $this->errors['_'][] = $this->getError('match', __('Passwords do not match'));
    }
    elseif ($required && $a == $empty) {
      $this->errors['_'][] = $this->getError('empty', __('Inform your password'));
    }
    elseif ($this->matchOriginal) {
      if ($a != $data[$this->getName()]) {
        $this->errors['_'][] = $this->getError('original', __('Wrong password'));
      }
    }
    elseif ($a != $empty) {
      $data[$this->getName()] = $a;
    }
    else {
      unset($data[$this->getName()]);
    }
  }

  /**
   * On validate callback. Validate component value.
   *
   * @param Simplify_Form_Action $action current action
   * @param Simplify_Validation_DataValidation $rules data validation rules
   */
  public function onValidate(Simplify_Form_Action $action, Simplify_Validation_DataValidation $rules)
  {
    parent::onValidate($action, $rules);

    $rules->setRule($this->getName(), new Simplify_Validation_Callback(array($this, 'validate')));
  }

  /**
   * Throws validation exception for validation that failed during onPostData
   *
   * @param string $value
   * @throws Simplify_ValidationException
   */
  public function validate($value)
  {
    if (!empty($this->errors['_'])) {
      $error = array_shift($this->errors['_']);

      if (!empty($error)) {
        throw new Simplify_ValidationException($error);
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
