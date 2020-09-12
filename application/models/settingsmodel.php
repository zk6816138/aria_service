<?php

class Settingsmodel extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->library ("ciredis");
    }

    //获取设置项
    public function getSetting($key){
        $this->db->select('value,update_time');
        $this->db->where('key', $key);
        $row=$this->db->get('settings')->row_array();
        return $row;
    }

    //设置
    public function setSetting($key,$value){
        $data = json_encode($value);
        $this->db->update('settings', ['value'=>$data,'update_time'=>date('Y-m-d H:i:s')], ['key'=>$key]);
        $res = $this->db->affected_rows();
        if ($res){
            $this->ciredis->del('settings:'.$key);

        }
        return $res;
    }
}