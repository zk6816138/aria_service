<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		EllisLab Dev Team
 * @copyright		Copyright (c) 2008 - 2014, EllisLab, Inc.
 * @copyright		Copyright (c) 2014 - 2015, British Columbia Institute of Technology (http://bcit.ca/)
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * CodeIgniter Application Controller Class
 *
 * This class object is the super class that every library in
 * CodeIgniter will be assigned to.
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author		EllisLab Dev Team
 * @link		http://codeigniter.com/user_guide/general/controllers.html
 */
class CI_Controller {

	private static $instance;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		self::$instance =& $this;

		// Assign all the class objects that were instantiated by the
		// bootstrap file (CodeIgniter.php) to local class variables
		// so that CI can run as one big super object.
		foreach (is_loaded() as $var => $class)
		{
			$this->$var =& load_class($class);
		}

		$this->load =& load_class('Loader', 'core');

		$this->load->initialize();
		
		log_message('debug', "Controller Class Initialized");

        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With, Token, Auto-Login, Client-Language, Remember-Password');
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

        if (strtolower($_SERVER['REQUEST_METHOD']) == 'options') {
            exit;
        }

        $this->load->database ();
	}

	public static function &get_instance()
	{
		return self::$instance;
	}

    //输出json
    protected function respJson($status,$arr=[]){
        if (!is_array($arr))return;
        $data['data'] = $arr;
        $data['code'] = STATUS_CODE[$status]['code'];
        $data['status'] = $status;
        if (isset($arr['msg']))$data['msg'] = $this->getTranslateText($arr['msg']);
        header(STATUS_CODE[$status]['text']);
        unset($data['data']['msg']);
        if (count($data['data']) == 0){
            unset($data['data']);
        }
        echo json_encode($data);
        exit();
    }

    //获取请求体json
    protected function getJson(){
        $data = file_get_contents('php://input');
        return json_decode($data, true);
    }

    //获取header头
    protected function getHeader($key){
	    $header = $this->input->get_request_header($key);
	    return $header ? $header : '';
    }

    //获取当前语言
    protected function getCurrentLanguage(){
	    $lang = $this->getHeader('Client-Language');
	    return $lang ? $lang : 'zh_Hans';
    }

    //获取翻译文本
    protected function getTranslateText($key){
        $languages = [];
        require_once(APPPATH.'config/language.php');
        return array_key_exists($key, $languages[$this->getCurrentLanguage()]) ? $languages[$this->getCurrentLanguage()][$key] : $key;
    }

    //创建token
    protected function createToken($userid){
        $token=md5(uniqid($userid,true));
        $this->load->library ("ciredis");
        $this->ciredis->set('user_token:'.$token,$userid);
        return $token;
    }

    //获取用户id
    protected function getUserid(){
        $this->load->library ("ciredis");
        $token = $this->getHeader('Token');
        return $this->ciredis->get('user_token:'.$token);
    }

    //删除token
    protected function removeToken($token){
        $this->load->library ("ciredis");
        $this->ciredis->del('user_token:'.$token);
    }
}
// END Controller class

/* End of file Controller.php */
/* Location: ./system/core/Controller.php */