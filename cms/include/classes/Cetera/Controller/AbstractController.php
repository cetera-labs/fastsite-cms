<?php
namespace Cetera\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Stdlib\RequestInterface as Request;
use Laminas\Stdlib\ResponseInterface as Response;    

abstract class AbstractController extends AbstractActionController
{
    
    protected $application;
    protected $twig;
    protected $uu;
    protected $c;
    protected $section;
    protected $material = null;
    
    public $twigParams = [
        'breadcrumbs' => [],
    ];     
    
    public function dispatch(Request $request, Response $response = null)
    {
        $this->application = \Cetera\Application::getInstance();
        $this->twig = $this->application->getTwig();
        $this->uu = $this->application->getUnparsedUrl();
        $this->c = $this->application->getCatalog();
        $this->section = $this->c;
        
        if ($this->uu) try {
            $arr = explode('/',$this->uu);
            $this->material = $this->c->getMaterialByAlias($arr[0]);
            $this->twigParams['breadcrumbs'][] = [
                'name' => $this->material->name,
                'url' => $this->material->url,
            ];
        }
        catch (\Exception $e) {
            $this->material = null;
        }
        
        try {
            return parent::dispatch($request, $response);
        } 
        catch (\Exception $e){
            return $this->serverErrorAction($e);
        }
    }
    
    public function notFoundAction()
    {
        $this->response->setStatusCode(404);
        return '404: Not found';
    }    
    
    public function serverErrorAction($exception)
    {
        $this->getResponse()->setStatusCode(500);
        print $exception->getMessage();        
    }     

}