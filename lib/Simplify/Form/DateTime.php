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

/**
 *
 * Data/Time utilities
 *
 */
class Simplify_Form_DateTime
{

  /**
   * Database date format
   *
   * @var string
   */
  const FORMAT_DB_DATE = 'Y-m-d';

  /**
   * Database time format
   *
   * @var string
   */
  const FORMAT_DB_TIME = 'H:i:s';

  /**
   * Database datetime format
   *
   * @var string
   */
  const FORMAT_DB_DATETIME = 'Y/m/d H:i:s';

  /**
   * Application datetime format
   *
   * @var string
   */
  public static $datetimeFormat = 'd/m/Y H:i:s';

  /**
   * Application date format
   *
   * @var string
   */
  public static $dateFormat = 'd/mY';

  /**
   * Application time format
   *
   * @var string
   */
  public static $timeFormat = 'H:i:s';

  /**
   * Format date and time
   *
   * @param mixed $value date
   * @return string
   */
  public static function datetime($value)
  {
    return self::format($value, self::$datetimeFormat);
  }

  /**
   * Format date
   *
   * @param mixed $value date
   * @return string
   */
  public static function date($value)
  {
    return self::format($value, self::$dateFormat);
  }

  /**
   * Format time
   *
   * @param mixed $value date
   * @return string
   */
  public static function time($value)
  {
    return self::format($value, self::$timeFormat);
  }

  /**
   * Convert $value to unix timestamp
   *
   * @param mixed $value
   * @return int
   */
  public static function timestamp($value)
  {
    return is_numeric($value) ? $value : strtotime(str_replace('/', '-', $value));
  }

  /**
   * Format date according to format
   *
   * @param mixed $value date
   * @param string $format date format
   * @return string
   */
  public static function format($value, $format)
  {
    return date($format, self::timestamp($value));
  }

  /**
   * Format date to database format
   *
   * @return string
   */
  public static function database($value, $format = self::FORMAT_DB_DATETIME)
  {
    return self::format($value, $format);
  }

}
