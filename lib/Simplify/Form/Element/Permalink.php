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
namespace Simplify\Form\Element;

use Simplify\Form\Action;

/**
 * Permalink form element
 */
class Permalink extends \Simplify\Form\Element
{

  /**
   *
   * @var callback
   */
  public $callback;

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Element::onPostData()
   */
  public function onPostData(Action $action, &$data, $post)
  {
    $data[$this->getName()] = empty($post[$this->getName()]) ? $this->buildPermalink($action, $data, $post) : $post[$this->getName()];
  }

  protected function buildPermalink(Action $action, $data, $post)
  {
    $id = $action->form->getId();
    $id = $id[0];

    $perma = call_user_func($this->callback, $data, $post);
    $perma = sy_slugify($perma);

    $pk = $action->form->getPrimaryKey();
    
    $table = $action->form->getTable();
    
    $field = $this->getFieldName();
    
    $i = 0;
    do {
      $c = $i ? '-' . $i : '';
    
      $sql = "SELECT {$pk} FROM {$table} WHERE {$field} LIKE '{$perma}{$c}'";
    
      if ($id) {
        $sql .= " AND {$pk} != {$id}";
      }
    
      $_row = \Simplify::db()->query($sql)->execute()->fetchOne();
      
      $i++;
    }
    while (!empty($_row) && $i < 10);
    
    $perma .= $c;

    return $perma;
  }

}
