<?php
namespace Cetera\Api;

use Laminas\Mvc\Controller\AbstractRestfulController,
    Laminas\View\Model\JsonModel;

class IndexController extends AbstractRestfulController
{

    public function indexAction()
    {
        return new JsonModel(['response' => true]);
    }

}