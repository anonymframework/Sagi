# Sagi
Sagi Database Class

#Setup

```
 $ composer require sagi/sagi:dev-master
```

#Initialize

```php

 $configs = [
    'host'  => 'localhost',
    'username' => 'root',
    'password' => '',
    'dbname' => 'test',
    // 'driver' => 'mysql' // Default=mysql.
    ];

 $db = new QueryBuilder($configs , $table);
 
 // you may want to set table after initialize, 
 
 $db = new QueryBuilder($configs);
 
 $db->setTable('users');
 
 // you may want to set your own PDO instance, you can!
 
 $pdo = new PDO('...whatever.');
 
 $db = new QueryBuilder($pdo);
 
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
 ####create
 
 
 >You shouldn't call any where query before `save` method for create
 >new data otherwise save method will update your datas.
 
 >You can also use QueryBuilder `create` method for this process.
 
 ```php
 
 $user->username = 'admin';
 $user->password = 'password';
 
 
  $user->save();
 
  // or
  
  $user->save([
     'username' => 'admin',
     'password' => 'password'
  ]);
 ```
####update


  >You can also use QueryBuilder `update` method for this process.

  >you may want to add `updateKey` variable on your class,  default is `id`
 ```php 
 
 
  
  $user->where('username', 'admin');
 
  $user->username = 'newAdmin';
  $user->password = 'newPassword';
  
  
   $user->save();
 ```
  ####find
 
 ```php
 
   $find = User::find(1); // equal to id = 1
 
 ```
 ####findOne
 
 ```php
 
   User::findOne(1); // equal to $find->one(); 
 
 ```
 ####findAll

 ```php
 
   User::findAll();
   
   // or 
   
   User::findAll(['auth' => 1]);
 
 ```
 ##Relations


####one
 
 ```php
 
 public function getPost(){
       $this->hasOne(Post::className(), ["user_id", "id"]);
 }
 
 ```
 ####many
 
  ```php
  
  public function getPosts(){
       return $this->hasMany(Post::className(), ["user_id", "id"]);
  }
  
  ```
 
 
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