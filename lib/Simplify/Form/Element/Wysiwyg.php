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
 * WYSIWYG form element
 *
 */
class Wysiwyg extends \Simplify\Form\Element
{

  const SERVICE_BROWSER = 'browser';

  /**
   *
   * @var \Simplify\Form\Browser
   */
  protected $browser;

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Element::getDisplayValue()
   */
  public function getDisplayValue(\Simplify\Form\Action $action, $data, $index)
  {
    $value = parent::getDisplayValue($action, $data, $index);
    $value = strip_tags($value);
    $value = sy_truncate($value, 80);

    return $value;
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Element::onRender()
   */
  public function onRender(\Simplify\Form\Action $action, $data, $index)
  {
    $wysiwygOptions = array();
    $wysiwygOptions['uploaderUrl'] = $this->getServiceUrl(\Simplify\Form::SERVICE_UPLOAD)->build();
    $wysiwygOptions['browserUrl'] = $this->getServiceUrl(self::SERVICE_BROWSER)->format(false)->build();

    $this->set('wysiwygOptions', json_encode($wysiwygOptions));

    return parent::onRender($action, $data, $index);
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Component::onExecuteServices()
   */
  public function onExecuteServices($serviceAction)
  {
    switch ($serviceAction) {
      case \Simplify\Form::SERVICE_UPLOAD :
        $funcNum = \Simplify::request()->get('CKEditorFuncNum');
        //$CKEditor = \Simplify::request()->get('CKEditor');
        //$langCode = \Simplify::request()->get('langCode');


        $fileUrl = false;
        $message = false;

        $response = $this->getBrowser()->upload('upload');

        if ($response === \Simplify\Form::RESULT_SUCCESS) {
          $fileUrl = $this->getBrowser()->getFileUrl();
        } else {
          $message = $this->getBrowser()->getErrors();
        }

        echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction({$funcNum}, '{$fileUrl}', '{$message}');</script>";

        exit();

      case self::SERVICE_BROWSER :
        $response = $this->getBrowser()->browse();

        if ($response === \Simplify\Form::RESULT_SUCCESS) {
          $funcNum = \Simplify::request()->get('CKEditorFuncNum');
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
   * @see \Simplify\Form\Element::onPostData()
   */
  public function onPostData(\Simplify\Form\Action $action, &$data, $post)
  {
    parent::onPostData($action, $data, $post);

    $data[$this->getName()] = $this->makeUrlsRelative($data[$this->getName()]);
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Element::onLoadData()
   */
  public function onLoadData(\Simplify\Form\Action $action, &$data, $row)
  {
    parent::onLoadData($action, $data, $row);

    if (isset($data[$this->getFieldName()])) {
      $data[$this->getName()] = $this->makeUrlsAbsolute($data[$this->getName()]);
    }
  }

  /**
   *
   * @return \Simplify\Form\Browser
   */
  protected function getBrowser()
  {
    if (empty($this->browser)) {
      $this->browser = new \Simplify\Form\Browser();
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
    $base = \Simplify::config()->get('www:url');

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
    $base = \Simplify::config()->get('www:url');

    $value = preg_replace('# src="(?!(' . preg_quote($base) . '|http))#', ' src="' . $base, $value);

    return $value;
  }

}
