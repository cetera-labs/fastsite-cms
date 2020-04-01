<?php
namespace Cetera\Api;

use Zend\Mvc\Controller\AbstractRestfulController,
    Zend\View\Model\JsonModel;

class IndexController extends AbstractRestfulController
{

    public function indexAction()
    {
        return new JsonModel(['response' => true]);
    }

}