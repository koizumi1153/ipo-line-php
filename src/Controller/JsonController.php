<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Json Controller
 *
 * Class YohaneController
 * @package App\Controller
 */
class JsonController extends AppController
{
  public function index(){
    $this->set('encode','');
    $this->set('decode','');
  }

  public function encode(){
    $encode = "";
    $decode = "";
    if(isset($this->request->data['encode'])){
      $decode = $this->request->data['encode'];
      $data = explode(",", $decode );
      $encode = json_encode($data,JSON_UNESCAPED_UNICODE);
    }

    $this->set('encode',$encode);
    $this->set('decode',$decode);

    $this->render('index');

  }

  public function decode(){
    $encode = "";
    $decode = "";
    if(isset($this->request->data['decode'])){
      $encode = $this->request->data['decode'];
      $data = json_decode($encode,true);
      $decode = implode(",", $data);
    }

    $this->set('encode',$encode);
    $this->set('decode',$decode);

    $this->render('index');
  }
}