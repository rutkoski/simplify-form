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
namespace Simplify\Form\Action;

use Simplify;
use Simplify\Db\QueryParameters;
use Simplify\Form;
use Simplify\Form\Action;
use Simplify\Menu;
use Simplify\MenuItem;
use Simplify\Form\Element\Image;
use Simplify\Form\Element\Text;

/**
 * Form action list
 */
class Gallery extends Action
{

    /**
     *
     * @var Image
     */
    protected $imageElement;

    /**
     *
     * @var Text
     */
    protected $captionElement;

    /**
     *
     * @var int
     */
    protected $actionMask = Form::ACTION_LIST;

    /**
     *
     * @param Image $element            
     */
    public function setImageElement(Image $element)
    {
        $this->imageElement = $element;
    }

    /**
     *
     * @return \Simplify\Form\Element\Image
     */
    public function getImageElement()
    {
        if (! $this->imageElement) {
            $this->imageElement = $this->form->getElementByType('\Simplify\Form\Element\Image');
        }
        return $this->imageElement;
    }

    /**
     *
     * @param Text $element            
     */
    public function setCaptionElement(Text $element)
    {
        $this->captionElement = $element;
    }

    /**
     *
     * @return \Simplify\Form\Element\Text
     */
    public function getCaptionElement()
    {
        if (! $this->captionElement) {
            $this->captionElement = $this->form->getElementByType('\Simplify\Form\Element\Text');
        }
        return $this->captionElement;
    }
    
    /**
     * (non-PHPdoc)
     *
     * @see Simplify\Form\Action::onExecute()
     */
    public function onExecute()
    {
        parent::onExecute();
        
        $this->onLoadData();
    }

    /**
     * (non-PHPdoc)
     *
     * @see Simplify\Form\Action::onRender()
     */
    public function onRender()
    {
        $elements = $this->getElements();
        
        $headers = array();
        foreach ($elements as $element) {
            $element->onRenderHeaders($this, $headers);
        }
        
        $caption = $this->getCaptionElement();
        $image = $this->getImageElement();
        
        $data = array();
        foreach ($this->formData as $index => $row) {
            $line = array();
            $line[Form::ID] = $row[Form::ID];
            $line['name'] = Form::ID . "[]";
            $line['menu'] = new Menu('actions');
            $line['menu']->addItem(new Menu('main'));
            
            $line['caption'] = $caption->getDisplayValue($this, $row, $index);
            
            $imageFile = $image->getValue($row);
            $line['imageUrl'] = $image->getImageUrl($imageFile);
            $line['thumbUrl'] = $image->getThumbUrl($imageFile, 240, 240);
            
            $line['elements'] = array();
            
            $elements->rewind();
            while ($elements->valid()) {
                $element = $elements->current();
                $elements->next();
                
                $element->onRenderLine($this, $line, $row, $index);
            }
            
            $this->form->onCreateItemMenu($line['menu'], $this, $row);
            
            $data[] = $line;
        }
        
        $bulk = array();
        
        $this->form->onCreateBulkOptions($bulk);
        
        $this->set('data', $data);
        $this->set('bulk', $bulk);
        
        return parent::onRender();
    }

    /**
     * (non-PHPdoc)
     *
     * @see Simplify\Form\Action::onCreateMenu()
     */
    public function onCreateMenu(Menu $menu)
    {
        $item = new MenuItem($this->getName(), $this->getTitle(), Form::ICON_LIST, $this->url());
        
        $menu->getItemByName('main')->addItem($item);
    }

    /**
     * (non-PHPdoc)
     *
     * @see Simplify\Form\Action::onLoadData()
     */
    protected function onLoadData()
    {
        $elements = $this->getElements();
        
        $pk = $this->form->getPrimaryKey();
        
        $params = array();
        $params[QueryParameters::SELECT][] = $this->form->getPrimaryKey();
        
        while ($elements->valid()) {
            $element = $elements->current();
            $element->onInjectQueryParams($this, $params);
            
            $elements->next();
        }
        
        foreach ($this->form->getFilters() as $filter) {
            $filter->onInjectQueryParams($this, $params);
        }
        
        $this->onInjectQueryParams($params);
        
        $data = $this->repository()->findAll($params);
        
        $this->formData = array();
        
        foreach ($data as $index => $row) {
            $this->formData[$index] = array();
            $this->formData[$index][Form::ID] = $row[$pk];
            $this->formData[$index][$pk] = $row[$pk];
            
            $elements->rewind();
            
            while ($elements->valid()) {
                $element = $elements->current();
                $element->onLoadData($this, $this->formData[$index], $row);
                
                $elements->next();
            }
        }
    }
}
