# Anonym-Mail

This is mail component for AnonymFramework.
fast, stable and have multiple driver

Which drivers are supported?
----------------------

`PHPMailer`, `SwiftMailer`

How Can I Create A Instance
------------------------

```php
use Sagi\Mail\Mail;

$mail = new Mail();

```

How can i start a driver?
-----------------------

```php

$configs = []; // host, username, password, port ws
$driver = $mail->driver('phpmailer', $configs); // or swift

```

How Can i add a driver?
--------------------

```php

$mail->add('drivername', 'Driver/Class/Name');

```


How can i use a driver?

```php

$driver->subject('subject name'); // add a subject
$driver->from('your@address.com', 'your name');
$driver->to('href@adress.com', 'href name');
$driver->returnPath('return@adress.com', 'return name');
$driver->bcc('bcc@adress.com', 'bcc name'); // add a bcc

$driver->attach('filename.ext');

```
