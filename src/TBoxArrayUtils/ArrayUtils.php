<?php

namespace TBoxArrayUtils;

use Zend\Stdlib\ArrayUtils as ZendArrayUtils;

class ArrayUtils extends ZendArrayUtils
{

    /**
     * Returns true only if the array is associative.
     * @param array $Array
     * @return bool True if the array is associative.
     */
    public static function isAssociativeArray($Array)
    {
        if( ! is_array($Array))
        {
            return false;
        }
        $keys = array_keys($Array);
        foreach ($keys as $key)
        {
            if(is_string($key))
            {
                return true;
            }
        }
        return false;
    }

    /**
     * Normalize all keys in an array to lower-case.
     * @param array $Array
     * @return array Normalized array.
     */
    public static function normalize($Array)
    {
        if( ! is_array($Array))
        {
            return array ();
        }

        $normalized = array ();
        foreach ($Array as $key => $val)
        {
            $normalized[strtolower($key)] = $val;
        }
        return $normalized;
    }

    /*
     * check if an array is empty
     * returns: (bool)
     */

    public static function isEmpty($InputVariable)
    {
        $result = true;

        if(is_array($InputVariable))
        {
            if(count($InputVariable) > 0)
            {
                foreach ($InputVariable as $value)
                {
                    $result = $result && self::isEmpty($value);
                }
            }
        }
        else
        {
            $result = empty($InputVariable);
        }

        return $result;
    }


    /**
     *
     * @param array $Array
     * @param type $Strict [false] - check only for isset; true check also for !empty
     * @return array
     */
    public static function removeEmptyKeys(array $Array, $Strict = false)
    {
        $array = array ();
        foreach ($Array as $key => $val)
        {
            if(isset($val))
            {
                if($Strict)
                {
                    if( ! empty($val))
                    {
                        $array[$key] = $val;
                    }
                }
                else
                {
                    $array[$key] = $val;
                }
            }
        }
        return $array;
    }

    /**
     * Extends ``array_unique()`` to support multi-dimensional arrays.
     *
     * @package Trip\Utils
     * @since 3.5
     *
     * @param array $array Expects an incoming array.
     * @return array Returns the ``$array`` after having reduced it to a unique set of values.
     */
    public static function array_unique_object(array $Array, $preseveKeys = false)
    {
        $array = array_map("unserialize", array_unique(array_map("serialize", $Array)));
        if( ! $preseveKeys)
        {
            $array = array_values($array);
        }
        return $array;
    }

    /**
     * 
     * @param type $Array
     * @param type $Strict [true]
     * @return array
     */
    public static function clean($Array, $Strict = true)
    {
        if( ! is_array($Array))
        {
            return $Array;
        }
        $cleaned = array ();

        foreach ($Array as $key => $value)
        {
            if($Strict)
            {
                if( ! empty($value))
                {
                    $tmp = self::clean($value, $Strict);
                    if( ! empty($tmp))
                    {
                        $cleaned[$key] = $tmp;
                    }
                }
            }//strict
            else
            {
                if(is_array($value))
                {
                    if( ! empty($value))
                    {
                        $tmp = self::clean($value, $Strict);
                        if( ! empty($tmp))
                        {
                            $cleaned[$key] = $tmp;
                        }
                    }
                }
                elseif(strlen($value) > 0)
                {
                    $tmp = self::clean($value, $Strict);
                    if(strlen($tmp) > 0)
                    {
                        $cleaned[$key] = $tmp;
                    }
                }
            }//not strict
        } //end foreach

        return $cleaned;
    }

    /**
     *
     * @param mix $Array
     * @return string ; false on failure
     * @throws \Exception
     */
    public static function toString($Array)
    {
        if( ! isset($Array))
        {
            return false;
        }
        if(is_array($Array))
        {
            if(self::isAssociativeArray($Array))
            {
                array_walk($Array, create_function('&$i,$k', '$i=" $k=\"$i\"";'));
            }
            $arrayString = implode($Array, "");
            return $arrayString;
        }
        if(is_object($Array))
        {
            throw new \Exception('This method is not implemented for object yet!');
        }
        return false;
    }


    // Will take all data in second array and apply to first array leaving any non-corresponding values untouched and intact
    public static function array_replace(array &$array1, array &$array2)
    {
        // This sub function is the iterator that will loop back on itself ad infinitum till it runs out of array dimensions
        if( ! function_exists('tier_parse'))
        {

            function tier_parse(array &$t_array1, array&$t_array2)
            {
                foreach ($t_array2 as $k2 => $v2)
                {
                    if(is_array($t_array2[$k2]))
                    {
                        tier_parse($t_array1[$k2], $t_array2[$k2]);
                    }
                    else
                    {
                        $t_array1[$k2] = $t_array2[$k2];
                    }
                }
                return $t_array1;
            }

        }

        foreach ($array2 as $key => $val)
        {
            if(is_array($array2[$key]))
            {
                tier_parse($array1[$key], $array2[$key]);
            }
            else
            {
                $array1[$key] = $array2[$key];
            }
        }
        return $array1;
    }

    public static function recursive_array_replace($find, $replace, $array)
    {
        if( ! is_array($array))
        {
            return str_replace($find, $replace, $array);
        }
        $newArray = array ();
        foreach ($array as $key => $value)
        {
            $newArray[$key] = self::recursive_array_replace($find, $replace, $value);
        }
        return $newArray;
    }

    public static function recursive_array_key_replace($find, $replace, $array)
    {
        if(!self::isNested($array))
        {
            return self::array_key_replace($find, $replace, $array);
        }
        else
        {
            $newArray = array();
            foreach($array as $key=>$value)
            {
                if($key===$find)
                {
                    $newArray[$replace] = self::recursive_array_key_replace($find, $replace, $value);
                }
                else
                {
                    $newArray[$key] = self::recursive_array_key_replace($find, $replace, $value);
                }
            }
            return $newArray;
        }
    }

    public static function array_key_replace($find, $replace, $array, $preserveOrder = false)
    {
        if(is_array($array))
        {
            if(array_key_exists($find, $array))
            {
                //this will raise a warning
                /*$test = array(
                    'ISO3' => null
                    ,'CountryID' => null
                    ,'RegionID' => null
                    ,'Latitude' => null
                    ,'Longitude' => null
                );
                array_flip($test);exit();*/
                //it only work with string and interger values
                //return array_flip(self::recursive_array_replace($find, $replace, array_flip($array)));

                if($preserveOrder)
                {
                    foreach ($array as $key => $value) 
                    {
                        if($key === $find)
                        {
                            $newArray[$replace] = $value;
                        }
                        else
                        {
                            $newArray[$key] = $value;
                        }
                    }

                    return $newArray;
                }
                else
                {
                    $array[$replace] = $array[$find];
                    unset($array[$find]);
                    
                    return $array;
                }

            }
            else
            {
                return $array;
            }
        }
        else
        {
            return $array;
        }
    }

    /**
     * 
     * @param array $DestinationArray
     * @param array $SourceArray
     * @param type $Strict
     * @return array
     */
    public static function exchange(array $DestinationArray, array $SourceArray, $Strict = true)
    {

        if( ! $Strict)
        {
            return array_merge($DestinationArray, $SourceArray);
        }
        foreach ($SourceArray as $key => $value)
        {
            if(array_key_exists($key, $DestinationArray))
            {
                $DestinationArray[$key] = $value;
            }
        }
        unset($SourceArray);
        return $DestinationArray;
    }

    public static function reset($Array)
    {
        if( ! isset($Array) || empty($Array))
        {
            return null;
        }
        if($Array instanceof \ArrayObject)
        {
            return $Array->current();
        }
        elseif(is_array($Array))
        {
            return reset($Array);
        }
        else
        {
            return $Array->current();
        }
    }
    
    public static function end($Array)
    {
        if( ! isset($Array) || empty($Array))
        {
            return null;
        }
        if($Array instanceof \ArrayObject)
        {
            $array = $Array->getArrayCopy();
            return end($array);
        }
        elseif(is_array($Array))
        {
            return end($Array);
        }
        else
        {
            $array = $Array->getArrayCopy();
            return end($array);
        }
    }

    /**
     * Searches an array *(or even a multi-dimensional array)* using a regular expression match against array values.
     *
     * @package Trip\Utils
     *
     * @param str $regex A regular expression to look for inside the array.
     * @return bool True if the regular expression matched at least one value in the array, else false.
     */
    public static function regex_in_array($regex = FALSE, $array = FALSE)
    {
        if(is_string($regex) && strlen($regex) && is_array($array))
        {
            foreach ($array as $value)
            {
                if(is_array($value))
                {
                    if(self::regex_in_array($regex, $value))
                    {
                        return true;
                    }
                }
                else if(is_string($value))
                {
                    if(@preg_match($regex, $value))
                    {
                        return true;
                    }
                }
            }
            return false;
        }
        else
        {
            return false;
        }
    }

    /**
     * Searches an array *(or even a multi-dimensional array)* of regular expressions, to match against a string value.
     *
     * @package Trip\Utils
     *
     * @param str $string A string to test against.
     * @param array $array An array of regex patterns to match against ``$string``.
     * @return bool True if at least one regular expression in the ``$array`` matched ``$string``, else false.
     */
    public static function in_regex_array($string = FALSE, $array = FALSE)
    {
        if(is_string($string) && strlen($string) && is_array($array))
        {
            foreach ($array as $value)
            {
                if(is_array($value))
                {
                    if(self::in_regex_array($string, $value))
                    {
                        return true;
                    }
                }
                else if(is_string($value))
                {
                    if(@preg_match($value, $string))
                    {
                        return true;
                    }
                }
            }
            return false;
        }
        else
        {
            return false;
        }
    }


    /**
     * Forces string values on each array value *(also supports multi-dimensional arrays)*.
     *
     * @package Trip\Utils
     *
     * @param array $array An input array.
     * @return array Returns the ``$array`` after having forced it to set of string values.
     */
    public static function force_strings($array = FALSE)
    {
        $array = (array) $array;

        foreach ($array as &$value)
        {
            if(is_array($value))
            {
                $value = self::force_strings($value);
            }
            else if( ! is_string($value))
            {
                $value = (string) $value;
            }
        }
        return $array;
    }

    /**
     * Forces integer values on each array value *(also supports multi-dimensional arrays)*.
     *
     * @package Trip\Utils
     *
     * @param array $array An input array.
     * @return array Returns the ``$array`` after having forced it to set of integer values.
     */
    public static function force_integers($array = FALSE)
    {
        $array = (array) $array;

        foreach ($array as &$value)
        {
            if(is_array($value))
            {
                $value = self::force_integers($value);
            }
            else if( ! is_integer($value))
            {
                $value = (int) $value;
            }
        }
        return $array;
    }

    /**
     * Sorts arrays *(also supports multi-dimensional arrays)* by key, low to high.
     *
     * @package Trip\Utils
     *
     * @param array $array An input array.
     * @param int $flags Optional. Can be used to modify the sorting behavior.
     * 	See: {@link http://www.php.net/manual/en/function.ksort.php}
     * @return Unlike PHP's ``ksort()``, this function returns the array, and does NOT work on a reference.
     */
    public static function ksort_deep($array = FALSE, $flags = SORT_REGULAR)
    {
        $array = (array) $array;
        ksort($array, $flags); //sort by key

        foreach ($array as &$value)
        {
            if(is_array($value))
            {
                $value = self::ksort_deep($value, $flags);
            }
        }
        return $array;
    }

    /**
     * Sorts an array by key case-insensitive, maintaining key to data correlations.
     * This is useful mainly for associative arrays.
     *
     * @param array $array_arg Array to be sorted by key case-insensitive
     */
    public static function ksort(&$array_arg)
    {
        $key_map = array ();

        // create a map with keys in lower case referencing the case-sensitive keys
        foreach ($array_arg as $key => $value)
        {
            $key_map[strtolower($key)] = $key;
        }

        // sort by the (lowercase) keys
        ksort($key_map);

        $ret_array = array ();

        foreach ($key_map as $lower_key => $original_key)
        {
            $ret_array[$original_key] = $array_arg[$original_key];
        }

        $array_arg = $ret_array;
    }


    /**
     * Searches the array recursively for a given value and returns the corresponding key if successful
     * similar to array_search
     * 
     * @param string $needle
     * @param iterable $haystack
     * @param boolean $subloop
     * @return array of keys FALSE on not found
     * @link php.net/array_search
     */
    public static function inArrayRecursive($needle, $haystack, $subloop = false)
    {
        if($subloop === false) $resarr = array ();
        foreach ($haystack as $key => $value)
        {
            $current_key = $key;
            if(is_string($needle)) $needle = trim(strtolower($needle));
            if(is_string($value)) $value = trim(strtolower($value));
            if($needle === $value OR (is_array($value) && self::inArrayRecursive($needle, $value, true) === true))
            {
                $resarr[] = $current_key;
                if($subloop === true) return true;
            }
        }
        if( !isset($resarr) )
        {
            $resarr = array();
        }
        
        return $resarr;
    }

    /**
     *
     * @param type $needle value to search for
     * @param array $haystack target array
     * @param type $callback callback to be runned (array's element, needle)
     * @param type $strict
     * @return type
     */
    public static function inArray($needle, array $haystack, $callback = null, $strict = false)
    {
        if( ! isset($callback) || empty($callback))
        {
            return parent::inArray($needle, $haystack, $strict);
        }

        $value = array_filter($haystack,
            function($el) use ($needle, $callback) {
                if( ! is_string($el))
                {
                    return false;
                }
                return ( $callback($el, $needle) !== false );
            });

        return $value;
    }


    public static function in_array_r($needle, $haystack, $strict = false)
    {
        foreach ($haystack as $item)
        {
            if(($strict ? $item === $needle : $item == $needle) || (is_array($item) && self::in_array_r($needle, $item, $strict)))
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Recursively implodes an array with optional key inclusion
     *
     * Example of $include_keys output: key, value, key, value, key, value
     *
     * @access  public
     * @param   array   $array         multi-dimensional array to recursively implode
     * @param   string  $glue          value that glues elements together
     * @param   bool    $include_keys  include keys before their values
     * @param   bool    $trim_all      trim ALL whitespace from string
     * @return  string  imploded array
     */
    public static function implodeRecursive(array $array, $glue = ',', $include_keys = false, $trim_all = true)
    {
        $glued_string = '';

        // Recursively iterates array and adds key/value to glued string
        array_walk_recursive($array,
            function($value, $key) use ($glue, $include_keys, &$glued_string) {
                $include_keys and $glued_string .= $key . $glue;
                $glued_string .= $value . $glue;
            });

        // Removes last $glue from string
        strlen($glue) > 0 and $glued_string = substr($glued_string, 0, -strlen($glue));

        // Trim ALL whitespace
        $trim_all and $glued_string = preg_replace("/(\s)/ixsm", '', $glued_string);

        return (string) $glued_string;
    }

    /**
     * Much faster alternative to PHP array_dif
     * @param array $Source the source array
     * @param array $Compare the compare array
     * @return array (the values that are in $Source and not in $Compare
     *
     * !IMPORTANT - it will diff values and not keys
     */
    public static function arrayDiff(array $Source, array $Compare)
    {
        $map = $out = array ();
        foreach ($Source as $val) $map[$val] = 1;
        foreach ($Compare as $val) if(isset($map[$val])) $map[$val] = 0;
        foreach ($map as $val => $ok) if($ok) $out[] = $val;
        return $out;

        /**
         * $a = array('A', 'B', 'C', 'D');
         * $b = array('X', 'C', 'A', 'Y');
         * print_r(my_array_diff($a, $b)); // B, D
         */
    }


    /**
     *
     * @param type $needle
     * @param type $haystack
     * @return array|false
     */
    public static function arrayKeyExists($needle, $haystack)
    {
        foreach ($haystack as $key => $value)
        {
            if($needle === $key)
            {
                return array ($key => $value);
                return true;
            }
            if(is_array($value))
            {
                if(($result = self::arrayKeyExists($needle, $value)) !== false)
                {
                    return $result;
                    return true;
                }
                else
                {
                    continue;
                }
            }
        }
        return false;
    }

    /**
     * version of array_merge_recursive without overwriting numeric keys
     *
     * @return arrays
     *
     */
    public static function mergeRecursive()
    {

        $arrays = func_get_args();
        $base = array_shift($arrays);

        foreach ($arrays as $array)
        {
            reset($base); //important
            if( ! is_array($array))
            {
                continue;
            }
            while (list($key, $value) = @each($array))
            {
                if(is_array($value) && isset($base[$key]) && is_array($base[$key]))
                {
                    $base[$key] = static::mergeRecursive($base[$key], $value);
                }
                else
                {
                    $base[$key] = $value;
                }
            }
        }

        return $base;
    }

    /**
     *
     * @param array $Array
     * @param bool $asKey if the key should be injected as key or as item
     * @return type
     *
     * input    array (
                    'name' =>
                    array (
                      'first' => 'Coco',
                      'last' =>
                      array (
                        'middle' => 'rico',
                        'last' => 'repkit',
                      ),
                    ),
                    'age' => '21',
                    'isAlive' => 'true',
                  )
     * result with $asKey = false array (
                                1 => 'age',
                                2 => 'isAlive',
                                3 => 'name',
                                4 =>
                                array (
                                  0 => 'first',
                                  2 => 'last',
                                  3 =>
                                  array (
                                    0 => 'middle',
                                    1 => 'last',
                                  ),
                                ),
                              )
     * use full for view helper htmllist
     * result with $asKey = true array (
                                1 => 'age',
                                2 => 'isAlive',
                                'name' =>
                                array (
                                  0 => 'first',
                                  'last' =>
                                  array (
                                    0 => 'middle',
                                    1 => 'last',
                                  ),
                                ),
                              )
     */
    public static function arrayKeys($Array, $asKey = false)
    {
        if( ! is_array($Array))
        {
            return $Array;
        }
        $keys = array_keys($Array);

        foreach ($Array as $key => $i)
        {
            if(is_array($i))
            {
                $keysToRemove = array_keys($keys, $key);
                foreach ($keysToRemove as $k)
                {
                    if(!$asKey)
                        $keys[] = $keys[$k];
                    unset($keys[$k]);
                    if(!$asKey)
                        $keys[] = static::arrayKeys($i,$asKey);
                }
                //$keys = array_merge($keys, array_keys_r($i));
                if($asKey)
                    $keys[$key] = static::arrayKeys($i,$asKey);
            }
        }
        return $keys;
    }

    /**
     * Check if a given array is a multidimensional array or not
     *
     * @param type $Array
     * @return boolean
     */
    public static function isNested($Array)
    {
        if(count($Array) == count($Array, COUNT_RECURSIVE))
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    /**
    * Gets the complete parent stack of a string array element if it is found
    * within the parent array
    *
    * This will not search objects within an array, though I suspect you could
    * tweak it easily enough to do that
    *
    * @param string $child The string array element to search for
    * @param array $stack The stack to search within for the child
    * @param bool $full return the full object or not [true]
    * @return array An array containing the parent stack for the child if found,
    *               false otherwise
    */
   public static function getParentStackComplete($child, $stack, $full=true) {
       $return = array();
       foreach ($stack as $k => $v) {
           if (is_array($v)) {
               // If the current element of the array is an array, recurse it
               // and capture the return stack
               $stack = self::getParentStackComplete($child, $v, $full);

               // If the return stack is an array, add it to the return
               if (is_array($stack) && !empty($stack)) {
                   if($full){
                    $return[$k] = $v;
                   }else{
                    $return[$k] = $stack;
                   }
               }
           } else {
               // Since we are not on an array, compare directly
               if ($v == $child) {
                   // And if we match, stack it and return it
                   $return[$k] = $child;
               }
           }
       }

       // Return the stack
       return empty($return) ? false: $return;
   }

    /**
    * 
    * Recursive array_keys that keeps the hierarchy
    *
    *
    * @param array $array The array to receive keys from
    * @return array An array containing the all the keys
    */
   public static function arrayKeysRecursive($array){
        foreach ($array as $key => $value) {
            if (is_array($value) || $value instanceof \Traversable) {
                $index[$key] = self::arrayKeysRecursive($value);
            } else {
                $index []= $key;
            }
        }

        return $index != null ? $index : [];
    }

    /**
    * 
    * Create one level array from multiple levels by concatenating the keys using a separator
    *
    *
    * @param array $array The array to transform
    * @param array $newArray The array used to the new values
    * @param string $newKey The new key obtainer by concatenating other keys
    * @param string $separator Separator used to concatenating keys
    */
   public static function reduceArrayToOneLevel($array,&$newArray,$newKey= '',$separator = '_'){
        foreach ($array as $key => $value) {
            if( !empty($newKey) ){
                $nextNewKey = $newKey . $separator . $key;
            }else{
                $nextNewKey = $key;
            }
            if (is_array($value) || $value instanceof \Traversable) {
                self::reduceArrayToOneLevel($value,$newArray,$nextNewKey);
            } else {
                $newArray[$nextNewKey]= $value;
            }
        }
    }

    /**
    * 
    * array_diff recursive
    *
    *
    * @param array $aArray1 The array to compare
    * @param array $aArray2 The array to compare to
    */
    public static function arrayRecursiveDiff($aArray1, $aArray2) {
	  $aReturn = array();

	  foreach ($aArray1 as $mKey => $mValue) {
	    if (array_key_exists($mKey, $aArray2)) {
	      if (is_array($mValue)) {
	        $aRecursiveDiff = self::arrayRecursiveDiff($mValue, $aArray2[$mKey]);
	        if (count($aRecursiveDiff)) { $aReturn[$mKey] = $aRecursiveDiff; }
	      } else {
	        if ($mValue != $aArray2[$mKey]) {
	          $aReturn[$mKey] = $mValue;
	        }
	      }
	    } else {
	      $aReturn[$mKey] = $mValue;
	    }
	  }

	  return $aReturn;
	} 

}

?>