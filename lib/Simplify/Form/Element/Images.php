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
 * Form element for multiple image upload
 *
 */
class Images extends \Simplify\Form\Element\Base\HasMany
{

  const FIELD_FILENAME = 0;

  const FIELD_THUMB = 1;

  /**
   * Path to image files, absolute or relative to web root
   *
   * @var string
   */
  public $path;

  /**
   *
   * @var boolean
   */
  public $deleteFile = false;
  
  /**
   *
   * @var array[]
   */
  protected $fields = array();

  /**
   *
   * @var int
   */
  protected $remove = \Simplify\Form::ACTION_NONE;

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
   *
   * @param string $fieldName
   * @param \Simplify\Thumb $thumb
   */
  public function addField($fieldName, \Simplify\Thumb $thumb = null)
  {
    $this->fields[] = array(
      self::FIELD_FILENAME => $fieldName,
      self::FIELD_THUMB => $thumb,
    );
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Element::getDisplayValue()
   */
  public function getDisplayValue(\Simplify\Form\Action $action, $data, $index)
  {
    $files = $this->getValue($data);

    $output = array();
    foreach ((array) $files as $file) {
      try {
        $file = $file[$this->fields[0][self::FIELD_FILENAME]];

        $imageUrl = $this->getImageUrl($file);
        $thumbUrl = $this->getThumbUrl($file, 50, 50);

        $value = "<a href=\"{$imageUrl}\" class=\"lightbox\">";
        $value .= "<img src=\"{$thumbUrl}\" class=\"img-polaroid\"/>";
        $value .= "</a>";

        $output[] = $value;
      }
      catch (\Simplify\ThumbException $e) {
        //
      }
    }

    return implode(' ', $output);
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Element\Base\HasMany::onBeforeLoadData()
   */
  protected function onBeforeLoadData(&$queryParams)
  {
    foreach ($this->fields as $field) {
      $queryParams[\Simplify\Db\QueryParameters::SELECT][] = $field[self::FIELD_FILENAME];
    }
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Element\Base\HasMany::onAfterLoadData()
   */
  protected function onAfterLoadData(&$data, $row, $index)
  {
    foreach ($this->fields as $field) {
      $data[$field[self::FIELD_FILENAME]] = $row[$field[self::FIELD_FILENAME]];
    }
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Element\Base\HasMany::onAfterSave()
   */
  protected function onAfterSave(\Simplify\Form\Action $action, &$data, $deleted)
  {
    foreach ($deleted as $row) {
      $this->onDelete($row);
    }
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Element\Base\HasMany::onAfterDelete()
   */
  public function onAfterDelete(\Simplify\Form\Action $action, &$data)
  {
    foreach ($data[$this->getName()] as $row) {
      $this->onDelete($row);
    }

    parent::onAfterDelete($action, $data);
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Element\Base\HasMany::onRenderRow()
   */
  public function onRenderRow(&$row, $data, $index)
  {
    $file = $data[$this->fields[0][self::FIELD_FILENAME]];

    $row['filename'] = $file;

    $imageUrl = false;
    $thumbUrl = false;

    if ($this->fileExists($file)) {
      $imageUrl = $this->getImageUrl($file);
      $thumbUrl = $this->getThumbUrl($file, 128, 128);
    }

    $row['imageUrl'] = $imageUrl;
    $row['thumbUrl'] = $thumbUrl;
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Element\Base\HasMany::onPostData()
   */
  public function onPostData(\Simplify\Form\Action $action, &$data, $post)
  {
    parent::onPostData($action, $data, $post);

    $id = $data[\Simplify\Form::ID];

    if (!empty($post[$this->getName()])) {
      foreach ($post[$this->getName()] as $index => $row) {
        foreach ($this->fields as $field) {
          $data[$this->getName()][$index][$field[self::FIELD_FILENAME]] = $row['filename'];
        }
      }
    }
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Element\Base\HasMany::onBeforeSave()
   */
  public function onBeforeSave(\Simplify\Form\Action $action, &$row, $data)
  {
    foreach ($this->fields as $field) {
      $row[$field[self::FIELD_FILENAME]] = $data[$field[self::FIELD_FILENAME]];
    }
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Element\Base\HasMany::onRender()
   */
  public function onRender(\Simplify\Form\Action $action, $data, $index)
  {
    $this->set('uploaderId', $this->getElementId($index) . '-uploader');
    $this->set('uploaderUrl', $this->getServiceUrl(\Simplify\Form::SERVICE_UPLOAD));

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
        $data = array();

        $file = \Simplify::request()->files($this->getName());

        try {
          $upload = new \Simplify\Upload($file);
          $upload->uploadPath = $this->path;
          $upload->upload();

          $file = $upload->getUploadedPath();

          $image['filename'] = $file;
          $image['thumbUrl'] = $this->getThumbUrl($file, 128, 128);
          $image['imageUrl'] = $this->getImageUrl($file);

          $data['success'] = true;
          $data['image'] = $image;
        }
        catch (\Simplify\UploadException $e) {
          $data['error'] = $e->getErrors();
        }

        $view = $this->getView(\Simplify\View::JSON);
        $view->copyAll($data);
        echo $view->render();
        exit();
        break;
    }
  }

  /**
   * Tries to delete the image file and all related cached thumbnails
   *
   * @param array $data form row
   */
  protected function onDelete(&$data)
  {
    if ($this->deleteFile) {
      foreach ($this->fields as $field) {
        $file = $data[$field[self::FIELD_FILENAME]];

        if (!empty($file)) {
          $this->getThumbComponent($file)->cleanCached();
  
          if (!sy_path_is_absolute($file)) {
            $file = \Simplify::config()->get('www:dir') . $file;
          }
  
          if (file_exists($file)) {
            $this->getThumbComponent($file)->cleanCached();
  
            @unlink($file);
          }
        }
      }
    }
  }

  /**
   * Get the thumb component
   *
   * @param string $file filename
   * @return \Simplify\Thumb
   */
  protected function getThumbComponent($file)
  {
    return \Simplify\Thumb::factory()->load($file);
  }
  
  /**
   * Get the url for a zoom cropped version of the imagem
   *
   * @param string $file
   * @param int $width
   * @param int $height
   * @return string
   */
  protected function getThumbUrl($file, $width, $height)
  {
    try {
      return \Simplify::config()->get('www:url') .
      \Simplify\Thumb::factory()->load($file)->zoomCrop($width, $height)->cache()->getCacheFilename();
    }
    catch (\Simplify\ThumbException $e) {
      //
    }
  }
  
  /**
   * Get the url for the full image
   *
   * @param string $file
   * @return string
   */
  protected function getImageUrl($file)
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
