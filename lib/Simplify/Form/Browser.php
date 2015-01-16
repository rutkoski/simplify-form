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

namespace Simplify\Form;

/**
 *
 * File browser
 *
 */
class Browser extends \Simplify\Renderable
{

  /**
   *
   * @var string
   */
  const ACTION_SELECT = 'select';

  /**
   * File extensions
   *
   * @var string[]
   */
  public $extensions = array('jpg', 'jpeg', 'png', 'gif');

  /**
   *
   * @var string
   */
  protected $errors;

  /**
   *
   * @var string
   */
  public $path = '/files/media';

  /**
   *
   * @var string
   */
  protected $file;

  /**
   *
   * @var string
   */
  protected $template = 'browser';

  /**
   *
   */
  public function getErrors()
  {
    return $this->errors;
  }

  /**
   * Get the url for the full image
   *
   * @param string $file
   * @return string
   */
  public function getFileUrl()
  {
    return \Simplify::config()->get('www_url') . $this->file;
  }

  /**
   * Get the url for the full image
   *
   * @param string $file
   * @return string
   */
  protected function getFilePath($absolute = false)
  {
    return ($absolute ? \Simplify::config()->get('www_dir') : '') . $this->file;
  }

  /**
   *
   * @param string $name the key in the $_FILES array
   * @return string the uploaded file path
   */
  public function upload($name)
  {
    try {
      $file = \Simplify::request()->files($name);

      $upload = new \Simplify\Upload($file);
      $upload->uploadPath = $this->path;
      $upload->upload();

      $this->file = $this->path . $upload->getUploadedPath();

      return \Simplify\Form::RESULT_SUCCESS;
    }
    catch (\Simplify\UploadException $e) {
      $this->errors = $e->getErrors();
    }

    return \Simplify\Form::RESULT_ERROR;
  }

  /**
   *
   * @return \Simplify\ViewInterface
   */
  public function browse()
  {
    $browserAction = \Simplify::request()->get('browserAction');

    switch ($browserAction) {
      case self::ACTION_SELECT :
        $this->file = \Simplify::request()->get('file');

        return \Simplify\Form::RESULT_SUCCESS;
    }

    $files = $this->findFiles();

    $this->set('files', $files);

    $this->setTemplate('browser');
    $this->setLayout('basic');

    return $this->getView();
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Renderable::getLayoutsPath()
   */
  public function getLayoutsPath()
  {
    $path = (array) parent::getLayoutsPath();
    $path[] = AMP_DIR . '/templates/layouts';
    $path[] = FORM_DIR . '/templates';
    return $path;
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Renderable::getTemplatesPath()
   */
  public function getTemplatesPath()
  {
    $path = array();
    $path[] = \Simplify::config()->get('templates_dir') . '/form';
    $path[] = FORM_DIR . '/templates';
    return $path;
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
  protected function getThumbUrl($file, $width = null, $height = null)
  {
    if (empty($width) && empty($height)) {
      return \Simplify::config()->get('www_url') . $file;
    }

    return \Simplify::config()->get('www_url') .
       $this->getThumbComponent($file)->zoomCrop($width, $height)->cache()->getCacheFilename();
  }

  /**
   *
   * @return array
   */
  protected function findFiles()
  {
    $baseUrl = \Simplify::config()->get('www_url');
    $baseDir = \Simplify::config()->get('www_dir');

    $files = glob($baseDir . $this->path . '/' . '*.{' . implode(',', $this->extensions) . '}',
      GLOB_BRACE);

    $selectUrl = \Simplify\URL::make(null, null, true)->set('browserAction', self::ACTION_SELECT);

    foreach ($files as &$file) {
      $file = substr($file, strlen($baseDir));

      $file = array(
        'filename' => $file,
        'url' => $baseUrl . $file,
        'selectUrl' => $selectUrl->extend()->set('file', $file),
        'thumbUrl' => $this->getThumbUrl($file, 128, 128),
        'imageUrl' => $this->getThumbUrl($file)
      );
    }

    return $files;
  }

}
