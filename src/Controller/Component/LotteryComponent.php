<?php
namespace App\Controller\Component;

use Cake\Controller\Component;

/**
 * 抽選関連コンポーネント
 *
 * Class LotteryComponent
 * @package App\Controller\Component
 */
class LotteryComponent extends Component
{

  /**
   * マスターからjson($key)内にkeywordが含まれているかチェック
   *
   * @param $masters
   * @param $keyword
   * @param $key
   *
   * @return $kind
   */
  public function lotteryJson($masters, $keyword, $key = "search"){
    $kind = 999; //default

    $hitKinds = array();
    foreach($masters as $master){
      $json = $master[$key];
      $retrieval = json_decode($json);

      if(!empty($retrieval)){
        foreach($retrieval as $words){
          if(strpos($keyword, $words) !== false){
            $hitKinds[] = $master['kind'];
            break;
          }
        }
      }
    }

    if(!empty($hitKinds)){
      $rand = mt_rand(0, (count($hitKinds) -1));
      $kind = $hitKinds[$rand];
    }

    return $kind;
  }

  /**
   * マスター内からランダムに1データ取得
   *
   * @param $masters
   * @return array
   */
  public function lotteryMaster($masters){
    $master = array();

    $masters = array_values($masters);
    // 抽選番号
    $rand = mt_rand(0, (count($masters) -1));
    if(isset($masters[$rand])){
      $master = $masters[$rand];
    }

    return $master;
  }
}