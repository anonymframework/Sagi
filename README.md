# Sagi
Sagi Database Class

#Setup

```
 $ composer require sagi/sagi:dev-master
```

#Initialize

```php

 $db = QueryBuilder::createNewInstance('users');
 
 // or 
 
 $db = QueryBuilder::createNewInstance()->setTable('users');

```
---------------------------
#### <i class="icon-file"></i> Create

```php
 
   $db->create([
       'username' => 'admin',
       'password' => 123456
    ]);
    
  
```

#### <i class="icon-trash"></i> Delete

```php

 $db->where('username', 'admin')->delete();
 
```

#### <i class="icon-pencil"></i> Update

```php

  $db->where('username', 'admin');
  
     $db->update([
      'username' => 'newAdmin'
      ]);

```


## Read


#### Select

```php

 $db->select('username,password'); 
 
  // or
  
 $db->select(['username', 'password']);
 
 // or you can use subqueries
 
 $db->select(['title', 'id', function(QueryBuilder $builder){
        return $builder->setTable('categories')->select('category_name')->where('id', 'posts.category_id')->setAs('category_name');
 })]);
 
 // title,id,(SELECT category_name FROM categories WHERE id = posts.category_id ) as category_name
  
```




#### Where

```php

 $db->where('username', 'admin');
 
 // or
  
 $db->where('username', 'admin', '=');
 
 // or
 
 $db->where(['username', '=', 'admin']);
 
```

#### orWhere

```php

 $db->orWhere('username', 'admin');
 
 // or
  
 $db->orWhere('username', 'admin', '=');
 
 // or
 
 $db->orWhere(['username', '=', 'admin']);
 
```

####Like

```php

$db->like('username', [$username, '%?%']); // username LIKE %$username%


```

####Orlike

```php

$db->orLike('username', [$username, '%?%']); // username LIKE %$username%

// make a not like query

$db->orLike('username', [$username, '%?%'], true); // username NOT LIKE %$username%


```

 #### NotLike

```php

$db->notLike('username', [$username, '%?%']); // username NOT LIKE %$username%

// or not like

$db->orNotlike('username', [$username, '%?%']); // username LIKE %$username%


```

####in

```php

$db->in('id', [1,2,3,4,5,6,7,8,9,10]);

// or 

$db->in('id', '1,2,3,4,5');

//or you can use subqueries

$db->in('username', function(QueryBuilder $builder){
   return $builder->select('username')->where('id', 1); 
});

```

####or in

```php

$db->OrIn('id', [1,2,3,4,5,6,7,8,9,10]);

// or 

$db->OrIn('id', '1,2,3,4,5');

//or you can use subqueries

$db->OrIn('username', function(QueryBuilder $builder){
   return $builder->select('username')->where('id', 1); 
});
```

#### WNotIn

```php

$db->notIn('id', [1,2,3,4,5]);

// or not query

$db->orNotIn('id', [1,2,3,4,5]);

```

#### Limit

```php

 $db->limit(15);
 
 // or 
 
 $db->limit([15]);
 
 // or 
 
 $db->limit([0,15]);

```

#### Order

```php

$db->order('id', 'DESC');

```


#### GroupBy

```php

 $db->group('id');
 
```

####having

```php
  $db->group('id')->having('COUNT(id) > 10');
```

#### Join


```php

$db->join('targetTable', ['targetColumn' => 'ourColumn']);

```

####first

gets only first data

```php

 $data = $db->first();

```

#### all

gets all datas with fetchAll method

```php
 
  $datas = $db->all();
 
```


```php
 
  foreach($datas as $data){
    echo $data->username;
  }

```

####count

```php

 $count = $db->count(); // returns int.

```

####exists

```php

$exists = $db->exists(); // returns true or false

```

#Working With Models


```php 

class User extends Model{

     public static function tableName(){
         return "users";  
      }

 }

 
 ```
 
 ####timestamps
 ```php
  
   protected $timestamps = false; // default is ['created_at', 'updated_at'];
 
 ```
 
####primaryKey

```php
 
  public $primaryKey = 'id'; // using for find and findOne methods,  default is 'id'
  
```
 
 ####fields
 
 ```php
 
  
  protected $fields = ['id', 'username'];
 ```
 
 ####expects
 
 
```php
 protected $expects = ['password'];
 ```
 
 ####create
 
 
 >You shouldn't call any where query before `save` method for create
 >new data otherwise save method will update your datas.
 
 >You can also use QueryBuilder `create` method for this process.
 
 ```php
 
 $user->username = 'admin';
 $user->password = 'password';
 
 
  $user->save();
 
  // or
  
  $user->create([
     'username' => 'admin',
     'password' => 'password'
  ]);
 ```
####update


  >You can also use QueryBuilder `update` method for this process.
  >you may want to add `updateKey` variable on your class,  default is `id`
 
 
 -----------------
 ```php 

  
  $user->where('username', 'admin');
 
  $user->username = 'newAdmin';
  $user->password = 'newPassword';
  
  
   $user->save();
   
   // or 
   
   $user->update([
      'whatever' => 'you wish'
   ]);
 ```
  -----------------

  ####find
 
 ```php
 
   $find = User::find(1); // equal to id = 1
 
 ```
  -----------------

 ####findOne
 
 ```php
 
   User::findOne(1); // equal to $find->one(); 
 
 ```
  -----------------

 ####findAll

 ```php
 
   User::findAll();
   
   // or 
   
   User::findAll(['auth' => 1]);
 
 ```
  -----------------

 ##Relations

 -----------------

####one
 
 ```php
 
 public function getPost(){
       $this->hasOne(Post::className(), ["user_id", "id"]);
 }
 
 ```
  -----------------

 ####many
 
  ```php
  
  public function getPosts(){
       return $this->hasMany(Post::className(), ["user_id", "id"]);
  }
  
  ```
 
  -----------------

 #Table Schema
 
 ```php
  use Sagi\Database\Schema;
 
  $schema = new Schema();
 
 
  $schema->createTable('users', function(Row $row){
      $row->pk('id');
      
      $row->int('role')->defaultValue(1);
      $row->string('username')->notNull();
      $row->string('password')->notNull();
  });
 ```
 
 -------------
 #Migrations
 ----------------
 
 ```
  php sagi migration:create create_user_table
  
 ```
 
 output;
 
 `migrations/migration_file16_08_25_06_08__create_user_table.php : migration created successfully`
 
```php


/**
 *  Created by Sagi Database Console
 *
 */

use Sagi\Database\Schema;

/**
 * @class CreateUserTable
 */
class CreateUserTable extends Schema{

    /**
     * includes createTable functions
     *
     */
    public function up(){


    }

    /**
     * includes dropTable function
     *
     */
    public function down(){


    }
}
```


####run migrations

```

 $ php sagi migrate

```

####drop migrations

```
 $ php sagi migration:drop
```

####reset migrations

```

 $ php sahi migration:reset

```

####drop migrations

```
 $ php sagi migration:drop
```

------------
#Create Models

```
 
  $ php sagi create:models
```

this method will be create your model auto into `models` dir.

output will be like this;

```
AuthAssignment created successfully in models/AuthAssignment.php
Authitem created successfully in models/Authitem.php
AuthitemChild created successfully in models/AuthitemChild.php
AuthRule created successfully in models/AuthRule.php
Categories created successfully in models/Categories.php
Comments created successfully in models/Comments.php
Likes created successfully in models/Likes.php
Migration created successfully in models/Migration.php
Posts created successfully in models/Posts.php
Users created successfully in models/Users.php

```

an model file will be like this;

```php
namespace Models;
/**
 * @class Users
 *
 */
class Users extends Model
{

    /**
     * @var array
     *
     */
    protected $fields = [
       'id','username','password','registered_date','email','name','auth_key','profile_image','role','reset_token'
    ];

    /**
     * @var string
     */
    public $primaryKey = 'id';

    /**
     * @var array|bool
     *
     */
    protected $timestamps = ['created_at', 'updated_at'];

    public static function getTableName()
    {
        return  'users';
    }

}
```

------------

#Validation

```php
 use Sagi\Database\Model;
 use Sagi\Database\Validation;
 
 class Users extends Model{
  use Validation;
  
  public function rules(){
      return [
              'content' => 'required',
              'title' => 'required|digit_min:6',
              'category' => 'required|numeric'
           ];
  }
  
  public function filters(){
    return [  
              'content' => 'xss',
              'title' => 'xss|strip_tags'
          ];
 }

```

#Pagination

```php

class Users extends Model
{
  use Pagination;
}

$users = Users::findAll();

$user->paginate($_GET['page'], 15);

$user->displayPagination();

```

> you can edit pagination template in `templates/pagination.temp`


```html

<ul class="pagination">

   @if($pagination->hasLess())
    <li>
         <a href="?page={{$pagination->getBefore()}}" aria-label="Previous">
           <span aria-hidden="true">&laquo;</span>
         </a>
    </li>
    @endif
   @foreach($pagination as $data)
       <li {!! $data->isCurrentPage() ? 'class="active"': "" !!}><a href="?page={{$data}}">{{$data}}</a> </li>
   @endforeach

   @if($pagination->hasMore())
   <li>
         <a href="?page={{$pagination->getNext()}}" aria-label="Next">
           <span aria-hidden="true">&raquo;</span>
         </a>
       </li>
   @endif
</ul>

```

output will be like this;

```

 <ul class="pagination">

          <li ><a href="?page=1">1</a></li>
          <li ><a href="?page=2">2</a></li>
          <li ><a href="?page=3">3</a></li>
   
      <li>
         <a href="?page=2" aria-label="Next">
           <span aria-hidden="true">&raquo;</span>
         </a>
       </li>
   </ul>

```

----------------
#Authorization

add to your user table migration schema;

```php
$row->auth();
```

```php

class Users extends Model{
   use Authorization;
}

var_dump($user->isSuperAdmin());
var_dump($user->isAdmin());
var_dump($user->isUser());
var_dump($user->isEditor());


```

### set role

```php

 $user = Users::findOne(1);
 
 $user->role = 'superadmin';
 
 $user->save();

```