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
 * WYSIWYG form element
 *
 */
class Simplify_Form_Element_Wysiwyg extends Simplify_Form_Element
{

  const SERVICE_BROWSER = 'browser';

  /**
   *
   * @var Simplify_Form_Browser
   */
  protected $browser;

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Element::getDisplayValue()
   */
  public function getDisplayValue(Simplify_Form_Action $action, $data, $index)
  {
    $value = parent::getDisplayValue($action, $data, $index);
    $value = strip_tags($value);
    $value = sy_truncate($value, 80);

    return $value;
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_ElementTable::onRender()
   */
  public function onRender(Simplify_Form_Action $action, $data, $index)
  {
    $this->set('uploaderUrl', $this->getServiceUrl(Simplify_Form::SERVICE_UPLOAD));
    $this->set('browserUrl', $this->getServiceUrl(self::SERVICE_BROWSER)->format(false));

    return parent::onRender($action, $data, $index);
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::onExecuteServices()
   */
  public function onExecuteServices(Simplify_Form_Action $action, $serviceAction)
  {
    switch ($serviceAction) {
      case Simplify_Form::SERVICE_UPLOAD :
        $funcNum = s::request()->get('CKEditorFuncNum');
        //$CKEditor = s::request()->get('CKEditor');
        //$langCode = s::request()->get('langCode');


        $fileUrl = false;
        $message = false;

        $response = $this->getBrowser()->upload('upload');

        if ($response === Simplify_Form::RESULT_SUCCESS) {
          $fileUrl = $this->getBrowser()->getFileUrl();
        } else {
          $message = $this->getBrowser()->getErrors();
        }

        echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction({$funcNum}, '{$fileUrl}', '{$message}');</script>";

        exit();

      case self::SERVICE_BROWSER :
        $response = $this->getBrowser()->browse();

        if ($response === Simplify_Form::RESULT_SUCCESS) {
          $funcNum = s::request()->get('CKEditorFuncNum');
          $fileUrl = $this->getBrowser()->getFileUrl();
          echo "<script type='text/javascript'>window.opener.CKEDITOR.tools.callFunction({$funcNum}, '{$fileUrl}');window.close();</script>";
        } else {
          echo $response;
        }

        exit();
    }
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::onPostData()
   */
  public function onPostData(Simplify_Form_Action $action, &$data, $post)
  {
    parent::onPostData($action, $data, $post);

    $data[$this->getName()] = $this->makeUrlsRelative($data[$this->getName()]);
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::onLoadData()
   */
  public function onLoadData(Simplify_Form_Action $action, &$data, $row)
  {
    parent::onLoadData($action, $data, $row);

    if (isset($data[$this->getFieldName()])) {
      $data[$this->getName()] = $this->makeUrlsAbsolute($data[$this->getName()]);
    }
  }

  /**
   *
   * @return Simplify_Form_Browser
   */
  protected function getBrowser()
  {
    if (empty($this->browser)) {
      $this->browser = new Simplify_Form_Browser();
    }
    return $this->browser;
  }

  /**
   *
   * @param string $value
   * @return string
   */
  protected function makeUrlsRelative($value)
  {
    $base = s::config()->get('www_url');

    $value = preg_replace('# src="' . preg_quote($base) . '#', ' src="', $value);

    return $value;
  }

  /**
   *
   * @param string $value
   * @return string
   */
  protected function makeUrlsAbsolute($value)
  {
    $base = s::config()->get('www_url');

    $value = preg_replace('# src="(?!(' . preg_quote($base) . '|http))#', ' src="' . $base, $value);

    return $value;
  }

}
