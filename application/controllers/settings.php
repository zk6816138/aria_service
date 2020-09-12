<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Settings extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Settingsmodel');
        $this->load->library ("ciredis");
    }

    public function setVersion(){
        $data = ['ver'=>'1.0.2','url'=>'https://app-download-url.oss-cn-shanghai.aliyuncs.com/AriaNg%20Setup%201.0.1.exe'];
        $this->Settingsmodel->setSetting('version',$data);
    }
    
    public function getVersion(){
        $key = 'settings:version';
        $cache = $this->ciredis->get($key);
        if (!$cache){
            $row = $this->Settingsmodel->getSetting('version');
            $value = json_decode($row['value'],true);
            $data = [
                'version'=>$value['ver'],
                'url'=>$value['url'],
                'update_time'=>$row['update_time']
            ];
            if ($row){
                $this->ciredis->set($key,json_encode($data),3600);
                $this->respJson('SUCCESS',$data);
            }
        }
        else {
            $this->respJson('SUCCESS',json_decode($cache,true));
        }
    }
}