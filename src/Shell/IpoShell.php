<?php
namespace App\Shell;

use Cake\Console\Shell;
use Cake\Controller\ComponentRegistry;
use App\Controller\Component\AqoursComponent;
use App\Controller\Component\RakutenComponent;

class IpoShell extends Shell
{

  public function initialize() {
    // component
    $this->Aqours  = new AqoursComponent(new ComponentRegistry());
    $this->Rakuten = new RakutenComponent(new ComponentRegistry());
  }

  /**
   * information取得
   */
  public function information()
  {
    $result = array();
    $update = array();
    $url = IPO_API_URL;
    $json = file_get_contents($url);
    if(!empty($json)){
      $information = json_decode($json , true);
      if(isset($information['data'])){
        # 全データ取得
        $Info = $this->Ipo->getInfoAll();
        $ipoCodes = array_column($Info, NULL,'code');

        foreach($information['data'] as $row){
          $data = array();
          $code = $row['code'];
          if(isset($ipoCodes[$code])) {
            // p_uri更新チェック
            $p_uri = $row['p_uri'];
            $p_kari = $row['p_kari'];
            if(
              (empty($ipoCodes[$code]['p_uri']) && !empty($p_uri)) ||
              (empty($ipoCodes[$code]['p_kari']) && !empty($p_kari))
            ){
              $data['id'] = $code;
              $data['p_uri'] = $p_uri;
              $data['p_kari'] = $p_kari;
              $update[] = $data;
            }
          }else{
            // 未存在のため設定
            $data['id'] = $row['code'];
            $data['code'] = $row['code'];
            $data['date'] = $row['date'];
            $data['name'] = $row['name'];
            $data['market_id'] = $row['market_id'];
            $data['market_id'] = $row['market_name'];
            $data['url'] = $row['url'];
            $data['p_kari'] = $row['p_kari'];
            $data['v_kobo'] = $row['v_kobo'];
            $data['p_uri'] = $row['p_uri'];
            $data['v_uri'] = $row['v_uri'];
            $data['unit'] = $row['unit'];
          }
        }
      }

      // 更新処理
      if(!empty($update)){
        foreach($update as $info){
          $this->Ipo->updateInfo($info);
        }
      }

      // 追加処理
      if(!empty($result)){
        $this->Ipo->setInfo($result);
      }
    }
  }

  /**
   * schedule取得
   */
  public function schedule(){
    $now = date('Y-m-d H:i:s');
    $information = $this->Ipo->getInfoFromDate();
    $result = array();

    if(!empty($information)) {
      $url = AQOURS_NICONICO_URL;
      foreach($information as $info) {
        $data = array();
        $code = $info['code'];
        $html = file_get_contents($url.$code);
        $doc = \phpQuery::newDocument($html);
        $book_building_date = trim($doc['#mainspace #page .tb_brown_sp']->find('table:eq(1)')->find('.kyotyo1')->text());

        $start = '';
        $end   = '';
        preg_match("@([0-9]{4,})/([0-9]{1,2})/([0-9]{1,2})@",$book_building_date,$date);
        if(!empty($date)) {
          $start = $date[0];
        }

        $str2 = substr($book_building_date, 10);
        preg_match("@([0-9]{4,})/([0-9]{1,2})/([0-9]{1,2})@",$book_building_date,$date2);
        if(!empty($date2)){
          $end =$date2[0];
        }else {
          preg_match("@([0-9]{1,2})/([0-9]{1,2})@", $str2, $date3);
          $end =$date3[0];
        }
        $attention = trim($doc['#mainspace #page .tb_brown_sp']->find('table:eq(1)')->find('td:eq(0)')->text());

        $data['code'] = $code;
        $data['url']  = $url.$code;
        $data['listed_date'] = $info['date'];
        $data['book_building_date'] = $book_building_date;
        $data['book_building_start_date'] = $start;
        $data['book_building_end_date'] = $end;
        $data['attention'] = $attention;
        $result[] = $data;
      }

      if(!empty($result)){
        $this->Ipo->setSchedule($result);
      }
    }
  }

}