<?php
namespace Simplify\Form\Element;

use Simplify\Form\Element;
use Simplify\Form\Action;

class Weekdays extends Element
{

    /**
     * 
     * {@inheritDoc}
     * @see \Simplify\Form\Element::onRender()
     */
    public function onRender(\Simplify\Form\Action $action, $data, $index)
    {
        $this->set('options', $this->getOptions($data));
        
        return parent::onRender($action, $data, $index);
    }

    /**
     * 
     * {@inheritDoc}
     * @see \Simplify\Form\Element::onPostData()
     */
    public function onPostData(\Simplify\Form\Action $action, &$data, $post)
    {
        $data[$this->getName()] = implode(',', (array) sy_get_param($post, $this->getName()));
    }

    /**
     * 
     * {@inheritDoc}
     * @see \Simplify\Form\Element::onLoadData()
     */
    public function onLoadData(Action $action, &$data, $row)
    {
        if (isset($row[$this->getFieldName()])) {
            $data[$this->getName()] = (array) explode(',', $row[$this->getFieldName()]);
        }
    }

    /**
     * 
     * {@inheritDoc}
     * @see \Simplify\Form\Element::getDisplayValue()
     */
    public function getDisplayValue(Action $action, $data, $index)
    {
        $options = $this->getOptions($data);
        $values = array_intersect_key($options['options'], array_flip($options['checked']));
        return implode(', ', $values);
    }
    
    /**
     *
     * @param unknown $data            
     * @return string[][]|unknown[]
     */
    protected function getOptions($data)
    {
        $options = array();
        $options['options'] = array(
            'Domingo',
            'Segunda',
            'Terça',
            'Quarta',
            'Quinta',
            'Sexta',
            'Sábado'
        );
        $options['checked'] = (array)$this->getValue($data);
        return $options;
    }
}