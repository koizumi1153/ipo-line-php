<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Project Ipo Controller
 *
 * Class IpoController
 * @package App\Controller
 */
class IpoController extends AppController
{
    public $components = ["Line", "Ipo", "Lottery"];

   public function index()
   {
      $this->autoRender = false;

      $messageData = array();
      $request = $this->request->data;

      if(!empty($request['events'])) {
        // ユーザーから送られてきたデータ
        $event = $request['events'][0];
        $type = $event['type'];
        $replyToken = $event['replyToken'];

        $userId = $event['source']['userId'];

        if ($type == 'follow') {
          $userName = $this->Line->getProfileName(IPO_ACCESS_TOKEN, $userId);

          $this->Ipo->setUsers($userId, $userName);

          $text = "IPO関連の情報をお知らせします。";
          $messageData = $this->Line->setTextMessage($text, $messageData);

          $text = "ブックビルディング開始前日20時、ブックビルディング終了前日20時、当選発表20時に通知します。";
          $messageData = $this->Line->setTextMessage($text, $messageData);
        } elseif ($type == 'unfollow') {
          $this->Ipo->deleteUser($userId);
        } elseif ($type == "message") {
          // text
          $text = $event['message']['text'];

          // 大分類取得 複数ある場合はランダム
          $kindMaster = $this->Ipo->getKinds();
          $kind = $this->Lottery->lotteryJson($kindMaster, $text);

          switch ($kind) {
            case WORDS:
              $messageData = $this->Ipo->getWordsMessage();
              break;

            case PUSH:
              // action を2つ定義
              $actions[] = $this->Line->confirmAction('設定する', '通知設定する');
              $actions[] = $this->Line->confirmAction('設定しない', '通知設定しない');

              $template = $this->Line->setConfirm('通知設定変更', $actions);
              $messageData = $this->Line->setTemplate($template, "通知設定変更できません");
              break;

            case PUSHON:
              $messageData = $this->Ipo->setPushFlg($userId, 1);
              break;

            case PUSHOFF:
              $messageData = $this->Ipo->setPushFlg($userId, 0);
              break;

            case PUSHTIME:

              $template = $this->Line->setButton('通知時間変更', '通知時間を変更します', [ACTION_DATE_TIME]);
              $messageData = $this->Line->setTemplate($template, "通知時間変更できません");
              break;

              #            $messageData = $this->Line->setDatetimepicker('PUSH時間変更',POSTBACK_SELECT_PUSH_TIME, SELECT_TIME,'23:00','00:00','09:00');
              break;

            default:
              $messageData = $this->Ipo->getWordsMessage($kind);
              break;
          }
        } elseif ($type == "postback") {
          $postback = $event['postback'];
          $messageData = $this->Ipo->getPostBackMessage($userId, $postback);
        }

        // 返信可能な場合に処理する
        if (!empty($messageData)) {
          $response = $this->Line->setResponse($replyToken, $messageData);
          $this->Line->sendMessage(LINE_API_URL, $response, IPO_ACCESS_TOKEN);
        }
      }
      echo 200;
    }
}