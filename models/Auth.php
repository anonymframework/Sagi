<?php
namespace Models;

use Models\Abstraction\AuthAbstract;
/**
 * @class Auth
 *
 */
class Auth extends AuthAbstract
{

    /**
     * @var string
     */
    public $primaryKey = ['user_id'];

    /**
     * @var array|bool
     *
     */
    protected $timestamps = ['created_at','updated_at'];

     /**
      * @var string
      */
    protected $table = 'auth';


         /**
      * 
      * @return Users
      */
      public function users(){
          return $this->hasOne(Users::className(), ['id', 'user_id']);       
       }


}