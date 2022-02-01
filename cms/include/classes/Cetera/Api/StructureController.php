<?php
namespace Cetera\Api;

use Laminas\View\Model\JsonModel;

class StructureController extends AbstractController
{

    public function getList()
    {
        return $this->get(0);
    }
    
    public function get($id)
    {
        return new JsonModel(['response' => true]);
    }    

}