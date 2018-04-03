<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

/**
 * Project Ipo 関連のコンポーネント（主にDB操作）
 *
 * Class IpoComponent
 * @package App\Controller\Component
 */
class IpoComponent extends Component
{
    public $components = ["Line", "Lottery"];

   /** @var string  */
    protected $USERS    = 'IpoUsers';

    /** @var string  */
    protected $KINDS    = 'IpoKinds';

    /** @var string  */
    protected $WORDS    = 'IpoWords';

    /** @var string  */
    protected $INFO     = 'IpoInformation';

    /** @var string  */
    protected $SCHEDULE = 'IpoSchedule';


    public function initialize(array $config) {
      $this->Users    = TableRegistry::get($this->USERS);
      $this->Kinds    = TableRegistry::get($this->KINDS);
      $this->Words    = TableRegistry::get($this->WORDS);
      $this->Info     = TableRegistry::get($this->INFO);
      $this->Schedule = TableRegistry::get($this->SCHEDULE);
    }


  /**
   * @param $userId
   * @param string $name
   */
    public function setUsers($userId, $name=''){
      $user = $this->Users->newEntity();
      $user->set([
        'user_id' => $userId,
        'name'    => $name,
        'created' => date('Y-m-d H:i:s')
      ]);

      $this->Users->save($user);
    }

    public function getUsers($userId){
      $query=$this->Users->find();
      $query->where(['user_id' => $userId]);
      $query->where(['deleted IS NULL']);
      
      $user = $query->first();
      return $user;
    }

    public function deleteUser($userId){
      $now = date('Y-m-d H:i:s');
      $query=$this->Users->query();

      $query->update()
        ->set(['deleted' => $now])
        ->where(['user_id' => $userId])
        ->where(['deleted IS NULL'])
        ->execute();
    }

    public function getKinds(){
      $query=$this->Kinds->find();
      $query->where(['deleted IS NULL']);
      $kind = $query->hydrate(false)->toArray();

      return $kind;
    }

    /**
     * @param $kindId
     * @param int $priority
     * @return mixed
     */
    public function getWords($kindId, $priority=0){
      $query=$this->Words->find();
      $query->where(['kind_id' => $kindId]);
      $query->where(['priority' => $priority]);
      $words = $query->hydrate(false)->toArray();

      return $words;
    }

    /**
     * @return mixed
     */
    public function getInfoAll(){
      $query=$this->Info->find();
       $query->where(['deleted IS NULL']);
      $maps = $query->hydrate(false)->toArray();

      return $maps;
    }

    /**
     * @param $info
     */
    public function updateInfo($info){
      $now = date('Y-m-d H:i:s');
      $query=$this->Info->query();

      $query->update()
        ->set(['updated' => $now])
        ->set(['p_uri' => $info['p_uri']])
        ->set(['p_kari' => $info['p_kari']])
        ->where(['id' => $info['id']])
        ->where(['deleted IS NULL'])
        ->execute();
    }

    /**
     * @param $contents
     */
    public function setInfo($contents)
    {
      $query = $this->Info->query();
      $query->insert([
        'id',
        'code',
        'date',
        'name',
        'market_id',
        'market_name',
        'url',
        'p_kari',
        'v_kobo',
        'p_uri',
        'v_uri',
        'unit',
        'created'
      ]);
      if (!empty($contents)) {
        foreach ($contents as $info) {
          $info['created'] = date('Y-m-d H:i:s');
          $query->values($info);
        }
        $query->execute();
      }
    }

    /**
     * @return mixed
     */
    public function getInfoFromDate(){
      $today = date('Y-m-d');
      $query=$this->Info->find();
      $query->where(['date > ' => $today]);
      $query->where(['deleted IS NULL']);
      $maps = $query->hydrate(false)->toArray();

      return $maps;
    }

    /**
     * 会話用 適当に返すだけ
     *
     * @return array
     */
    public function getWordsMessage($kindId=WORDS){
      $messageData = array();

      $wordsMaster = self::getWords($kindId, PRIORITY_DEFAULT);
      $word = $this->Lottery->lotteryMaster($wordsMaster);
      if (!empty($word)) {
        $text = $word['word'];
        $messageData = $this->Line->setTextMessage($text, $messageData);
      }

      return $messageData;
    }

    /**
     * スケジュール追加
     *
     * @param $contents
     */
    public function setSchedule($contents){
      $query = $this->Schedule->query();
      $query->insert([
        'code',
        'url',
        'listed_date',
        'book_building_date',
        'book_building_start_date',
        'book_building_end_date',
        'attention',
        'created'
      ]);
      if (!empty($contents)) {
        foreach ($contents as $info) {
          $info['created'] = date('Y-m-d H:i:s');
          $query->values($info);
        }
        $query->execute();
      }
    }

  /**
   * push_flg変更
   * @param $userId
   * @param string $flg
   * @return mixed
   */
  public function setPushFlg($userId, $flg='0'){
    $now = date('Y-m-d H:i:s');
    $query = $this->Users->query();

    $query->update()
      ->set(['push_flg' => $flg])
      ->set(['updated' => $now])
      ->where(['user_id' => $userId])
      ->where(['deleted IS NULL'])
      ->execute();

    if($flg) {
      $text = "通知を設定したよ。";
    }else {
      $text = "通知を解除したよ。";
    }
    $messageData = $this->Line->setTextMessage($text);
    return $messageData;
  }

  /**
   * @param $messageData
   * @param $access_token
   */
  public function sendMessage($messageData, $access_token){
    if(!empty($messageData)) {
      // ユーザー取得
      $userCount = $this->getPushUsersCount();
      if ($userCount > 0) {
        $allPage = ceil($userCount / LINE_MULTI_USER);
        for ($page = 1; $page <= $allPage; $page++) {
          $user = $this->getPushUsers($page);
          $userIds = array_column($user, 'user_id');

          // PUSH
          if (count($messageData) > LINE_MESSAGE_COUNT) {
            $messages = array_chunk($messageData, LINE_MESSAGE_COUNT);
            foreach ($messages as $message) {
              $this->Line->sendPush(LINE_API_MULTI_URL, $access_token, $userIds, $message);
            }
          } else {
            $this->Line->sendPush(LINE_API_MULTI_URL, $access_token, $userIds, $messageData);
          }
        }
      }
    }
  }
}