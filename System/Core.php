<?php
/**
 * Created by PhpStorm.
 * User: .PolluX
 * Date: 1/3/2016
 * Time: 8:11 PM
 */

namespace TeamManager;

use TeamManager\Exceptions\NotFoundException;
use TeamManager\Utils\StringUtils;

class Core
{
    /**
     * @var \Smarty
     */
    private $smarty;

    /**
     * @var array
     */
    public $settings;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * @var DatabaseManager
     */
    private $internalDatabase;

    /**
     * @var array(DatabaseManager)
     */
    private $serverDatabase;

    /**
     * @var Core
     */
    private static $instance;

    /**
     * @var ExceptionHandler
     */
    protected $exceptionHandler;

    /**
     * @var string
     */
    protected $mainTemplate = 'index.tpl';

    /**
     * Core constructor.
     */
    public function __construct()
    {
        $this->initDefines();
        //$this->setSettings(); == initConfiguration
        $this->initConfiguration();
        //$this->isMaintenance();
        $this->initExceptionHandler();
        $this->initSmarty();
        $this->initDatabases();
        $this->initTranslator();
        $this->initUserManager();
        $this->initApp();

        // TODO: maintenance == TRUE -> smarty
        if($this->settings['page_settings']['maintenance'] === TRUE){
            header('HTTP/1.1 503 Service Unavailable');
            $this->smarty->assign('maintenance', 1);
        }

        Core::$instance = $this;
    }

    /**
     * Init app
     */
    private function initApp()
    {
        $core =& $this;
        require APP_DIR . 'init.php';
    }

    public function execute(){
        // only for development
        // TODO: db creation doctrine
        $this->smarty->debugging = $this->settings['page_settings']['in_dev'];

        // TODO: smarty variables

        $uri = $this->prepareUri();

        $path = explode('/', $uri);
        $page = $this->settings['page_settings']['default_page'] ?: 'Home';

        if(count($path) > 0 && $uri !== '') {
            $page = $path[0];
        }

        $pageFullName = "\\App\\Pages\\" . $page;

        if(!class_exists($pageFullName)) {
            throw new NotFoundException;
        }
        /** @var $pageObject BasePage */
        $pageObject = new $pageFullName();

        if(!($pageObject instanceof BasePage)) {
            throw new NotFoundException;
        }

        $pageObject->setSmarty($this->smarty);
        $pageObject->setCore($this);
        $pageObject->setArgs($path);

        $this->doAuthorization($pageObject);

        /** Container pages allows us to create sub-pages */
        if ($pageObject instanceof ContainerPage) {
            $pageObject = $this->handleContainerPage($pageObject, $pageFullName, $path);
        }

        array_shift($path);

        $pageObject->setSmarty($this->smarty);
        $pageObject->setCore($this);
        $pageObject->setArgs($path);

        if ($pageObject instanceof Controller) {
            // The big deal begins

            $action = isset($path[0]) ? $path[0] : '';

            array_shift($path);
            $pageObject->setArgs($path);

            if (method_exists($pageObject, $action)) {
                $layout = 'index.tpl';

                $result = $pageObject->$action();

                if (stripos($result, ':') !== false) {
                    list($layout, $template) = explode(':', $result);
                } else {
                    $template = $result;
                }

                $this->smarty->assign('pageTemplate', $template);
                $this->smarty->display($layout);
            } else {
                $pageObject->_404();
            }
            return;
        }


        $this->renderPage($pageObject);
    }

    /**
     * Get path info from path_info env variable or from url
     *
     * @return string
     */
    public function getPathInfo()
    {
        static $pathInfo;

        if ($pathInfo == null) {
            if (isset($_SERVER['PATH_INFO'])) {
                $pathInfo = $_SERVER['PATH_INFO'];
            } else {
                $query = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
                $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
                $pathInfo = rtrim(str_replace('?'.$query, '', $uri), '/');
            }
        }
        return $pathInfo;
    }

    /**
     * @return string
     */
    protected function detectBaseUrl()
    {
        $base_url = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
        $base_url .= '://'. $_SERVER['HTTP_HOST'];
        $base_url .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);

        if(StringUtils::endsWith($base_url, '//')){
            $base_url = substr($base_url, 0, -1);
        }
        return $base_url;
    }
    /**
     * Prepare uri for page routing
     *
     * @return string
     */
    protected function prepareUri()
    {
        $query = $this->getPathInfo();

        $basePath = $this->settings['page_settings']['base_path'];
        // Cut / from begin and end
        if($basePath[0] == '/') {
            $basePath = substr($basePath, 1);
        }
        if($basePath[strlen($basePath) - 1] == '/') {
            $basePath = substr($basePath, 0, strlen($basePath) - 1);
        }

        $query = str_replace($basePath, '', $query);

        return trim($query, '/');
    }

    /**
     * Do authorization for a page
     *
     * @param BasePage $page
     */
    protected function doAuthorization(BasePage $page)
    {
        if (method_exists($page, 'authorize')) {
            if ( ! $page->authorize($this)) {
                $page->redirectTo('');
                exit;
            }
        }
    }

    /**
     * @param $pageObject BasePage
     * @param $pageFullName String
     * @param array $path
     * @return BasePage
     * @throws NotFoundException
     */
    protected function handleContainerPage($pageObject, $pageFullName, array &$path) {
        $pageFullName = $pageFullName . '\\' . (isset($path[1]) ? $path[1] : '');
        if (! class_exists($pageFullName)) {
            echo $pageFullName . '<br />';
            throw new NotFoundException;
        }

        $pageObject->preRender($this, $this->smarty);

        array_shift($path);
        $pageObject = new $pageFullName;
        if ($pageObject instanceof ContainerPage) {
            return $this->handleContainerPage($pageObject, $pageFullName, $path);
        } else {
            $this->doAuthorization($pageObject);
        }

        return $pageObject;
    }

    /**
     * Render page
     *
     * @param BasePage $page
     */
    protected function renderPage(BasePage $page)
    {
        $page->preRender($this, $this->smarty);
        $this->smarty->assign('pageTemplate', $page->getTemplate($this, $this->smarty));
        $this->smarty->display($this->mainTemplate);
        $page->postRender($this, $this->smarty);
    }

    private function initDatabases() {
        // TODO: mysql db anbinden

        $mappingsPath = SYSTEM_DIR.'mappings'.DS;

        $internalMappings =  $mappingsPath.'internal'.DS;

        $this->internalDatabase = new DatabaseManager($this->settings['internal_database'], $internalMappings);

        $env = $this->inDev() ? 'dev' : 'production';

        /*$accountDbSettings = $this->settings['server_database'][$env]['account'];
        $playerDbSettings =  $this->settings['server_database'][$env]['player'];

        $this->serverDatabase = [];
        $this->serverDatabase['account'] = new DatabaseManager($accountDbSettings, $accountMappings);
        $this->serverDatabase['player'] = new DatabaseManager($playerDbSettings, $playerMappings);*/
    }

    /**
     * @return bool
     */
    public function inDev() {
        return $this->settings['page_settings']['in_dev'] == true;
    }

    private function initExceptionHandler() {
        $this->exceptionHandler = new ExceptionHandler($this);

        $this->addException('\Quantum\Exceptions\NotFoundException', function (Core $core) {
            $core->displayNotFound();
        });
    }

    public function addException($class, \Closure $closure)
    {
        $this->exceptionHandler->pushError($class, $closure);
    }

    /**
     * Throw page not found
     */
    public function displayNotFound()
    {
        $this->smarty->assign('pageTemplate', 'error/404.tpl');
        $this->smarty->display('index.tpl');
        exit;
    }

    public function getUserManager() {
        return $this->userManager;
    }

    private function initUserManager() {
        $this->userManager = new UserManager($this);
    }

    private function initDefines() {
        if(!defined('DS')) {
            define('DS', DIRECTORY_SEPARATOR);
        }

        if(!defined('SYSTEM_DIR')) {
            define('SYSTEM_DIR', dirname(__FILE__) . DS);
        }

        if(!defined('ROOT_DIR')) {
            define('ROOT_DIR', realpath(SYSTEM_DIR . '..') . DS);
        }

        if (!defined('APP_DIR')) {
            define('APP_DIR', ROOT_DIR . 'App' . DS);
        }

        if (!defined('STORAGE_DIR')) {
            define('STORAGE_DIR', ROOT_DIR . 'Storage' . DS);
        }
    }

    /**
     * Expose the settings to the other classes
     *
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    public function setSettings(){
        $this->settings = $this->parseINI('config.ini');
    }

    /**
     * Sets the current login into the session
     * @param $account Account
     */
    public function setAccount($account) {
        // TODO: account class
        $this->userManager->setAccount($account);
    }

    /**
     * Gets the current logged in user
     * @return null|Account
     */
    public function getAccount() {
        return $this->userManager->getCurrentAccount();
    }

    /**
     * Loads the configuration file
     */
    private function initConfiguration() {
        if(!file_exists(ROOT_DIR . 'config.ini')) {
            echo 'No config.ini found. You\'ll be redirected to the Installation.';
            $this->timedRefresh(10, 'Install');
            exit;
        }

        $this->settings = $this->parseINI(ROOT_DIR . 'config.ini');
        $this->settings['external_path'] = $this->detectBaseUrl();
    }

    /**
     * Initialise the template system
     */
    private function initSmarty() {
        $this->smarty = new \Smarty();
        $this->smarty->setTemplateDir(APP_DIR.'templates');
        $this->smarty->setCompileDir(APP_DIR.'templates/compiled');

        $pluginDirectories = $this->smarty->getPluginsDir();
        $pluginDirectories[] = SYSTEM_DIR . 'Smarty';
        $this->smarty->setPluginsDir($pluginDirectories);
    }

    public function createHash($clean, $method) {
        switch($method){
            case 'mysql':
                // Default MySQL5 Hash implementation
                return '*' . strtoupper(sha1(sha1($clean, true)));
            case 'md5':
                return md5($clean);
            case 'sha1':
                return sha1($clean);
            case 'bcrypt':
                // bCrypt, returns FALSE if false
                return password_hash($clean, PASSWORD_BCRYPT);
            default:
                return FALSE;
        }
    }

    /**
     * @param $time int value in seconds
     * @param $location string page
     */
    public function timedRefresh($time, $location){
        header('Refresh: '.$time.';url='.$location.'');
    }

    /**
     * Generates html code which display recaptcha
     * @return string
     */
    public function getRecaptchaHtml() {
        if($this->settings['in_dev'])
            return '';

        return '<script src="https://www.google.com/recaptcha/api.js"></script>' .
        '<div class="g-recaptcha" data-sitekey="' . $this->settings['recaptcha']['public'] . '"></div>';
    }

    /**
     * Check if the captcha was solved
     * @return boolean
     */
    public function validateCaptcha() {
        if($this->settings['in_dev']) {
            return true;
        }

        $recaptchURL = 'https://www.google.com/recaptcha/api/siteverify';
        $secret = $this->settings['recaptcha']['private'];
        $data = array(
            'secret' => $secret,
            'response' => $_POST['g-recaptcha-response'],
            'remoteip' => $_SERVER['REMOTE_ADDR']
        );
        $request = array(
            'http' => array(
                'header' => 'Content-type: application/x-www-form-urlencoded\r\n',
                'method' => 'POST',
                'content' => http_build_query($data)
            )
        );
        $context = stream_context_create($request);
        $result = file_get_contents($recaptchURL, false, $context);
        $json = json_decode($result);
        return $json->{'success'} == 1;
    }

    private function addMessage($type, $message, array $format = array()) {
        $messages = $this->smarty->getTemplateVars($type);
        if($messages === null) {
            $messages = array();
        }
        $message = $this->translator->translate($message);
        foreach($format as $key => $value) {
            $message = str_replace('%'.$key.'%', $value, $message);
        }

        $messages[] = $message;
        $this->smarty->assign($type, $messages);
    }

    private function initTranslator() {
        // TODO: read lang from database
        $this->translator = new Translator('DE', $this->internalDatabase);
    }

    /**
     * Returns the database manager for the database given
     * @param $type string Database type (player, account, log)
     * @return DatabaseManager
     */
    public function getServerDatabase($type) {
        return $this->serverDatabase[$type];
    }

    /**
     * @return DatabaseManager
     */
    public function getInternalDatabase() {
        return $this->internalDatabase;
    }

    /**
     * @param $file
     * @return array
     */
    private function parseINI($file){
        $parsed = parse_ini_file($file, true);
        return $parsed;
    }

    public function errorDiv($type = NULL, $text = 'test'){
        // TODO: rename this / proper name
        switch($type){ // .info,.success,.warning,.error
            case 'info':
                echo '<div class="info">'.$text.'</div>';
                break;
            case 'success':
                echo '<div class="success">'.$text.'</div>';
                break;
            case 'warning':
                echo '<div class="warning">'.$text.'</div>';
                break;
            case 'error':
                echo '<div class="error">'.$text.'</div>';
                break;
            default:
                echo '<div id="admin_box"><div class="warning">Wrong type ("'.$type.'") given for function "errorDiv"</div>The provided error message was: "'.$text.'"</div>';
                break;
        }
    }

    public static function getInstance() {
        return Core::$instance;
    }

    /**
     * @return Translator
     */
    public function getTranslator() {
        return $this->translator;
    }

    /**
     * @return string
     */
    public function getMainTemplate() {
        return $this->mainTemplate;
    }

    /**
     * @param string $mainTemplate
     */
    public function setMainTemplate($mainTemplate) {
        $this->mainTemplate = $mainTemplate;
    }
}