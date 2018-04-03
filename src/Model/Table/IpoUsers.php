<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;


class IpoUsers extends Table
{
  /**
   * Initialize method
   *
   * @param array $config The configuration for the Table.
   * @return void
   */
  public function initialize(array $config)
  {
    parent::initialize($config);

    $this->table('ipo_users');
    $this->primaryKey('id');

    $this->addBehavior('Timestamp');
  }

  /**
   * Default validation rules.
   *
   * @param \Cake\Validation\Validator $validator Validator instance.
   * @return \Cake\Validation\Validator
   */
  public function validationDefault(Validator $validator)
  {
    $validator
      ->integer('id')
      ->allowEmpty('id', 'create');

    $validator
      ->requirePresence('user_id', 'create')
      ->notEmpty('user_id');

    $validator
      ->requirePresence('name', 'create')
      ->allowEmpty('name');

    $validator
      ->requirePresence('push_flg', 'create')
      ->notEmpty('push_flg');

    $validator
      ->dateTime('deleted')
      ->allowEmpty('deleted');

    return $validator;
  }
}