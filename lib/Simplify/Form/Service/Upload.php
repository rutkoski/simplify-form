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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Rodrigo Rutkoski Rodrigues <rutkoski@gmail.com>
 */
namespace Simplify\Form\Service;

use Simplify;
use Simplify\Form\Service;

class Upload extends Service
{

  /**
   *
   * @var string
   */
  public $path;

  /**
   * Validation errors
   *
   * @var string[]
   */
  protected $uploadErrors;

  /**
   */
  public function __construct()
  {
    parent::__construct();
    
    $this->path = \Simplify::config()->get('files_path');
  }

  /**
   * (non-PHPdoc)
   * 
   * @see \Simplify\Form\Service::onExecuteServices()
   */
  public function onExecuteServices($serviceAction)
  {
    switch ($serviceAction) {
      case 'upload' :
        $this->upload();
        break;
    }
  }

  /**
   *
   * @return string
   */
  public function upload()
  {
    $post = $this->form->getPostData();
    $name = $this->getName();
    
    if (! empty($post[$name]['file']['name'])) {
      try {
        $upload = new \Simplify\Upload($post[$name]['file']);
        $upload->uploadPath = $this->path;
        $upload->hashFilename = true;
        $upload->upload();
        
        return $upload->getUploadedPath();
      }
      catch (\Simplify\UploadException $e) {
        $this->uploadErrors[] = $e;
      }
      catch (\Simplify\ValidationException $e) {
        $this->uploadErrors[] = $e;
      }
    }
  }

}
