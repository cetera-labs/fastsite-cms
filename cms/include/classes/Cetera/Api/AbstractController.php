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
            $data = $this->jsonDecode($request->getContent());
        }
        else {
            $data = $request->getPost()->toArray();
        }
        
        if (is_array($data)) {
            foreach($data as $key => $value) {
                $this->params[ strtolower($key) ] = $value;
            }
        }
        
        try {
            return parent::dispatch($request, $response);
        } 
        catch (\Exception $e){
            return $this->serverErrorAction($e);
        }
    }  
    
    public function serverErrorAction($exception)
    {
        if ($this->getResponse()->getStatusCode() < 300) {
            $this->getResponse()->setStatusCode(500);
        }
        return new JsonModel([
            'success' => false,
            'error' => [
                'message' => $exception->getMessage()
            ]
        ]);        
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
    
    public function getBearerToken() {
        $header = $this->getRequest()->getHeaders('Authorization');
        // HEADER: Get the access token from the header
        if ($header) {
            if (preg_match('/Bearer\s(\S+)/', $header->getFieldValue(), $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    /*
    * Проверка авторизации
    */
    public function checkAuth($groups = null)
    {
        $user = \Cetera\Application::getInstance()->getUser();
        if (!$user){
            $this->getResponse()->setStatusCode(401);
            throw new \Exception('Ошибка авторизации');
        } elseif (is_array($groups)) {
            foreach ($groups as $g) {
                if (!$user->hasRight($g)) {
                    $this->getResponse()->setStatusCode(401);
                    throw new \Exception('Недостаточно полномочий');                    
                }
            }
        }
        elseif( is_integer($groups) ) {
            if (!$user->hasRight($groups)) {
                $this->getResponse()->setStatusCode(401);
                throw new \Exception('Недостаточно полномочий');                    
            }            
        }
    }    

}