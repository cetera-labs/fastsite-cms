<?php
namespace Cetera\Controller;

use Zend\Mvc\Controller\AbstractController;
use Zend\Stdlib\RequestInterface as Request;
use Zend\Stdlib\ResponseInterface as Response;    

class AbstractController extends AbstractController
{
    
    protected $application;
    protected $twig;
    protected $uu;
    protected $c;
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
        
        return parent::dispatch($request, $response);
    }

}