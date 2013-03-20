<?php

class Simplify_Form_Element_Images extends Simplify_Form_Element_Base_HasMany
{

  const FIELD_FILENAME = 'filename';

  const FIELD_ORDER = 'order';

  /**
   * @var array
   */
  public $fields = array(self::FIELD_FILENAME => 'image_filename', self::FIELD_ORDER => 'image_order');

  /**
   * @var string
   */
  public $filePath;

  /**
   *
   * @var boolean
   */
  public $sortable = true;

  public function getDisplayValue(Simplify_Form_Action $action, $data, $index)
  {
    $files = $this->getValue($data, $index);

    $output = array();
    foreach ((array) $files as $file) {
      try {
        $file = $file[$this->getFieldName(self::FIELD_FILENAME)];

        $value = s::config()->get('www_url') . Thumb::factory()->load($file)->cropResize(50, 50)->getCacheFilename();

        $output[] = "<img src=\"{$value}\" class=\"img-polaroid\"/>";
      } catch (ThumbException $e) {
        //
      }
    }

    return implode(' ', $output);
  }

  protected function onBeforeFindAll(&$queryParams)
  {
    $queryParams['fields'][] = $this->getFieldName(self::FIELD_FILENAME);

    if ($this->sortable) {
      $queryParams['orderBy'][] = $this->getFieldName(self::FIELD_ORDER);
    }
  }

  protected function onAfterFindAll(&$row, $data, $index)
  {
    $_row = $this->getRow($data, $index);
    $row[$this->getFieldName(self::FIELD_FILENAME)] = $_row[$this->getFieldName(self::FIELD_FILENAME)];
  }

  protected function onAfterPostData(&$row, $data, $index)
  {
    $_row = $this->getRow($data, $index);

    $row[$this->getFieldName(self::FIELD_FILENAME)] = $_row[self::FIELD_FILENAME];

    if ($this->sortable) {
      $row[$this->getFieldName(self::FIELD_ORDER)] = array_search($_row, array_values($data));//$_row[self::FIELD_ORDER];
    }
  }

  public function onBeforeSave(&$row, $data)
  {
    $row[$this->getFieldName(self::FIELD_FILENAME)] = $data[$this->getFieldName(self::FIELD_FILENAME)];

    if ($this->sortable) {
      $row[$this->getFieldName(self::FIELD_ORDER)] = $data[$this->getFieldName(self::FIELD_ORDER)];
    }
  }

  protected function onAfterSave(&$row, $deleted)
  {
    foreach ($deleted as $_row) {
      $this->onDelete($_row);
    }
  }

  protected function onDelete($row)
  {
    $filename = $row[$this->getFieldName(self::FIELD_FILENAME)];

    if (! empty($filename)) {
      $file = s::config()->get('www_dir') . $filename;

      if (file_exists($file)) {
        Thumb::factory()->load($file)->cleanCached();

        @unlink($file);
      }
    }
  }

  public function onRenderRow(&$row, $data, $index)
  {
    $_row = $this->getRow($data, $index);

    $filename = $_row[$this->getFieldName(self::FIELD_FILENAME)];

    $row[$this->getFieldName(self::FIELD_FILENAME)] = $filename;

    $row['image_src'] = s::config()->get('www_url') . $filename;
    $row['thumb_src'] = s::config()->get('www_url') . $this->getThumb($filename, 100, 100);
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_ElementTable::onRender()
   */
  public function onRender(Simplify_Form_Action $action, $data, $index)
  {
    $this->set('filenameField', $this->getFieldName(self::FIELD_FILENAME));
    $this->set('orderField', $this->getFieldName(self::FIELD_ORDER));
    $this->set('uploaderUrl', Simplify_URL::make(null, array('formAction' => 'services', 'serviceName' => $this->getName(), 'serviceAction' => 'upload'), false, null, Simplify_URL::JSON));
    $this->set('sortable', $this->sortable);

    return parent::onRender($action, $data, $index);
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::onExecuteServices()
   */
  public function onExecuteServices(Simplify_Form_Action $action, $serviceAction)
  {
    switch ($serviceAction) {
      case 'delete' :
        $pk = $this->getPrimaryKey();

        $id = $this->form->getId();

        $image = s::db()->query()->from($this->getTable())->where(Simplify_Db_QueryObject::buildIn($pk, $id))->execute(array($pk => $id))->fetchRow();

        $filename = $this->getFilesDir() . $image[$this->getFieldName('filename')];

        @unlink($filename);

        s::db()->delete($this->getTable(), Simplify_Db_QueryObject::buildIn($pk, $id))->execute(array($pk => $id));

        $view = View::factory('Json');
        $view->set(Simplify_Form::ID, $id);
        echo $view->render();
        exit();
        break;

      case 'checkUpload' :
        $data = array();
        $view = View::factory(View::JSON);
        $view->copyAll($data);
        echo $view->render();
        exit();
        break;

      case 'upload' :
        $data = array();

        if (!empty($_FILES['Filedata'])) {
          try {
            $upload = new Upload();
            $upload->hashFilename = true;
            $upload->upload('Filedata');

            $filename = $upload->getUploadedPath();

            $image = array($this->getFieldName('filename') => $filename);

            $image['thumb_src'] = s::config()->get('www_url') . $this->getThumb($filename, 100, 100);
            $image['image_src'] = s::config()->get('www_url') . $filename;

            $data['image'] = $image;
          } catch (Exception $e) {
            $data['error'] = $e->getMessage();
          }
        }

        $view = View::factory(View::JSON);
        $view->copyAll($data);
        echo $view->render();
        exit();
        break;
    }
  }

  protected function getThumb($file, $width = null, $height = null, $params = null)
  {
    try {
      $src = false;

      if (! empty($file) && file_exists(s::config()->get('www_dir') . $file)) {
        $src = Thumb::factory()->load($file)->cropResize($width, $height)->getCacheFilename();
      }
    } catch (ThumbException $e) {
      $src = false;
    }

    return $src;
  }

  protected function getFilesUrl()
  {
    return s::config()->get('files_url');
  }

  protected function getCacheUrl()
  {
    return s::config()->get('files_url') . '/cache';
  }

  protected function getFilesDir()
  {
    return s::config()->get('files_dir');
  }

  protected function getCacheDir()
  {
    return s::config()->get('files_dir') . '/cache';
  }

}
