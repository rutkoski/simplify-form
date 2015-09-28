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
 * File upload form element
 *
 */
class File extends \Simplify\Form\Element
{

  /**
   *
   * @var boolean
   */
  public $required = false;

  /**
   *
   * @var string
   */
  public $path;

  /**
   * Always add the object to these actions
   *
   * @var int
   */
  protected $add = \Simplify\Form::ACTION_DELETE;

  /**
   * Validation errors
   *
   * @var string[]
   */
  protected $uploadErrors;

  /**
   *
   * @param unknown_type $name
   * @param unknown_type $label
   */
  public function __construct($name, $label = null)
  {
    parent::__construct($name, $label);

    $this->path = \Simplify::config()->get('files:path');
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Element::onRender()
   */
  public function onRender(\Simplify\Form\Action $action, $data, $index)
  {
    $file = $this->getValue($data);

    if (!empty($file)) {
      if ($this->fileExists($file)) {
        $fileUrl = $this->getFileUrl($file);
      }
      else {
        $fileUrl = false;
      }
    }

    $this->set('fileUrl', $fileUrl);

    return parent::onRender($action, $data, $index);
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Element::getDisplayValue()
   */
  public function getDisplayValue(\Simplify\Form\Action $action, $data, $index)
  {
    $file = $this->getValue($data);

    $value = '';

    if (!empty($file)) {
      if ($this->fileExists($file)) {
        $fileUrl = $this->getFileUrl($file);

        $value = "<a href=\"{$fileUrl}\">{$file}</a>";
      }
      else {
        $value = '<i class="icon-warning-sign"></i> File is missing';
      }
    }

    return $value;
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Element::onPostData()
   */
  public function onPostData(\Simplify\Form\Action $action, &$data, $post)
  {
    $name = $this->getName();

    $file = $this->getValue($data);

    if (!empty($file)) {
      if (!$this->fileExists($file) || $post[$name]['delete']) {
        $this->onDelete($data);

        $data[$this->getName()] = '';
      }
    }

    if (!empty($post[$name]['name']) || $this->required) {
      try {
        $upload = new \Simplify\Upload($post[$name]);
        $upload->uploadPath = $this->path;
        $upload->hashFilename = true;
        $upload->upload();

        $this->onDelete($data);

        $data[$this->getName()] = $upload->getUploadedPath();
      }
      catch (\Simplify\UploadException $e) {
        $this->uploadErrors[] = $e;
      }
      catch (\Simplify\ValidationException $e) {
        $this->uploadErrors[] = $e;
      }
    }
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Element::onValidate()
   */
  public function onValidate(\Simplify\Form\Action $action, $data)
  {
    $rule = new \Simplify\Validation\Callback(array($this, 'validate'));
    $rule->validate($this->getValue($data));
  }

  /**
   * Throw validation errors if there are any
   *
   * @param string $value
   */
  public function validate($value)
  {
    if (!empty($this->uploadErrors)) {
      $error = array_shift($this->uploadErrors);
      throw $error;
    }
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Component::onBeforeDelete()
   */
  public function onBeforeDelete(\Simplify\Form\Action $action, &$data)
  {
    $this->onDelete($data);
  }

  /**
   * Tries to delete the image file and all related cached thumbnails
   *
   * @param array $data form row
   */
  protected function onDelete(&$data)
  {
    $file = $data[$this->getFieldName()];

    if (!empty($file)) {
      if (!sy_path_is_absolute($file)) {
        $file = \Simplify::config()->get('www:dir') . $file;
      }

      if (file_exists($file)) {
        @unlink($file);
      }
    }
  }

  /**
   * Get the url for the file
   *
   * @param string $file
   * @return string
   */
  protected function getFileUrl($file)
  {
    return \Simplify::config()->get('www:url') . $file;
  }

  /**
   * Check if the file exists
   *
   * @param string $file
   * @return string
   */
  protected function fileExists($file)
  {
    $file = \Simplify::config()->get('www:dir') . $file;
    return ! empty($file) && file_exists($file) && ! is_dir($file);
  }

}
