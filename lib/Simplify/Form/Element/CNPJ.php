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
 * Text form element
 *
 */
class CNPJ extends \Simplify\Form\Element\Text
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
    public $mask = '99.999.999/9999-99';

    /**
     * @var string
     */
    protected $template = 'form_element_text';
    
    /**
     * (non-PHPdoc)
     * @see \Simplify\Form\Element::onValidate()
     */
    public function onValidate(\Simplify\Form\Action $action, $data)
    {
        parent::onValidate($action, $data);

        $invalid = sprintf(__('O valor informado para o campo %1$s é inválido'), $this->getLabel());
        $required = $this->required ? sprintf(__('O campo %1$s é obrigatório'), $this->getLabel()) : false;

        $rule = new \Simplify\Validation\Cnpj($invalid, $required);
        $rule->validate($this->getValue($data));
    }

}