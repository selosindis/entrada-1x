<?php

class Collection implements Iterator, ArrayAccess, Countable {
    private $position = 0;
    private $container = array();  

    public function __construct($array) {
        $this->position = 0;
        if (is_array($array)) {
        	$this->container = $array; 
        }
    }

    public function push($value) {
    	array_push($this->container, $value);
    }
    
    public function unshift($value) {
    	array_unshift($this->container, $value);
    }
    
    public function pop() {
    	$value = array_pop($this->container);
    	if (!$this->valid()) {
    		$this->rewind();
    	}
    	return $value;
    }
    
    public function shift() {
    	$value = array_shift($this->container);
    	if (!$this->valid()) {
    		$this->rewind();
    	}
    	return $value;
    }
    
    function rewind() {
        $this->position = 0;
    }

    function current() {
        return $this->container[$this->position];
    }

    function key() {
        return $this->position;
    }

    function next() {
        ++$this->position;
    }
    
    function prev() {
    	--$this->position;
    }

    function valid() {
        return isset($this->container[$this->position]);
    }
    
	public function offsetSet($offset, $value) {
        $this->container[$offset] = $value;
    }
    public function offsetExists($offset) {
        return isset($this->container[$offset]);
    }
    public function offsetUnset($offset) {
        unset($this->container[$offset]);
    }
    public function offsetGet($offset) {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }
    
    public function count() {
    	return count($this->container);
    }
    
    public function contains($element) {
    	return in_array($element, $this->container, true);
    }
}