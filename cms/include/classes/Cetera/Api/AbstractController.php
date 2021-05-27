<?php
namespace Cetera\Api;

use Zend\Mvc\Controller\AbstractRestfulController,
    Zend\View\Model\JsonModel;
use Zend\Stdlib\RequestInterface as Request;
use Zend\Stdlib\ResponseInterface as Response;  
use \Firebase\JWT\JWT;

class AbstractController extends AbstractRestfulController
{
    protected $params = [];
    public $user = null;
    
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
        try {
            $decoded = JWT::decode($this->getBearerToken(), $this->getJwtKey(), ['HS256']);
            $this->user = \Cetera\User::getByToken( $decoded->data->id );
            \Cetera\Application::getInstance()->setUser($this->user);
        }
        catch (\Exception $e){
            $this->getResponse()->setStatusCode(401);
            throw new \Exception('Ошибка авторизации');
        }
        if (is_array($groups)) {
            foreach ($groups as $g) {
                if (!$this->user->hasRight($g)) {
                    $this->getResponse()->setStatusCode(401);
                    throw new \Exception('Недостаточно полномочий');                    
                }
            }
        }
        elseif( is_integer($groups) ) {
            if (!$this->user->hasRight($groups)) {
                $this->getResponse()->setStatusCode(401);
                throw new \Exception('Недостаточно полномочий');                    
            }            
        }
    }
    
    protected function getJwtKey() {
        $key = \Cetera\Application::getInstance()->getVar('api_jwt_key');
        if (!$key) {
            throw new \Exception('Не задан api_jwt_key');
        }
        return $key;
    }        

    public function getTokenAction() {
        
        if (!$this->checkParams(['login']) || !$this->checkParams(['password'])) {
            return $this->invalidParams();
        }
        
        $user = null;
		if (isset($this->params['login'])) $user = User::getByLogin($this->params['login']);  
		if (!$user && isset($this->params['email'])) $user = User::getByEmail($this->params['email']);

		if (!$user || !$user->isEnabled()) {
			throw new \Exception('Пользователь не найден');
		}
        
		if (!$user->checkPassword($this->params['password'])) {
			throw new \Exception('Неправильный пароль');
		}        
        
        $token = [
           "iss" => "http://any-site.org",
           "aud" => "http://any-site.com",
           "iat" => time(),
           "nbf" => time(),
           "data" => array(
               "id" => $user->id,
           )
        ];       
        
        return JWT::encode($token, $this->getJwtKey());    
    }

}