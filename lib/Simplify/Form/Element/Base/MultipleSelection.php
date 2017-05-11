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
namespace Simplify\Form\Element\Base;

use Simplify\Form\Action;

/**
 * Base class for miltiple selection elements
 */
abstract class MultipleSelection extends \Simplify\Form\Element
{

    /**
     * Selection options
     *
     * @var array|ListProvider
     */
    public $options;

    /**
     *
     * @var string
     */
    public $associationTable;

    /**
     *
     * @var string
     */
    public $associationPrimaryKey;

    /**
     *
     * @var string
     */
    public $associationForeignKey;

    /**
     *
     * @var string
     */
    public $table;

    /**
     *
     * @var string
     */
    public $primaryKey;

    /**
     *
     * @var string
     */
    public $foreignKey;

    /**
     *
     * @var string
     */
    public $labelField;

    /**
     * (non-PHPdoc)
     *
     * @see \Simplify\Form\Component::onExecuteServices()
     */
    public function onExecuteServices($serviceAction)
    {
        parent::onExecuteServices($serviceAction);
        
        switch ($serviceAction) {
            case 'toggle':
                $pid = $this->form->getId();
                $fid = \Simplify::request()->post($this->getName());
                
                $this->set('value', $this->toggleValue($pid[0], $fid));
                
                break;
        }
        
        return $this->getView();
    }

    /**
     *
     * @param unknown_type $pid            
     * @param unknown_type $fid            
     * @return boolean
     */
    public function toggleValue($pid, $fid)
    {
        $t = $this->getTable();
        
        if (empty($t)) {
            return false;
        }
        
        $pk = $this->getPrimaryKey();
        $fk = $this->getForeignKey();
        $at = $this->getAssociationTable();
        $apk = $this->getAssociationPrimaryKey();
        $afk = $this->getAssociationForeignKey();
        
        $data = array(
            $apk => $pid,
            $afk => $fid
        );
        
        $found = \Simplify::db()->query()
            ->from($at)
            ->select("COUNT(*)")
            ->where("{$apk} = :{$apk} AND {$afk} = :{$afk}")
            ->execute($data)
            ->fetchOne();
        
        if (empty($found)) {
            \Simplify::db()->insert($at, $data)->execute($data);
        } else {
            \Simplify::db()->delete($at, "{$apk} = :{$apk} AND {$afk} = :{$afk}")->execute($data);
        }
        
        return ! $found;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Simplify\Form\Element::onRender()
     */
    public function onRender(\Simplify\Form\Action $action, $data, $index)
    {
        $this->set('options', $this->getOptions($data));
        
        return parent::onRender($action, $data, $index);
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Simplify\Form\Element::onRenderLine()
     */
    public function onRenderLine(\Simplify\Form\Action $action, &$line, $data, $index)
    {
        $element = array();
        
        $element['id'] = $this->getElementId($index);
        $element['name'] = $this->getInputName($index);
        $element['class'] = $this->getElementClass();
        $element['label'] = $this->getLabel();
        $element['controls'] = $this->onRender($action, $data, $index)->render();
        
        $line['elements'][$this->getId()] = $element;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Simplify\Form\Element::onPostData()
     */
    public function onPostData(\Simplify\Form\Action $action, &$data, $post)
    {
        $data[$this->getName()] = (array) sy_get_param($post, $this->getName());
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Simplify\Form\Element::onCollectTableData()
     */
    public function onCollectTableData(\Simplify\Form\Action $action, &$row, $data)
    {
        if ($this->options instanceof \Simplify\Form\Provider || ! is_null($this->options)) {
            $row[$this->getFieldName()] = $data[$this->getName()];
        }
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Simplify\Form\Component::onSave()
     */
    public function onSave(\Simplify\Form\Action $action, &$data)
    {
        $id = $data[\Simplify\Form::ID];
        
        $options = $this->getOptions($data);
        
        $a = $options['checked'];
        
        $b = $data[$this->getName()];
        
        $add = array_diff($b, $a);
        $rem = array_diff($a, $b);
        
        $at = $this->getAssociationTable();
        $apk = $this->getAssociationPrimaryKey();
        $afk = $this->getAssociationForeignKey();
        
        foreach ($add as $_id) {
            $data = array(
                $apk => $id,
                $afk => $_id
            );
            
            \Simplify::db()->insert($at, $data)->execute($data);
        }
        
        foreach ($rem as $_id) {
            $data = array(
                $apk => $id,
                $afk => $_id
            );
            
            \Simplify::db()->delete($at, "{$apk} = :{$apk} AND {$afk} = :{$afk}")->execute($data);
        }
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Simplify\Form\Element::onInjectQueryParams()
     */
    public function onInjectQueryParams(\Simplify\Form\Action $action, &$params)
    {
        //
    }

    public function onLoadData(\Simplify\Form\Action $action, &$data, $row)
    {
        if (isset($row[$this->getFieldName()])) {
            $this->defaultValue = (array)$row[$this->getFieldName()];
        }
    }
    
    /**
     *
     * @param unknown_type $data            
     * @return multitype:multitype:Ambigous <unknown, ArrayAccess> multitype:unknown
     */
    public function getOptions($data)
    {
        if ($this->options instanceof \Simplify\Form\Provider) {
            $options = $this->options->getData();
            $checked = (array) $this->defaultValue;
        } elseif (! is_null($this->options)) {
            $options = (array) $this->options;
            $checked = (array) $this->defaultValue;
        } else {
            $t = $this->getTable();
            $pk = $this->getPrimaryKey();
            $fk = $this->getForeignKey();
            $at = $this->getAssociationTable();
            $apk = $this->getAssociationPrimaryKey();
            $afk = $this->getAssociationForeignKey();
            
            $q = \Simplify::db()->query()
                ->select("{$t}.{$fk}")
                ->select("{$t}.{$this->labelField}")
                ->select("{$at}.{$afk} AS checked")
                ->from($t)
                ->leftJoin("{$at} ON ({$t}.{$fk} = {$at}.{$afk} AND {$at}.{$apk} = :{$pk})");
            
            $data = $q->execute(array(
                $pk => $data[\Simplify\Form::ID]
            ))->fetchAll();
            
            $options = sy_array_to_options($data, $fk, $this->labelField);
            
            $checked = array();
            foreach ($data as $row) {
                if ($row['checked']) {
                    $checked[] = $row[$fk];
                }
            }
        }
        
        return array(
            'options' => $options,
            'checked' => $checked
        );
    }

    /**
     *
     * @param mixed[mixed] $options
     *            array with options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     *
     * @param \Simplify\Form\Provider $provider            
     */
    public function setOptionsProvider(\Simplify\Form\Provider $provider)
    {
        $this->options = $provider;
    }

    /**
     * Get the related table name.
     * By default, it's the element's name.
     *
     * @return string
     */
    public function getTable()
    {
        if (empty($this->table)) {
            $this->table = $this->getName();
        }
        return $this->table;
    }

    /**
     * Get the key on the main table.
     * By default, it's the form's primary key.
     *
     * @return string
     */
    public function getPrimaryKey()
    {
        if (empty($this->primaryKey)) {
            $this->primaryKey = $this->form->getPrimaryKey();
        }
        
        return $this->primaryKey;
    }

    /**
     * Get the key on the related table.
     * By default, it's the table's primary key (<table>_id).
     *
     * @return string
     */
    public function getForeignKey()
    {
        if (empty($this->foreignKey)) {
            $this->foreignKey = \Simplify\Inflector::singularize($this->getTable()) . '_id';
        }
        
        return $this->foreignKey;
    }

    /**
     * Get the association table.
     * By default, it's the names of the main and related tables,
     * sorted alphabeticaly, separated by _ (<atable>_<btable>).
     *
     * @return string
     */
    public function getAssociationTable()
    {
        if (empty($this->associationTable)) {
            $tables = array(
                $this->form->getTable(),
                $this->getTable()
            );
            
            sort($tables);
            
            $this->associationTable = implode('_', $tables);
        }
        
        return $this->associationTable;
    }

    /**
     * Get the key of the main table on the association table.
     *
     * @return string
     */
    public function getAssociationPrimaryKey()
    {
        if (empty($this->associationPrimaryKey)) {
            $this->associationPrimaryKey = $this->getPrimaryKey();
        }
        
        return $this->associationPrimaryKey;
    }

    /**
     * Get the key of the related table on the association table.
     *
     * @return string
     */
    public function getAssociationForeignKey()
    {
        if (empty($this->associationForeignKey)) {
            $this->associationForeignKey = $this->getForeignKey();
        }
        
        return $this->associationForeignKey;
    }
}
