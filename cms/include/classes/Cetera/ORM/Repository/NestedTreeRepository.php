<?php
namespace Cetera\ORM\Repository;

class NestedTreeRepository extends \Gedmo\Tree\Entity\Repository\NestedTreeRepository {
    
    public function recoverParents()
    {
        $tree = $this->childrenHierarchy();
        $this->recoverParent(null, $tree);        
    }        

    protected function recoverParent($parent_id, $children)
    {
        $meta = $this->getClassMetadata();         
            
        foreach ($children as $child) {
            
            $this->_em->getConnection()->update($meta->table['name'],[
                $meta->associationMappings['parent']['joinColumns'][0]['name'] => $parent_id,
            ],[
                'id' => $child['id'],
            ]);
            
            $this->recoverParent($child['id'], $child['__children']);
        }
    }        
    
}