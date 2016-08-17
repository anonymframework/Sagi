# Sagi
Sagi Database Class


#Initialize

```php

 $configs = [
    'host'  => 'localhost',
    'username' => 'root',
    'password' => '',
    'dbname' => 'test'
    ];

 $db = new QueryBuilder($configs , $table);
 
 // you may want to set table after initialize, 
 
 $db = new QueryBuilder($configs);
 
 $db->setTable('users');
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

  $db->where('username', 'admin')->update([
      'username' => 'newAdmin'
      ]);

```


## Read


#### Select

```php

 $db->select('username,password'); 
 
  // or
  
 $db->select(['username', 'password']);
  
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

// make a or like query

$db->like('username', [$username, '%?%'], true); // username NOT LIKE %$username%


```

####Orlike

```php

$db->orLike('username', [$username, '%?%']); // username LIKE %$username%

// make a or like query

$db->orLike('username', [$username, '%?%'], true); // username NOT LIKE %$username%


```

####in

```php

$db->in('id', [1,2,3,4,5,6,7,8,9,10]);

// or 

$db->in('id', '1,2,3,4,5');

//or you can use callbacks

$db->in('username', function(QueryBuilder $builder){
   return $builder->select('username')->where('id', 1); 
});

// you may want to use not in query

// you can, just add a third parameter and set it true

$db->in('id', [1,2,3,4,5], true);

```

####or in

```php

$db->OrIn('id', [1,2,3,4,5,6,7,8,9,10]);

// or 

$db->OrIn('id', '1,2,3,4,5');

//or you can use callbacks

$db->OrIn('username', function(QueryBuilder $builder){
   return $builder->select('username')->where('id', 1); 
});

// you may want to use not in query


// you can, just add a third parameter and set it true

$db->OrIn('id', [1,2,3,4,5], true);

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

  echo $data->username;

```

```php
 
  foreach($datas as $data){
    echo $data->username;
  }

```

### Relations

#### one


```php

$db->relation('posts', ['user_id', 'id']);

$post = $db->posts; 

// or

$post = $db->posts();

// you may want to set alias

$db->relation(['aliasName', 'tableName'], ['user_id', 'id']);

$post = $db->aliasName;

// or 
$post = $db->aliasName();

```

####many

```php

$db->relation('posts', ['user_id', 'id', 'many']);




```

##Examples;

```php
 
 $db->where('username', 'admin')->relation('posts', ['user_id', 'id', 'many']);

 
 $data = $db->first();
 
 
 foreach($db->posts as $post)
 {
    echo $post->id;
 }

```


###SubRelatives

You can set subrelatives


```php

$db->relation(['post', 'posts'], ['user_id', 'id']);

$post = $db->post;

$post->relation(['category', 'categories'], ['id', 'category_id']);

// or  

$db->relation(['posts.category', 'categories']); // ['firstTableName.secondTableName.aliasName', 'tableName']

// be careful, you set on $db not on $post;

echo $post->category->category_name; 




```

