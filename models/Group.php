<?php

	class Group extends IDKModel {
		
		/**
		 * @Persist
		 */
		public $title;
		/**
	    * @OneToMany(class="permission")
	    * @var OneToManyArray
	    */
	    public $permissions;
	}

?>