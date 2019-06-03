<?php
namespace Cetera;

class Controller {
    
    private $application;
    
    public function __construct() {
        $this->application = Application::getInstance();
    }

    public function defaultAction() {
        print 'DEFAULT';
    }
    
    public function notFoundAction() {
        print 'NOT FOUND';
    }  
    
    /**
     * Transform an "action" token into a method name
     *
     * @param  string $action
     * @return string
     */
    public static function getMethodFromAction($action)
    {
        $method  = str_replace(['.', '-', '_'], ' ', $action);
        $method  = ucwords($method);
        $method  = str_replace(' ', '', $method);
        $method  = lcfirst($method);
        $method .= 'Action';
        return $method;
    }

}