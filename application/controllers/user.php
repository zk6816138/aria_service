<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User extends CI_Controller {

    public function __construct()
    {
        parent::__construct ();
        $this->load->model('Usermodel');
    }

	public function login()
	{
        $autoLogin = $this->getHeader('Auto-Login');
        $remember = $this->getHeader('Remember-Password');
        $token = $this->getHeader('Token');
        $data = $this->getJson();
        $userid = null;
        $user = null;

        if (isset($data['token'])){
            if (!empty($data['token'])){
                $this->removeToken($data['token']);
            }
            unset($data['token']);
        }

        if (($autoLogin || $remember) && count($data) == 1 && isset($data['account']) && $token){ //记住密码或自动登录
            $userid = $this->getUserid();
            if ($userid){
                $user = $this->Usermodel->getUserById($userid);
                if (!$user || $user['account'] != $data['account']) {
                    $this->respJson('ERROR',['msg'=>'Login Failed, Authentication Failed']);
                }
            }
            else{
                $this->respJson('ERROR',['msg'=>'Login Failed, Authentication Failed']);
            }
        }
        else{ //账号密码登录
            if ($data['account'] && $data['password']){
                $user = $this->Usermodel->getUserByAccount($data['account']);
                if ($user && md5($data['account'].md5($data['password'])) == $user['password']){
                    $userid = $user['id'];
                }
                else{
                    $this->respJson('ERROR',['msg'=>'Login Failed, Wrong Account Or Password']);
                }
            }
            else{
                $this->respJson('ERROR',['msg'=>'Login Failed, Please Enter Account And Password']);
            }
        }

        if ($user && $userid){
            $this->removeToken($token);
            $this->Usermodel->updateUser($user['id'], ['last_login_time'=>date('Y-m-d H:i:s'),'last_login_ip'=>ip2long($_SERVER["REMOTE_ADDR"])]);
            $result = [
                'msg'=>'Login Success',
                'account'=>$user['account'],
                'uid'=>$userid,
                'avatar'=>$user['avatar'],
                'last_login_time'=>$user['last_login_time'],
                'token'=>$this->createToken($userid)
            ];

            $this->respJson('SUCCESS', $result);
        }
	}

    public function register()
    {
        $data = $this->getJson();

        if (isset($data['token'])){
            if (!empty($data['token'])){
                $this->removeToken($data['token']);
            }
            unset($data['token']);
        }

        if (empty($data['account'])){
            $this->respJson('ERROR',['msg'=>'Account Cannot Be Empty']);
        }
        if (mb_strlen($data['account'])<6 || mb_strlen($data['account'])>20){
            $this->respJson('ERROR',['msg'=>'Account Must Be 6-20 Digits']);
        }
        if(!preg_match("/[a-zA-Z].*+$/", $data['account']) ||
            !preg_match("/[0-9].*+$/", $data['account']) ||
            preg_match("/[^a-zA-Z0-9].*+$/", $data['account'])){
            $this->respJson('ERROR',['msg'=>'The Account Number Can Only Be A Combination Of Letters And Numbers']);
        }
        if (mb_strlen($data['password'])<6 || mb_strlen($data['password'])>20){
            $this->respJson('ERROR',['msg'=>'Password Must Be 6-20 Digits']);
        }
        if(!preg_match("/[a-zA-Z].*+$/", $data['password']) ||
            !preg_match("/[0-9].*+$/", $data['password']) ||
            preg_match("/[^a-zA-Z0-9].*+$/", $data['password'])){
            $this->respJson('ERROR',['msg'=>'Password Can Only Be A Combination Of Letters And Numbers']);
        }
        if ($this->Usermodel->checkUsername($data['account'])){
            $this->respJson('ERROR',['msg'=>'Account Already Exists']);
        }

        $data['password'] = md5($data['account'].md5($data['password']));
        $data['reg_time'] = $data['last_login_time'] = date('Y-m-d H:i:s');

        $arr = ['male.png','female.png'];
        $random_keys=mt_rand(0,1);
        $data['avatar'] = $arr[$random_keys];

        $userid = $this->Usermodel->regUser($data);
        if ($userid){
            $result = [
                'msg'=>'Registration Success',
                'account'=>$data['account'],
                'uid'=>$userid,
                'avatar'=>$data['avatar'],
                'last_login_time'=>$data['last_login_time'],
                'token'=>$this->createToken($userid),
            ];
            $this->respJson('SUCCESS',$result);
        }
        else{
            $this->respJson('ERROR',['msg'=>'Registration Failed']);
        }
    }

    public function avatar(){
        $userid = $this->getUserid();
        if (!$userid){
            $this->respJson('ERROR',['msg'=>'Upload Failed, Authentication Failed']);
        }
        $image = $this->getJson()['data'];
        if (empty($image)){
            $this->respJson('ERROR',['msg'=>'Upload Failed, Image Error']);
        }
        if (strstr($image,",")){
            $image = explode(',',$image);
            $image = $image[1];
        }
        $path = UPLOAD_PATH.'avatar/';
        if (!file_exists($path)) {
            mkdir($path, null, true);
            chmod($path,0777);
        }
        $file = $path."{$userid}.jpg";
        if (file_put_contents($file, base64_decode($image))){
            $avatar = str_replace(ROOT_PATH,'',$file).'?t='.time();
            $this->Usermodel->updateUser($userid, ['avatar'=>$avatar]);
            $this->respJson('SUCCESS',['msg'=>'Upload Success','avatar'=>$avatar]);
        }
        else{
            $this->respJson('ERROR',['msg'=>'Upload Failed, Error To Write File']);
        }
    }

    public function logout(){
        $userid = $this->getUserid();
        if (!$userid){
            $this->respJson('ERROR',['msg'=>'Authentication Failed']);
        }
        $data = $this->getJson();
        $this->removeToken($data['token']);
        $this->respJson('SUCCESS');
    }
}
