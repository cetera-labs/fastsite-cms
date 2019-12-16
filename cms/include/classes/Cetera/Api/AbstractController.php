<?php
namespace Cetera\Api;

use Zend\Mvc\Controller\AbstractRestfulController,
    Zend\View\Model\JsonModel;
use Zend\Stdlib\RequestInterface as Request;
use Zend\Stdlib\ResponseInterface as Response;    

class AbstractController extends AbstractRestfulController
{
    protected $params = [];
    
    public function dispatch(Request $request, Response $response = null)
    {
        if ($this->requestHasContentType($request, self::CONTENT_TYPE_JSON)) {
            $data = $this->create($this->jsonDecode($request->getContent()));
        }

        $data = $request->getPost()->toArray();
        
        foreach($data as $key => $value) {
            $this->params[ strtolower($key) ] = $value;
        }
        
        return parent::dispatch($request, $response);
    }  

    public function checkParams($params)
    {
        foreach ($params as $param) {
            if (!isset($this->params[ $param ])) {
                return false;
            }
        }
        return true;
    }        
    
    public function invalidParams()
    {
        $t = \Cetera\Application::getInstance()->getTranslator();
        
        return new JsonModel([
            'success' => false,
            'error' => [
                'message' => $t->_('Неправильные параметры')
            ]
        ]);
    }

}