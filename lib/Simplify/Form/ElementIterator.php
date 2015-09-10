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

use Simplify\Form;

/**
 *
 * Form element iterator
 *
 */
class ElementIterator implements \Iterator {

	/**
	 *
	 * @var FormElement[]
	 */
	protected $elements;

	/**
	 *
	 * @var int
	 */
	protected $mask;

	/**
	 *
	 * @var int
	 */
	protected $index;

	/**
	 *
	 * @param FormElement[] $elements
	 * @param int $mask
	 */
	public function __construct($elements, $mask = Form::ACTION_ALL) {
		$this->elements = $elements;
		$this->mask     = $mask;
		$this->index    = -1;
		$this->next();
	}

	/**
	 * (non-PHPdoc)
	 * @see Iterator::current()
	 * @return Element
	 */
	public function current() {
		return $this->elements[$this->key()];
	}

	/**
	 * (non-PHPdoc)
	 * @see Iterator::next()
	 */
	public function next() {
		do {
			$this->index++;
		} while ($this->index < count($this->elements) && !$this->elements[$this->index]->show($this->mask));
	}

	/**
	 * (non-PHPdoc)
	 * @see Iterator::key()
	 */
	public function key() {
		return $this->index;
	}

	/**
	 * (non-PHPdoc)
	 * @see Iterator::valid()
	 */
	public function valid() {
		return $this->index >= 0 && $this->index < count($this->elements);
	}

	/**
	 * (non-PHPdoc)
	 * @see Iterator::rewind()
	 */
	public function rewind() {
		$this->index = count($this->elements) ? 0 : -1;
	}

}
