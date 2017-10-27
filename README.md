# Messente PHP client

A PHP client to send SMS messages through the [Messente](https://messente.com/) platform.

[![Latest Stable Version](https://poser.pugx.org/benmorel/messente/v/stable)](https://packagist.org/packages/benmorel/messente)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](http://opensource.org/licenses/MIT)

## Installation

This library is installable via [Composer](https://getcomposer.org/):

```bash
composer require benmorel/messente
```

## Requirements

This library requires PHP 7.1 or later.

## Quick start

Sending an SMS:

```php
$messente = new Messente('username', 'password');
$messageId = $messente->send('Hello word', '+441234567890'); // optionally provide the sender number
```

Querying an SMS status:

```php
$messente->getStatus($messageId); // 'SENT', 'FAILED' or 'DELIVERED'
```
