<?php

class Bootstrap extends Yaf\Bootstrap_Abstract{

    // public function _initSession (Yaf\Dispatcher $dispatcher)
    // {
    //     Yaf\Session::getInstance()->start();
    //     header('content-type:text/html;charset=utf-8');
    // }

    public function _initConfig() {
        $config = Yaf\Application::app()->getConfig();
        Yaf\Registry::set('config', $config);
    }

    public function _initDb() {
        if (file_exists($_SERVER['DOCUMENT_ROOT'].'/crm/config.php')) {
            include($_SERVER['DOCUMENT_ROOT'].'/crm/config.php');
        } else if (file_exists($_SERVER['DOCUMENT_ROOT'].'/opm/config.php')) {
            include($_SERVER['DOCUMENT_ROOT'].'/opm/config.php');
        } 
        if (empty($sugar_config['dbconfig'])) {
            throw new RuntimeException('dbconfig not found.', 1);
        }
        $db_config = new Yaf\Config\Simple([
            'type' => $sugar_config['dbconfig']['db_type'],
            'host' => $sugar_config['dbconfig']['db_host_name'],
            'usr' => $sugar_config['dbconfig']['db_user_name'],
            'pwd' => $sugar_config['dbconfig']['db_password'],
            'dbname' => $sugar_config['dbconfig']['db_name'],
            'charset' => 'utf8'
        ]);
        Yaf\Registry::set('db_config', $db_config);
        \DB\Factory::create();
    }

    public function _initRoute(Yaf\Dispatcher $dispatcher) {
        $dispatcher->setDefaultController('Dbvcs');
        // 最后注册的路由协议, 最先尝试路由
        // $router = Yaf\Dispatcher::getInstance()->getRouter();
        // $route = new Yaf\Route\Rewrite('/dbvcs', ['controller' => 'index', 'action' => 'index']);
        // $router->addRoute('default', $route);
        // $route = new Yaf\Route\Rewrite('/dbvcs/:action', ['controller' => 'index', 'action' => ':action']);
        // $router->addRoute('action', $route);
        // $route = new Yaf\Route\Rewrite('/dbvcs/:action/*', ['controller' => 'index', 'action' => ':action']);
        // $router->addRoute('action_param', $route);
        // $route = new Yaf\Route\Rewrite('/dbvcs/version/:version', ['controller' => 'index', 'action' => 'version']);
        // $router->addRoute('verson', $route);
    }

    public function _initStreamWrapper(Yaf\Dispatcher $dispatcher) {
        GitLab\StreamWrapper::register();
    }

}