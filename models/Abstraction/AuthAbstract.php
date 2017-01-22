<?php
namespace Models\Abstraction;

use Sagi\Database\Model;
/**
 * @class AuthAbstract
 *
 *@property $user_id int
 *@property $role string
 *@property $created_at string
 *@property $updated_at string
 *@method $this filterByUserId(int $user_id)
 *@method $this filterByRole(string $role)
 *@method $this filterByCreatedAt(string $created_at)
 *@method $this filterByUpdatedAt(string $updated_at)
 *@method $this setUserId(int $user_id)
 *@method int getUserId()
 *@method $this setRole(string $role)
 *@method string getRole()
 *@method $this setCreatedAt(string $created_at)
 *@method string getCreatedAt()
 *@method $this setUpdatedAt(string $updated_at)
 *@method string getUpdatedAt()

 */
abstract class AuthAbstract extends Model{
}
