<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;


class IpoSchedule extends Table
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

    $this->table('ipo_schedule');
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
      ->requirePresence('code', 'create')
      ->notEmpty('code');

    $validator
    ->requirePresence('url', 'create')
    ->allowEmpty('url');

    $validator
      ->requirePresence('listed_date', 'create')
      ->allowEmpty('listed_date');

    $validator
      ->requirePresence('book_building_date', 'create')
      ->allowEmpty('book_building_date');

    $validator
      ->requirePresence('book_building_start_date', 'create')
      ->allowEmpty('book_building_start_date');

    $validator
      ->requirePresence('book_building_end_date', 'create')
      ->allowEmpty('book_building_end_date');

    $validator
      ->requirePresence('attention', 'create')
      ->allowEmpty('attention');

    $validator
      ->requirePresence('lottery_date', 'create')
      ->allowEmpty('lottery_date');

    $validator
      ->requirePresence('lead_manager', 'create')
      ->allowEmpty('lead_manager');

    $validator
      ->dateTime('deleted')
      ->allowEmpty('deleted');

    return $validator;
  }
}