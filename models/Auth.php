<?php
namespace Models;

use Sagi\Database\Model;
/**
 * @class Users
 *
 */
class Auth extends Model
{

    /**
     * @var string
     */
    public $primaryKey = 'user_id';

    /**
     * @var array|bool
     *
     */
    protected $timestamps = ['created_at','updated_at'];

     /**
      * @var string
      */
    protected $table = 'auth';


    

}