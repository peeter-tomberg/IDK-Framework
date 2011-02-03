<?php

/**
 * An SQLArrayCollection is a Collection implementation that wraps a regular PHP array.
 *
 * @since   2.0
 * @author  Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author  Jonathan Wage <jonwage@gmail.com>
 * @author  Roman Borschel <roman@code-factory.org>
 */

namespace Idk\Orm\Collections;

use \Countable, \IteratorAggregate, \ArrayIterator;

class SQLArrayCollection implements Countable, IteratorAggregate {
	
	 /**
     * An array containing the entries of this collection.
     *
     * @var array
     */
    protected $_elements;

  
    /**
     * Sets the internal iterator to the first element in the collection and
     * returns this element.
     *
     * @return mixed
     */
    public function first() {
        return reset($this->_elements);
    }

    /**
     * Sets the internal iterator to the last element in the collection and
     * returns this element.
     *
     * @return mixed
     */
    public function last() {
        return end($this->_elements);
    }
   
    /**
     * Moves the internal iterator position to the next element.
     *
     * @return mixed
     */
    public function next() {
        return next($this->_elements);
    }
    
    /**
     * Gets the element of the collection at the current internal iterator position.
     *
     * @return mixed
     */
    public function current() {
        return current($this->_elements);
    }

    /**
     * Removes the specified element from the collection, if it is found.
     *
     * @param mixed $element The element to remove.
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeElement($element) {
        $key = array_search($element, $this->_elements, true);
        
        if ($key !== false) {
            unset($this->_elements[$key]);
            
            return true;
        }
        
        return false;
    }

    /**
     * Checks whether the given element is contained in the collection.
     * Only element values are compared, not keys. The comparison of two elements
     * is strict, that means not only the value but also the type must match.
     * For objects this means reference equality.
     *
     * @param mixed $element
     * @return boolean TRUE if the given element is contained in the collection,
     *          FALSE otherwise.
     */
    public function contains($element) {
        return in_array($element, $this->_elements, true);
    }
	/**
     * Returns the number of elements in the collection.
     *
     * Implementation of the Countable interface.
     *
     * @return integer The number of elements in the collection.
     */
    public function count() {
        return count($this->_elements);
    }
	/**
     * Adds an element to the collection.
     *
     * @param mixed $value
     * @return boolean Always TRUE.
     */
    public function add($value) {
        $this->_elements[] = $value;
        return true;
    }

    /**
     * Checks whether the collection is empty.
     * 
     * Note: This is preferrable over count() == 0.
     *
     * @return boolean TRUE if the collection is empty, FALSE otherwise.
     */
    public function isEmpty() {
        return ! $this->_elements;
    }

    /**
     * Gets an iterator for iterating over the elements in the collection.
     *
     * @return ArrayIterator
     */
    public function getIterator() {
    	return new ArrayIterator($this->_elements);
    }
    /**
     * Returns a string representation of this object.
     *
     * @return string
     */
    public function __toString() {
        return __CLASS__ . '@' . spl_object_hash($this);
    }

   
    
    
    
    
    
    /**
     * @var string
     */
	protected $limit = "";
	/**
	 * @var string
	 */
	protected $orderBy = " ORDER BY id ";
	/**
	 * @var string
	 */
	protected $order = " ASC ";
	/**
	 * @var integer
	 */
	protected $state = 0;
	/**
	 * Limit the rows returned
	 * @param integer $start - If $end is not defined, this will limit the number of rows returned.
	 * @param integer $end - If defined, $start defines the starting point of the limitation and $end defines the end
	 */
	public function limit($start, $end = "") {
		$this->limit = " LIMIT " . intval($start) . (intval($end) > 0 ? "," . intval($end) : "") . " ";
		$this->state = 0;
		return $this;
	}
	/**
	 * Order this collection by a title
	 * @param string $by
	 */
	public function orderBy($by) {
		$this->orderBy = " ORDER BY `" .\Idk\Database::getInstance()->orm->driver->escape($by) . "` ";
		$this->state = 0;
		return $this;
	}
	/**
	 * Define the direction of the order (ASC or DESC)
	 * @param string $direction
	 */
	public function order($direction) {
		if(strtolower($direction) == 'asc')
			$this->order = 'ASC';
		else 
			$this->order = 'DESC';
			
		$this->state = 0;
		return $this;
	}
	
	/**
	 * This function will fetch data from the database and convert it to models. 
	 */
	public function fetch() {}
}
