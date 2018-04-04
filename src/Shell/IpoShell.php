<?php
namespace App\Shell;


use Cake\Console\Shell;
use Cake\Controller\ComponentRegistry;
use App\Controller\Component\IpoComponent;
use App\Controller\Component\LineComponent;
require_once '/var/www/ipo/app/vendor/phpQuery-onefile.php';


class IpoShell extends Shell
{

  public function initialize() {
    // component
    $this->Ipo  = new IpoComponent(new ComponentRegistry());
    $this->Line = new LineComponent(new ComponentRegistry());
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
            $data['market_id'] = '';
            $data['market_name'] = $row['market_name'];
            $data['url'] = $row['url'];
            $data['p_kari'] = $row['p_kari'];
            $data['v_kobo'] = $row['v_kobo'];
            $data['p_uri'] = $row['p_uri'];
            $data['v_uri'] = $row['v_uri'];
            $data['unit'] = $row['unit'];
            $result[] = $data;
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
      $url = IPO_SCHUDULE_BASE_URL;
      foreach($information as $info) {
        $data = array();
        $code = $info['code'];
        $html = @file_get_contents($url.$code);
        if($html === false) continue;
        $doc = \phpQuery::newDocument($html);
        if(!empty(trim($doc['#mainspace']))) {
          $book_building_date = trim($doc['#mainspace #page .tb_brown_sp']->find('table:eq(1)')->find('.kyotyo1')->text());

          $start = '';
          $end = '';
          preg_match("@([0-9]{4,})/([0-9]{1,2})/([0-9]{1,2})@", $book_building_date, $date);
          if (!empty($date)) {
            $start = $date[0];
          }

          $str2 = substr($book_building_date, 10);
          preg_match("@([0-9]{4,})/([0-9]{1,2})/([0-9]{1,2})@", $str2, $date2);
          if (!empty($date2)) {
            $end = $date2[0];
          } else {
            preg_match("@([0-9]{1,2})/([0-9]{1,2})@", $str2, $date3);
            if (!empty($date3)) $end = date('Y')."/".$date3[0];
          }
          $attention = trim($doc['#mainspace #page .tb_brown_sp']->find('table:eq(1)')->find('td:eq(0)')->text());

          $lottery_date = '';
          $lottery_str = trim($doc['#mainspace #page .tb_brown_sp']->find('table:eq(2)')->find('td:eq(0)')->text());
          preg_match("@([0-9]{4,})/([0-9]{1,2})/([0-9]{1,2})@", $lottery_str, $date4);
          if (!empty($date4)) {
            $lottery_date = $date4[0];
          } else {
            preg_match("@([0-9]{1,2})/([0-9]{1,2})@", $lottery_str, $date5);
            if (!empty($date5)) $lottery_date = date('Y')."/".$date5[0];
          }

          $data['code'] = $code;
          $data['url'] = $url . $code;
          $data['listed_date'] = $info['date'];
          $data['book_building_date'] = $book_building_date;
          $data['book_building_start_date'] = $start;
          $data['book_building_end_date'] = $end;
          $data['attention'] = $attention;
          $data['lottery_date'] = $lottery_date;
          $result[] = $data;
        }
      }

      if(!empty($result)){
        $this->Ipo->setSchedule($result);
      }
    }
  }

  /**
   * push
   * 抽選日(当日20時)
   * BB開始通知(前日20時)
   * BB終了通知(前日20時)
   *
   */
  public function push(){
    $messageData = array();
    // 抽選日情報取得
    $lotterySchedule = $this->Ipo->getLotterySchedule();
    if(!empty($lotterySchedule)){
      $text = "";
      $cnt=0;
      foreach($lotterySchedule as $schedule){
        $code = $schedule['code'];
        $date = $schedule['lottery_date'];
        $info = $this->Ipo->getInfoFromCode($code);

        if($cnt != 0) $text .= "\n\n";
        $text .= $info['name']."の当選発表日です。";
        $str = $info['name']."の当選発表日です。";
        $this->Ipo->tweet($str);
        $cnt++;
      }

      if(!empty($text)){
        $messageData = $this->Line->setTextMessage($text, $messageData);
      }
    }

    // BB開始通知(前日20時)
    $bookBuildingStartSchedule = $this->Ipo->getBookBuildingStartDateSchedule();
    if(!empty($bookBuildingStartSchedule)){
      $text = "";
      $cnt=0;
      foreach($bookBuildingStartSchedule as $schedule){
        $code = $schedule['code'];
        $date = $schedule['book_building_date'];
        $info = $this->Ipo->getInfoFromCode($code);

        if($cnt != 0) $text .= "\n\n\n";
        $text .= "明日から".$info['name']."のブックビルディング期間です。\n\n{$schedule['attention']}\n\n{$date}\n\n{$schedule['url']}";
        $str = "明日から".$info['name']."の抽選申込期間です。\n{$date}";
        $this->Ipo->tweet($str);
        $cnt++;
      }

      if(!empty($text)){
        $messageData = $this->Line->setTextMessage($text, $messageData);
      }
    }

    // BB終了通知(前日20時)
    $bookBuildingEndSchedule = $this->Ipo->getBookBuildingEndDateSchedule();
    if(!empty($bookBuildingEndSchedule)){
      $text = "";
      $cnt=0;
      foreach($bookBuildingEndSchedule as $schedule){
        $code = $schedule['code'];
        $date = $schedule['book_building_date'];
        $info = $this->Ipo->getInfoFromCode($code);

        if($cnt != 0) $text .= "\n\n";
        $text .= $info['name']."のブックビルディング期間は明日まで！";
        $cnt++;
      }

      if(!empty($text)){
        $messageData = $this->Line->setTextMessage($text, $messageData);
      }
    }

    if(!empty($messageData)){
      $this->Ipo->sendMessage($messageData, IPO_ACCESS_TOKEN);
    }
  }

  /**
   * 上場日の通知
   */
  public function listing(){
    // 当日上場データを取得
    $information = $this->Ipo->getInfoFromDate();
    if(!empty($information)){
      $text = "";
      $cnt=0;
      foreach($information as $info){
        if($cnt != 0) $text .= "\n\n";
        $str = $info['name']."(".$info['code'].")が本日上場です！";
        $this->Ipo->tweet($str);
      }
    }
  }

  public function test(){
    $str="test";
    $this->Ipo->tweet($str);
  }

}