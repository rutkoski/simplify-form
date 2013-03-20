<?php

class Simplify_Form_Element_Image extends Simplify_Form_Element
{

  /**
   *
   * @var boolean
   */
  public $required = true;

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
  protected $add = Simplify_Form::ACTION_DELETE;

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::onRender()
   */
  public function onRender(Simplify_Form_Action $action, $data, $index)
  {
    $file = $this->getValue($data, $index);

    if (! empty($file)) {
      $this->set('thumb_src', s::config()->get('www_url') . Thumb::factory()->load($file)->cropResize(100, 100)->getCacheFilename());
      $this->set('image_src', s::config()->get('www_url') . $file);
    }

    return parent::onRender($action, $data, $index);
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Element::getDisplayValue()
   */
  public function getDisplayValue(Simplify_Form_Action $action, $data, $index)
  {
    $file = $this->getValue($data, $index);

    if (! empty($file)) {
      $value = s::config()->get('www_url') . Thumb::factory()->load($file)->cropResize(50, 50)->getCacheFilename();
      return "<img src=\"{$value}\" class=\"img-polaroid\"/>";
    }

    return '';
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Element::onPostData()
   */
  public function onPostData(&$row, $data, $index)
  {
    $name = $this->getFieldName();

    $_row = $this->getRow($data, $index);

    if (! empty($_FILES['formData']['name'][$index][$name]) || $this->required) {
      try {
        $_FILES[$name] = array(
          'name' => array($index => $_FILES['formData']['name'][$index][$name]),
          'type' => array($index => $_FILES['formData']['type'][$index][$name]),
          'tmp_name' => array($index => $_FILES['formData']['tmp_name'][$index][$name]),
          'error' => array($index => $_FILES['formData']['error'][$index][$name]),
          'size' => array($index => $_FILES['formData']['size'][$index][$name]),
        );

        $upload = new Upload();
        $upload->uploadPath = $this->path;
        $upload->hashFilename = true;
        $upload->upload($name, $index);

        $this->onDelete($row);

        $row[$this->getName()] = $upload->getUploadedPath();
      }
      catch (UploadException $e) {
        throw new ValidationException($e->getMessage());
      }
    } elseif (! empty($_row[$name]['delete'])) {
      $this->onDelete($row);
      $row[$this->getName()] = '';
    }
  }

  /**
   *
   */
  public function onBeforeDelete(Simplify_Form_Action $action, $row)
  {
    $this->onDelete($row);
  }

  protected function onDelete($row)
  {
    $filename = $row[$this->getFieldName()];

    if (! empty($filename)) {
      $file = s::config()->get('www_dir') . $filename;

      if (file_exists($file)) {
        $this->getThumbComponent($file)->cleanCached();

        @unlink($file);
      }
    }
  }

  /**
   *
   * @return Thumb
   */
  protected function getThumbComponent($file)
  {
    $thumb = new Thumb();
    $thumb->load($file);
    return $thumb;
  }

}
