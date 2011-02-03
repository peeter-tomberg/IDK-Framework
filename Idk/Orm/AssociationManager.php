<?php
namespace Idk\Orm;
class AssociationManager {
	
	/**
	 * Creates an association between the slaves and the parent, and stores all of the slaves
	 * @param $parent
	 * @param $slaves
	 * @param $foreignKey
	 */
	public static function createOneToManyRelationship(IDKModel $parent, IDKModel $model, $foreignKey) {
		if(!$model->hasForeignKey($foreignKey)) {
			$model->storeForeignKey($foreignKey,$parent->id );
		}
		$model->store();
		
	}
}

?>