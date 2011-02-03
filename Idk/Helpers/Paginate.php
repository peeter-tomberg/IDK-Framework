<?php
/**
 * Pagination helper class
 * @author User
 *
 */

namespace Idk\Helpers;

class Paginate {

	private $page;
	
	private $limit;
	
	private $total_pages;
	/**
	 * @var Paginate
	 */
	public static function create() {
		return new Paginate();
	}

	/**
	 * Sets the URL we'll be navigating to
	 * @param $i
	 * @var Paginate
	 */
	public function setPage($i) {
		$this->page = $i;
		return $this;
	}
	/**
	 * Sets the amount of entries we'll be viewing per page
	 * @param $i
	 * @var Paginate
	 */
	public function setLimit($i) {
		$this->limit = $i;
		return $this;
	}
	/**
	 * Sets the amount of entries we have
	 * @param $i
	 * @var Paginate
	 */
	public function setTotalEntries($i) {
		$this->total_pages = $i;
		return $this;
	}
	/**
	 * The class of the div that contains this pagination navigation
	 * @var string
	 */
	public $class = 'paginate';
	/**
	 * The class of the href that is disabled
	 * @var string
	 */
	public $classDisabled = 'disabled';
	/**
	 * The class of the href that is the current page
	 * @var string
	 */
	public $classCurrent = 'current';
	/**
	 * The name of the variable that is used for paging
	 * @var string
	 */
	public $variable = 'page';
	/**
	 * The word used for "Previous" 
	 * @var string
	 */
	public $previous = 'Previous';
	/**
	 * The word used for "Next" 
	 * @var string
	 */
	public $next = 'Next';
	/**
	 * Renders the actual navigation bar
	 */
	public function render() {
	
		$stages = 3;
		$page = Database::getInstance()->orm->driver->escape($_GET[$this->variable]);

		// Initial page num setup
		if ($page == 0){$page = 1;}
		$prev = $page - 1;	
		$next = $page + 1;							
		$lastpage = ceil($this->total_pages/$this->limit);		
		$LastPagem1 = $lastpage - 1;					
		
		
		$paginate = '';
		if($lastpage > 1) {	
	
			$paginate .= "<div class='".$this->class."'>";
			// Previous
			if ($page > 1){
				$paginate.= "<a href='".$this->page."?".$this->variable."=".$prev."'>".$this->previous."</a>";
			}
			else{
				$paginate.= "<span class='".$this->classDisabled."'>".$this->previous."</span>";	}
			

		
		// Pages	
		if ($lastpage < 7 + ($stages * 2))	// Not enough pages to breaking it up
		{	
			for ($counter = 1; $counter <= $lastpage; $counter++)
			{
				if ($counter == $page){
					$paginate.= "<span class='".$this->classCurrent."'>$counter</span>";
				}else{
					$paginate.= "<a href='".$this->page."?".$this->variable."=$counter'>$counter</a>";}					
			}
		}
		elseif($lastpage > 5 + ($stages * 2))	// Enough pages to hide a few?
		{
			// Beginning only hide later pages
			if($page < 1 + ($stages * 2))		
			{
				for ($counter = 1; $counter < 4 + ($stages * 2); $counter++)
				{
					if ($counter == $page){
						$paginate.= "<span class='".$this->classCurrent."'>$counter</span>";
					}else{
						$paginate.= "<a href='".$this->page."?".$this->variable."=$counter'>$counter</a>";}						
				}
				$paginate.= "...";
				$paginate.= "<a href='".$this->page."?".$this->variable."=$LastPagem1'>$LastPagem1</a>";
				$paginate.= "<a href='".$this->page."?".$this->variable."=$lastpage'>$lastpage</a>";		
			}
			// Middle hide some front and some back
			elseif($lastpage - ($stages * 2) > $page && $page > ($stages * 2))
			{
				$paginate.= "<a href='".$this->page."?".$this->variable."=1'>1</a>";
				$paginate.= "<a href='".$this->page."?".$this->variable."=2'>2</a>";
				$paginate.= "...";
				for ($counter = $page - $stages; $counter <= $page + $stages; $counter++)
				{
					if ($counter == $page){
						$paginate.= "<span class='".$this->classCurrent."'>$counter</span>";
					}else{
						$paginate.= "<a href='".$this->page."?".$this->variable."=$counter'>$counter</a>";}					
				}
				$paginate.= "...";
				$paginate.= "<a href='".$this->page."?".$this->variable."=$LastPagem1'>$LastPagem1</a>";
				$paginate.= "<a href='".$this->page."?".$this->variable."=$lastpage'>$lastpage</a>";		
			}
			// End only hide early pages
			else
			{
				$paginate.= "<a href='".$this->page."?".$this->variable."=1'>1</a>";
				$paginate.= "<a href='".$this->page."?".$this->variable."=2'>2</a>";
				$paginate.= "...";
				for ($counter = $lastpage - (2 + ($stages * 2)); $counter <= $lastpage; $counter++)
				{
					if ($counter == $page){
						$paginate.= "<span class='".$this->classCurrent."'>$counter</span>";
					}else{
						$paginate.= "<a href='".$this->page."?".$this->variable."=$counter'>$counter</a>";}					
				}
			}
		}
					
				// Next
		if ($page < $counter - 1){ 
			$paginate.= "<a href='".$this->page."?".$this->variable."=$next'>".$this->next."</a>";
		}
		else{
			$paginate.= "<span class='".$this->classDisabled."'>".$this->next."</span>";
		}
		$paginate.= "</div>";		
		
		echo $paginate;
		}
	}
}

?>