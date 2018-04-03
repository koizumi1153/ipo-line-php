<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;


class IpoInformation extends Table
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

    $this->table('ipo_information');
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
      ->requirePresence('date', 'create')
      ->allowEmpty('date');

    $validator
      ->requirePresence('name', 'create')
      ->allowEmpty('name');

    $validator
      ->requirePresence('market_id', 'create')
      ->allowEmpty('market_id');

    $validator
      ->requirePresence('market_name', 'create')
      ->allowEmpty('market_name');

    $validator
      ->requirePresence('url', 'create')
      ->allowEmpty('url');

    $validator
      ->requirePresence('p_kari', 'create')
      ->allowEmpty('p_kari');

    $validator
      ->requirePresence('v_kobo', 'create')
      ->allowEmpty('v_kobo');

    $validator
      ->requirePresence('p_uri', 'create')
      ->allowEmpty('p_uri');

    $validator
      ->requirePresence('v_uri', 'create')
      ->allowEmpty('v_uri');

    $validator
      ->requirePresence('unit', 'create')
      ->allowEmpty('unit');

    $validator
      ->dateTime('deleted')
      ->allowEmpty('deleted');

    return $validator;
  }
}