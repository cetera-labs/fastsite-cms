<?php
namespace Cetera\Api;

use Laminas\View\Model\JsonModel;
use Cetera\Application;
use Doctrine\Inflector\InflectorFactory;
use Laminas\Stdlib\RequestInterface as Request;
use Laminas\Stdlib\ResponseInterface as Response;  
use Cetera\ORM\Paginator;

class EntityController extends AbstractController
{
    private $inflector;
    private $repository;
    private $em;
    private $entity;

    public function dispatch(Request $request, ?Response $response = null)
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
        if (!$this->params['page']) {
            $this->params['page'] = 1;
        }
        if (!$this->params['items_per_page']) {
            $this->params['items_per_page'] = 10;
        }          
        
        $query = $this->em->createQueryBuilder()
                               ->select('e')
                               ->from($this->entity, 'e')
                               ->setFirstResult( $this->params['items_per_page']*($this->params['page']-1) )
                               ->setMaxResults( $this->params['items_per_page'] );


        $paginator = new Paginator($query);

        $res = [
            'success' => true,
            'total' => count($paginator),           
            'data' => $paginator->asArray(),
        ];
        
        return new JsonModel( $res );
    }     
    

}
