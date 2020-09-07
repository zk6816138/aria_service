<?php

class Usermodel extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    //注册用户
    public function regUser($data){
        $this->db->insert('user',$data);
        return $this->db->insert_id();
    }

    //检查用户名
    public function checkUsername($account){
        $this->db->where('account', $account);
        $row=$this->db->get('user')->num_rows();
        return $row?true:false;
    }

    //通过id查找用户
    public function getUserById($id){
        $this->db->where('id', $id);
        $row=$this->db->get('user')->row_array();
        return $row;
    }

    //通过用户名查找用户
    public function getUserByAccount($account){
        $this->db->where('account', $account);
        $row = $this->db->get('user')->row_array();
        return $row;
    }

    //更新用户信息
    public function updateUser($userid,$data){
        $this->db->update('user', $data, ['id'=>$userid]);
        return $this->db->affected_rows();
    }
}