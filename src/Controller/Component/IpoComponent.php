<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;
use Abraham\TwitterOAuth\TwitterOAuth; //twitter

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
      $info = $query->hydrate(false)->toArray();

      return $info;
    }

    /**
     * @param $code
     * @return mixed
     */
    public function getInfoFromCode($code){
      $query=$this->Info->find();
      $query->where(['code' => $code]);
      $query->where(['deleted IS NULL']);
      return $query->first()->toArray();
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
        'lottery_date',
        'lead_manager',
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
   * @param $code
   * @param $attention
   */
    public function updateSchedule($code, $attention){
      $now = date('Y-m-d H:i:s');
      $query=$this->Schedule->query();

      $query->update()
        ->set(['updated' => $now])
        ->set(['attention' => $attention])
        ->where(['code' => $code])
        ->where(['deleted IS NULL'])
        ->execute();
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

  /**
   * 抽選日当日のデータを取得する
   * @return mixed
   */
  public function getLotterySchedule(){
    $today = date('Y-m-d');
    $query=$this->Schedule->find();
    $query->where(['lottery_date' => $today]);
    $query->where(['deleted IS NULL']);
    $schedule = $query->hydrate(false)->toArray();

    return $schedule;
  }

  /**
   * BB開始前日のデータを取得する
   * @return mixed
   */
  public function getBookBuildingStartDateSchedule(){
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    $query=$this->Schedule->find();
    $query->where(['book_building_start_date' => $tomorrow]);
    $query->where(['deleted IS NULL']);
    $schedule = $query->hydrate(false)->toArray();

    return $schedule;
  }


  /**
   * BB終了前日のデータを取得する
   * @return mixed
   */
  public function getBookBuildingEndDateSchedule(){
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    $query=$this->Schedule->find();
    $query->where(['book_building_end_date' => $tomorrow]);
    $query->where(['deleted IS NULL']);
    $schedule = $query->hydrate(false)->toArray();

    return $schedule;
  }

  /**
   * ツイート処理
   *
   * @param $str
   */
  public function tweet($str){
    if(CAKEPHP_ENV == "production") {
      $twitter = new TwitterOAuth(IPO_TWITTER_CONSUMER_KEY, IPO_TWITTER_CONSUMER_SECRET, IPO_TWITTER_ACCESS_TOKEN, IPO_TWITTER_ACCESS_TOKEN_SECRET);

      $result = $twitter->post(
        "statuses/update",
        array("status" => "{$str}")
      );
    }
  }

  /**
   * PUSH 可能ユーザー数取得
   * @return mixed
   */
  public function getPushUsersCount($time='')
  {
    $query = $this->Users->find();
    $query->where(['push_flg' => ON_FLG]);
    $query->where(['deleted IS NULL']);

    if(!empty($time)) $query->where(['push_time' => $time]);

    $total = $query->count();
    return $total;
  }

  /**
   * PUSH可能ユーザー取得
   * @param $page
   * @return mixed
   */
  public function getPushUsers($page, $time='')
  {
    $query = $this->Users->find()->select(['user_id']);
    $query->where(['push_flg' => ON_FLG]);
    $query->where(['deleted IS NULL']);
    $query->order(['id' => 'ASC']);
    $query->limit(LINE_MULTI_USER)->page($page);

    if(!empty($time)) $query->where(['push_time' => $time]);

    $users = $query->hydrate(false)->toArray();
    return $users;
  }

  /**
   * @return mixed
   */
  public function getInfoListing(){
    $today = date('Y-m-d');
    $query=$this->Info->find();
    $query->where(['date' => $today]);
    $query->where(['deleted IS NULL']);
    $info = $query->hydrate(false)->toArray();

    return $info;
  }

  public function getScheduleFromDate(){
    $today = date('Y-m-d');
    $query=$this->Schedule->find();
    $query->where(['listed_date > ' => $today]);
    $query->where(['deleted IS NULL']);
    $info = $query->hydrate(false)->toArray();

    return $info;
  }
}