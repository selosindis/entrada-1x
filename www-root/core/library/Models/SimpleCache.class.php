<?php
class SimpleCache {
	private $cacheArr;
	
	private function __construct() {
		$this->cacheArr = array();
	}
	
	/**
	 * @return SimpleCache
	 */
	public static function getCache() {
		static $cache;
		if (!$cache) {
			$cache = new SimpleCache();
		}
		return $cache;
	}
	
	/*****     Instance Methods       *****/
	
	/**
	 * varargs function. all parameters after the first are used to identify cached object
	 * @param $value The value to store. object, array, or primitive
	 * @param $key at least one argument for the cache key. Additional arguments may be specified
	 */
	public function set($value,$key) {

		$numargs = func_num_args() - 1; // don't count value
		
		$args = func_get_args();
		
		array_shift($args); //throw away $value, we already have a reference
		
		$curArr =& $this->cacheArr;
		
		for ($i = 0; $i < $numargs -1; $i++) {
			if (!is_array($curArr[$args[$i]])) {
				$curArr[$args[$i]] = array();
			}
			$curArr =& $curArr[$args[$i]];
		}
		
		$curArr[$args[$numargs-1]] = $value;
	}
	
	/**
	 *  varargs function. all parameters are used to identify cached object
	 * @param $key at least one argument for the cache key. Additional arguments may be specified 
	 */
	public function get($key) {
		$numargs = func_num_args(); 
		
		$args = func_get_args();
		
		$curArr =& $this->cacheArr;
		
		for ($i = 0; $i < $numargs -1; $i++) {
			if (!is_array($curArr[$args[$i]])) {
				return;
			}
			$curArr =& $curArr[$args[$i]];
		}
		
		return $curArr[$args[$numargs-1]];	
	}
	
	/**
	 *  varargs function. all parameters are used to identify cached object. Removes object if found
	 * @param $key at least one argument for the cache key. Additional arguments may be specified 
	 */
	public function remove($key) {
		$numargs = func_num_args(); 
		
		$args = func_get_args();
		
		$curArr =& $this->cacheArr;
		
		for ($i = 0; $i < $numargs -1; $i++) {
			if (!is_array($curArr[$args[$i]])) {
				return;
			}
			$curArr =& $curArr[$args[$i]];
		}
		
		unset($curArr[$args[$numargs-1]]);	
	}
}