# Smsc notifications channel for Laravel 5.3+

[![Latest Version on Packagist](https://img.shields.io/packagist/v/laravel-notification-channels/smsc-ru.svg?style=flat-square)](https://packagist.org/packages/edemsky/sms-ru)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/laravel-notification-channels/smsc-ru.svg?style=flat-square)](https://packagist.org/packages/edemsky/sms-ru)

This package makes it easy to send notifications using [smsc.ru](https://smsc.ru) (aka СМС–Центр) with Laravel 5.3+.

## Contents

- [Installation](#installation)
    - [Setting up the SmscRu service](#setting-up-the-SmscRu-service)
- [Usage](#usage)
    - [Available Message methods](#available-methods)
- [Changelog](#changelog)
- [Testing](#testing)
- [Security](#security)
- [Contributing](#contributing)
- [Credits](#credits)
- [License](#license)


## Installation

Install this package with Composer:

```bash
composer require laravel-notification-channels/smsc-ru
```

If you're using Laravel 5.x you'll also need to specify a version constraint:

```bash
composer require edemsky/sms-ru
```

The service provider gets loaded automatically. Or you can do this manually:

```php
// config/app.php
'providers' => [
    ...
    NotificationChannels\SmsBee\SmsServiceProvider::class,
],
```

### Setting up the SmscRu service

Add your SmscRu login, secret key (hashed password) and default sender name (or phone number) to your `config/services.php`:

```php
// config/services.php
...
'sms' => [
    'login'  => env('SMSCRU_LOGIN'),
    'secret' => env('SMSCRU_SECRET'),
    'sender' => 'John_Doe',
    'extra'  => [
        // any other API parameters
        // 'tinyurl' => 1
    ],
],
...
```

> If you want use other host than `smsc.ru`, you MUST set custom host WITH trailing slash.

```
// .env
...
SMSCRU_HOST=http://www1.smsc.kz/
...
```

```php
// config/services.php
...
'sms' => [
    ...
    'host' => env('SMSCRU_HOST'),
    ...
],
...
```

## Usage

You can use the channel in your `via()` method inside the notification:

```php
use Illuminate\Notifications\Notification;
use NotificationChannels\SmsBee\SmsMessage;
use NotificationChannels\SmsBee\SmsChannel;

class AccountApproved extends Notification
{
    public function via($notifiable)
    {
        return [SmsChannel::class];
    }

    public function toSmscRu($notifiable)
    {
        return SmsMessage::create("Task #{$notifiable->id} is complete!");
    }
}
```

In your notifiable model, make sure to include a `routeNotificationForSmscru()` method, which returns a phone number
or an array of phone numbers.

```php
public function routeNotificationForSmscru()
{
    return $this->phone;
}
```

### Available methods

`from()`: Sets the sender's name or phone number.

`content()`: Set a content of the notification message.

`sendAt()`: Set a time for scheduling the notification message.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Security

If you discover any security related issues, please email jhaoda@gmail.com instead of using the issue tracker.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [JhaoDa](https://github.com/jhaoda)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
