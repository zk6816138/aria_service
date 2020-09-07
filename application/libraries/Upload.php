<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Upload
{
    private $file;          //文件信息
    private $fileList;      //文件列表
    private $inputName;     //标签名称
    private $uploadPath;    //上传路径
    private $fileMaxSize;   //最大尺寸
    private $uploadFiles;   //上传文件
    private $fileName;      //文件名
    //允许上传的文件类型
    private $allowExt = array('bmp', 'jpg', 'jpeg', 'png', 'gif');

    /**
     * ImageUploadTool constructor.
     * @param $inputName input标签的name属性
     * @param $uploadPath 文件上传路径
     */
    public function __construct($attr=array())
    {
        $this->inputName = $attr['inputName'];
        $this->uploadPath = $attr['uploadPath'];
        $this->fileName = $attr['filename'];
        $this->fileList = array();
        $this->file = $file = array(
            'name' => null,
            'type' => null,
            'tmp_name' => null,
            'size' => null,
            'errno' => null,
            'error' => null
        );
    }

    /**
     * 设置允许上传的图片后缀格式
     * @param $allowExt 文件后缀数组
     */
    public function setAllowExt($allowExt)
    {
        if (is_array($allowExt)) {
            $this->allowExt = $allowExt;
        } else {
            $this->allowExt = array($allowExt);
        }
    }

    /**
     * 设置允许上传的图片规格
     * @param $fileMaxSize 最大文件尺寸
     */
    public function setMaxSize($fileMaxSize)
    {
        $this->fileMaxSize = $fileMaxSize;
    }

    /**
     * 获取上传成功的文件数组
     * @return mixed
     */
    public function getUploadFiles()
    {
        return $this->uploadFiles;
    }

    /**
     * 得到文件上传的错误信息
     * @return array|mixed
     */
    public function getErrorMsg()
    {
        if (count($this->fileList) == 0) {
            return $this->file['error'];
        } else {
            $errArr = array();
            foreach ($this->fileList as $item) {
                array_push($errArr, $item['error']);
            }
            return $errArr;
        }
    }

    /**
     * 初始化文件参数
     * @param $isList
     */
    private function initFile($isList)
    {
        if ($isList) {
            foreach ($_FILES[$this->inputName] as $key => $item) {
                for ($i = 0; $i < count($item); $i++) {
                    if ($key == 'error') {
                        $this->fileList[$i]['error'] = null;
                        $this->fileList[$i]['errno'] = $item[$i];
                    } else {
                        $this->fileList[$i][$key] = $item[$i];
                    }
                }
            }
        } else {
            $this->file['name'] = $_FILES[$this->inputName]['name'];
            $this->file['type'] = $_FILES[$this->inputName]['type'];
            $this->file['tmp_name'] = $_FILES[$this->inputName]['tmp_name'];
            $this->file['size'] = $_FILES[$this->inputName]['size'];
            $this->file['errno'] = $_FILES[$this->inputName]['error'];
        }
    }

    /**
     * 上传错误检查
     * @param $errno
     * @return null|string
     */
    private function errorCheck($errno)
    {
        switch ($errno) {
            case UPLOAD_ERR_OK:
                return null;
            case UPLOAD_ERR_INI_SIZE:
                return '文件尺寸超过服务器限制';
            case UPLOAD_ERR_FORM_SIZE:
                return '文件尺寸超过表单限制';
            case UPLOAD_ERR_PARTIAL:
                return '只有部分文件被上传';
            case UPLOAD_ERR_NO_FILE:
                return '没有文件被上传';
            case UPLOAD_ERR_NO_TMP_DIR:
                return '找不到临时文件夹';
            case UPLOAD_ERR_CANT_WRITE:
                return '文件写入失败';
            case UPLOAD_ERR_EXTENSION:
                return '上传被扩展程序中断';
        }
    }

    /**
     * 上传文件校验
     * @param $file
     * @throws Exception
     */
    private function fileCheck($file)
    {
        //图片上传过程是否顺利
        if ($file['errno'] != 0) {
            $error = $this->errorCheck($file['errno']);
            throw new Exception($error);
        }
        //图片尺寸是否符合要求
        if (!empty($this->fileMaxSize) && $file['size'] > $this->fileMaxSize) {
            throw new Exception('文件尺寸超过' . ($this->fileMaxSize / 1024) . 'KB');
        }
        //图片类型是否符合要求
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (!in_array($ext, $this->allowExt)) {
            throw new Exception('不符合要求的文件类型');
        }
        //图片上传方式是否为HTTP
        if (!is_uploaded_file($file['tmp_name'])) {
            throw new Exception('文件不是通过HTTP方式上传的');
        }
        //图片是否可以读取
        if (!getimagesize($file['tmp_name'])) {
            throw new Exception('图片文件损坏');
        }
        //检查上传路径是否存在
        if (!file_exists($this->uploadPath)) {
            mkdir($this->uploadPath, null, true);
            chmod($this->uploadPath,0777);
        }
    }

    /**
     * 单文件上传，成功返回true
     * @return bool
     */
    public function acceptSingleFile()
    {
        $this->initFile(false);
        try {
            $this->fileCheck($this->file);
            //$md_name = md5(uniqid(microtime(true), true)) . '.' . pathinfo($this->file['name'], PATHINFO_EXTENSION);
            if (!empty($this->fileName)){
                $md_name = $this->fileName;
            }
            else{
                $md_name = date('YmdHis').'_'.mt_rand(1000,9999) . '.' . pathinfo($this->file['name'], PATHINFO_EXTENSION);
            }

            if (move_uploaded_file($this->file['tmp_name'], $this->uploadPath . $md_name)) {
                $this->uploadFiles = array($this->uploadPath . $md_name);
            } else {
                throw new Exception('文件上传失败');
            }
        } catch (Exception $e) {
            $this->file['error'] = $e->getMessage();
        } finally {
            if (file_exists($this->file['tmp_name'])) {
                unlink($this->file['tmp_name']);
            }
        }
        return empty($this->file['error']) ? true : false;
    }

    /**
     * 多文件上传，全部成功返回true
     * @return bool
     */
    public function acceptMultiFile()
    {
        $this->initFile(true);
        $this->uploadFiles = array();
        for ($i = 0; $i < count($this->fileList); $i++) {
            try {
                $this->fileCheck($this->fileList[$i]);
                $ext = pathinfo($this->fileList[$i]['name'], PATHINFO_EXTENSION);
                $md_name = md5(uniqid(microtime(true), true)) . '.' . $ext;
                if (move_uploaded_file($this->fileList[$i]['tmp_name'], $this->uploadPath . $md_name)) {
                    array_push($this->uploadFiles, $this->uploadPath . $md_name);
                } else {
                    throw new Exception('文件上传失败');
                }
            } catch (Exception $e) {
                $this->fileList[$i]['error'] = $e->getMessage();
            } finally {
                if (file_exists($this->fileList[$i]['tmp_name'])) {
                    unlink($this->fileList[$i]['tmp_name']);
                }
            }
        }
        foreach ($this->fileList as $item) {
            if (!empty($item['error'])) {
                return false;
            }
        }
        return true;
    }
}