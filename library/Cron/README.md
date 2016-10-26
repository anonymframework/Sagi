# Cron


Bu bileşen cron job ekleme ve silme gibi işler için kullanılır.

-------------------------

Sınıfın Çağrımı
--------------

Cronjob ile ilgili sınıflar `Sagi\Cron` namespace içinde bulunur.

```php

use Sagi\Cron\BasicCron;
use Sagi\Cron\Task;


$cron = new BasicCron();

```

------------------------------

Yeni bir iş eklemek
-------------------


Ekleyeceğiniz işleri direk terminal ile veya terminalde php üzerinden çağrılacak şekilde yapabilirsiniz.


**Terminal komutu çağırmak**:

```php

$cron->event(function(){

    return Task::exec('your exec command');

});

```

----------------------------

**Php komutu çağırmak**:

```php

$cron->event(function(){

 return Task::php('/var/www/html/test.php'); // call test.php
});

```

----------------------------

İşlerin Ne Zaman Çalışacağını Ayarlamak
----------------

Eklediğiniz işler ön tanımlı olarak her dakika çalışacak şekilde ayarlıdır. Bunu düzenlemek için;

`return Task::php('/var/www/html/test.php')` kodundan sonra `daily` gibi methodları çağırabilirsiniz.

**Örnek Olarak:**


```php


$cron->event(function(){

 return Task::php('/var/www/html/test.php')->daily(); // call test.php everyday
});


```

-----------------------------

**Kullanabileceğiniz Değerler aşağıdaki gibidir**

```php
->everyMinute();        // her dakika yürütür
->everyFiveMinutes();   // her 5 dakikada bir yürütür
->everyTenMinutes();    // her 10 dakikada bir yürütür
->everyThirtyMinutes();	// her 30 dakikada bir yürütür
->hourly();	            // her saat başı yürütür
->daily();	            // her gün yürütür
->dailyAt('13:00');	    // her günün girilen saatinde yürütür
->twiceDaily(1, 13);	// her gün girilen saatlerde yürütür
->weekly();	            // her haftanın başında yürütür
->monthly();	        // her ayın başında yürütür


->weekdays();	        // haftanın her günü yürütür
->sundays();	        // sadece pazar günleri yürütür
->mondays();	        // sadece pazartesi günleri yürütür
->tuesdays();	        // sadece  salı yürütür
->wednesdays();         // sadece çarşamba günleri yürütür
->thursdays();	        // sadece perşembe günleri yürütür
->fridays();	        // sadece cuma günleri yürütür
->saturdays();	        // sadece perşembe günleri yürütür
->when(Closure);	    // girdiğiniz Closure fonksiyondan true döndüğü zaman yürütür // konsol üzerinde çalışır

```

>Bu verileri arka arkayada çağırabilirsiniz.

->when() ile kontrol yaptırma(**AnonymConsole**)
---------------

`when` methodu ile istediğiniz kontrolu yaptırabilirsiniz. **Sadece Anonym Console üzerinde çalışır**

```php

$cron->event(function(){

 return Task::php('/var/www/html/test.php')->daily()->when(function(){
    return true;
 });
});

```

>Kontrolun başarılı olduğunun anlaşılması için `true` değeri dönmelidir.

-----------------------------

İşleri Yürütmek(Cron a eklemek)
--------------------------


```php

$cron->run();

```


Bir işi silmek
----------------

```php

$job = Task::php('/var/www/html/test.php')->daily();

$cron->removeJob($job);

```

Tüm işleri temizlemek
--------------------

```php

$cron->clean();

```

Eklentiler
--------------

Konsol Uygulamasında Komut çağrılmadan önce ve sonra yapılacak işleri ayarlayabilirsiniz.


```php

$schedule->event(function(){
    echo 'hello world';
})->before(function(){

})->after(function(){

});

```