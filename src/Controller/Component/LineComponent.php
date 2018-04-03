<?php
namespace App\Controller\Component;

use Cake\Controller\Component;

/**
 * LINE Message APIに関するコンポーネント
 *
 * Class LineComponent
 * @package App\Controller\Component
 */
class LineComponent extends Component
{

    /**
     * @param $text
     * @param array $messageData
     * @return array
     */
    public function setTextMessage($text, $messageData = array()){
#      if($replace) $text = str_replace("n", "%0D%0A", $text);
      $message = [
        'type' => 'text',
        'text' => $text,
      ];

      $messageData[] = $message;
      return $messageData;
    }


    /**
     * @param $replyToken
     * @param $messageData
     * @return array
     */
    public function setResponse($replyToken, $messageData){
      $response = [
        'replyToken' => $replyToken,
        'messages'   => $messageData
      ];

      return $response;
    }

    /**
     * メッセージ送信
     *
     * @param $api
     * @param $response
     * @param $ch
     */
    public function sendMessage($uri, $response, $accessToken){
      $ch = curl_init($uri);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
      curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json; charser=UTF-8',
        'Authorization: Bearer ' . $accessToken
      ));
      $result = curl_exec($ch);

      curl_close($ch);

      return $result;
    }


    /**
     * プッシュ通知
     *
     * @param $uri
     * @param $accessToken
     * @param $toUser
     * @param $messageData
     */
    public function sendPush($uri, $accessToken, $toUser, $messageData){
      $response = [
        'to' => $toUser,
        'messages' => $messageData
      ];

      $ch = curl_init($uri);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
      curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json; charser=UTF-8',
        'Authorization: Bearer ' . $accessToken
      ));
      $result = curl_exec($ch);
      curl_close($ch);
      return $result;
    }


    /**
     * プロフィール取得
     *
     * @param $accessToken
     * @param $userId
     * @return bool
     */
    public function getProfileName($accessToken, $userId){

      $uri = "https://api.line.me/v2/bot/profile/{$userId}";
      $ch = curl_init($uri);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json; charser=UTF-8',
        'Authorization: Bearer ' . $accessToken
      ));
      $result = curl_exec($ch);
      curl_close($ch);

      if(isset($result['displayName'])) return $result['displayName'];

      return false;
    }


  /**
   * 画像
   *
   * @param $image
   * @param $preview
   * @param array $messageData
   * @return array
   */
  public function setImgMessage($image, $preview, $messageData = array()){
    $message = [
      'type' => 'image',
      'originalContentUrl' => $image,
      'previewImageUrl'    => $preview,
    ];

    $messageData[] = $message;
    return $messageData;
  }

  /**
   * 位置情報
   *
   * @param $location
   * @return array
   */
  public function setLocationMessage($location, $messageData = array()){
    $message = [
      'type' => 'location',
      'title'     => $location['name'],
      'address'   => $location['address'],
      'latitude'  => $location['latitude'],
      'longitude' => $location['longitude'],
    ];

    $messageData[] = $message;
    return $messageData;
  }

  /**
   * @param string $label
   * @param string $data
   * @param string $mode
   * @param null $max
   * @param null $min
   */
  public function setDatetimepicker($label, $data = POSTBACK_SELECT_PUSH_TIME, $mode=SELECT_TIME, $max=null, $min=null, $initial=null){
    if(empty($max)) $max = self::getMaxTimePicker($mode);
    if(empty($min)) $min = self::getMinTimePicker($mode);
    if(empty($initial)) $initial = self::getInitial($mode);

    $message[] = [
      "type" => "datetimepicker",
      "label" => $label,
      "data" => $data,
      "mode" => $mode,
      "initial" => $initial,
      "max" => $max,
      "min" => $min
    ];

    return $message;
  }

  /**
   * @param $mode
   * @return string
   */
  private function getMaxTimePicker($mode){
    $max = "";
    switch ($mode){
      case SELECT_DATE:
        $max = "2100-12-31";
        break;
      case SELECT_TIME:
        $max = "23:00";
        break;
      case SELECT_DATETIME:
        $max = "2100-12-31T23:59";
        break;
    }
    return $max;
  }

  /**
   * @param $mode
   * @return string
   */
  private function getMinTimePicker($mode){
    $min = "";
    switch ($mode){
      case SELECT_DATE:
        $min = "1900-01-01";
        break;
      case SELECT_TIME:
        $min = "00:00";
        break;
      case SELECT_DATETIME:
        $min = "1900-01-01T00:00";
        break;
    }
    return $min;
  }

  /**
   * @param $mode
   * @return string
   */
  private function getInitial($mode){
    $initial = self::getMinTimePicker($mode);
    return $initial;
  }

  /**
   * @param $label
   * @param $text
   * @return array
   */
  public function confirmAction($label, $text){
    return ["type" => "message",
            "label" => $label,
            "text" => $text];
  }

  /**
   * @param $text
   * @param $actions
   * @return array
   */
  public function setConfirm($text, $actions){
    $confirm = [
      "type" => "confirm",
      "text" => $text,
      "actions" => $actions,
    ];

    return $confirm;
  }

  /**
   * @param $template
   * @param $text
   * @return array
   */
  public function setTemplate($template, $text){
    $message[] = [
      "type"     => "template",
      "altText"  => $text,
      "template" => $template,
    ];

    return $message;
  }

  /**
   * @param null $title
   * @param $text
   * @param array $actions
   * @param string $defaultAction
   * @param string $thumbnailImageUrl
   * @param string $imageAspectRatio
   * @param string $imageSize
   * @param string $imageBackgroundColor
   * @return array
   */
  public function setButton($title=null, $text, $actions=[ACTION_POST_BACK],
                            $defaultAction='',
                            $thumbnailImageUrl='',
                            $imageAspectRatio='',
                            $imageSize='',
                            $imageBackgroundColor=''){
    $button = [
      "type" => "buttons",
      "text" => $text,
      "actions" => $actions,
    ];

    // タイトル
    if(!empty($title)) $button['title'] = $title;

    // 画像URL
    if(!empty($thumbnailImageUrl)) $button['thumbnailImageUrl'] = $thumbnailImageUrl;
    // 画像のアスペクト比
    if(!empty($imageAspectRatio)) $button['imageAspectRatio'] = $imageAspectRatio;
    // 画像の表示形式
    if(!empty($imageSize)) $button['imageSize'] = $imageSize;
    // 画像の背景色
    if(!empty($imageBackgroundColor)) $button['imageBackgroundColor'] = $imageBackgroundColor;

    //画像、タイトル、テキストの領域全体に対して設定できる、タップされたときのアクション
    if(!empty($defaultAction)) $button['defaultAction'] = $defaultAction;

    return $button;
  }
}