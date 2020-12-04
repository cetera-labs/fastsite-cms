<?php
namespace Cetera\Api;

use Zend\View\Model\JsonModel;
use Cetera\Application;
use Doctrine\Inflector\InflectorFactory;
use Zend\Stdlib\RequestInterface as Request;
use Zend\Stdlib\ResponseInterface as Response;  
use Cetera\ORM\Paginator;

class EntityController extends AbstractController
{
    private $inflector;
    private $repository;
    private $em;
    private $entity;

    public function dispatch(Request $request, Response $response = null)
    {
        $this->inflector = InflectorFactory::create()->build();
        $this->em = Application::getInstance()->getEntityManager();
        $this->entity = 'Cetera\Entity\\'.$this->inflector->classify( $this->getEvent()->getRouteMatch()->getParam('entity') );
        $this->repository = $this->em->getRepository( $this->entity );
        
        return parent::dispatch($request, $response);
    } 

    public function defaultAction()
    {
        return $this->listAction();
    }

    public function listAction()
    {
        $query = $this->em->createQueryBuilder()
                               ->select('e')
                               ->from($this->entity, 'e')
                               ->setFirstResult(0)
                               ->setMaxResults(5);


        $paginator = new Paginator($query);

        $c = count($paginator);

        $res = [
            'success' => true,
            'total' => (int)$c,
            //'pages' => (int)$list->getPageCount(),            
            'data' => $paginator->asArray(),
        ];
        
        return new JsonModel( $res );
    }     
    

}