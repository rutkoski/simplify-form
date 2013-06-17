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
 * Form element for multiple image upload
 *
 */
class Simplify_Form_Element_Images extends Simplify_Form_Element_Base_HasMany
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
   * @var array[]
   */
  protected $fields = array();

  /**
   *
   * @var int
   */
  protected $remove = Simplify_Form::ACTION_NONE;

  /**
   *
   * @param unknown_type $name
   * @param unknown_type $label
   */
  public function __construct($name, $label = null)
  {
    parent::__construct($name, $label);

    $this->path = s::config()->get('files_path');
  }

  /**
   *
   * @param string $fieldName
   * @param Simplify_Thumb $thumb
   */
  public function addField($fieldName, Simplify_Thumb $thumb = null)
  {
    $this->fields[] = array(
      self::FIELD_FILENAME => $fieldName,
      self::FIELD_THUMB => $thumb,
    );
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Element::getDisplayValue()
   */
  public function getDisplayValue(Simplify_Form_Action $action, $data, $index)
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
      catch (Simplify_ThumbException $e) {
        //
      }
    }

    return implode(' ', $output);
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Element_Base_HasMany::onBeforeLoadData()
   */
  protected function onBeforeLoadData(&$queryParams)
  {
    foreach ($this->fields as $field) {
      $queryParams[Simplify_Db_QueryParameters::SELECT][] = $field[self::FIELD_FILENAME];
    }
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Element_Base_HasMany::onAfterLoadData()
   */
  protected function onAfterLoadData(&$data, $row, $index)
  {
    foreach ($this->fields as $field) {
      $data[$field[self::FIELD_FILENAME]] = $row[$field[self::FIELD_FILENAME]];
    }
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Element_Base_HasMany::onAfterSave()
   */
  protected function onAfterSave(Simplify_Form_Action $action, &$data, $deleted)
  {
    foreach ($deleted as $row) {
      $this->onDelete($row);
    }
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Element_Base_HasMany::onAfterDelete()
   */
  public function onAfterDelete(Simplify_Form_Action $action, &$data)
  {
    foreach ($data[$this->getName()] as $row) {
      $this->onDelete($row);
    }

    parent::onAfterDelete($action, $data);
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Element_Base_HasMany::onRenderRow()
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
   * @see Simplify_Form_Element::onPostData()
   */
  public function onPostData(Simplify_Form_Action $action, &$data, $post)
  {
    parent::onPostData($action, $data, $post);

    $id = $data[Simplify_Form::ID];

    if (!empty($post[$this->getName()])) {
      foreach ($post[$this->getName()] as $index => $row) {
        $data[$this->getName()][$index][$this->fields[0][self::FIELD_FILENAME]] = $row['filename'];
      }
    }
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Element_Base_HasMany::onBeforeSave()
   */
  public function onBeforeSave(Simplify_Form_Action $action, &$row, $data)
  {
    foreach ($this->fields as $field) {
      $row[$field[self::FIELD_FILENAME]] = $data[$field[self::FIELD_FILENAME]];
    }
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_ElementTable::onRender()
   */
  public function onRender(Simplify_Form_Action $action, $data, $index)
  {
    $this->set('uploaderId', $this->getElementId($index) . '-uploader');
    $this->set('uploaderUrl', $this->getServiceUrl(Simplify_Form::SERVICE_UPLOAD));

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
        $data = array();

        $file = s::request()->files($this->getName());

        try {
          $upload = new Simplify_Upload($file);
          $upload->uploadPath = $this->path;
          $upload->upload();

          $file = $upload->getUploadedPath();

          $image['filename'] = $file;
          $image['thumbUrl'] = $this->getThumbUrl($file, 128, 128);
          $image['imageUrl'] = $this->getImageUrl($file);

          $data['success'] = true;
          $data['image'] = $image;
        }
        catch (Simplify_UploadException $e) {
          $data['error'] = $e->getErrors();
        }

        $view = $this->getView(Simplify_View::JSON);
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
    foreach ($this->fields as $field) {
      $file = $data[$field[self::FIELD_FILENAME]];

      if (!empty($file)) {
        $this->getThumbComponent($file)->cleanCached();

        $file = $this->path . $file;

        if (!sy_path_is_absolute($file)) {
          $file = s::config()->get('www_dir') . $file;
        }

        if (file_exists($file)) {
          $this->getThumbComponent($file)->cleanCached();

          @unlink($file);
        }
      }
    }
  }

  /**
   * Get the thumb component
   *
   * @param string $file filename
   * @return Simplify_Thumb
   */
  protected function getThumbComponent($file)
  {
    return Simplify_Thumb::factory()->load($file);
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
    return s::config()->get('www_url') .
       $this->getThumbComponent($file)->zoomCrop($width, $height)->cache()->getCacheFilename();
  }

  /**
   * Get the url for the full image
   *
   * @param string $file
   * @return string
   */
  protected function getImageUrl($file)
  {
    return s::config()->get('www_url') . $this->path . $file;
  }

  /**
   * Check if the file exists
   *
   * @param string $file
   * @return string
   */
  protected function fileExists($file)
  {
    $file = s::config()->get('www_dir') . $this->path . $file;
    return !empty($file) && file_exists($file) && !is_dir($file);
  }

}
