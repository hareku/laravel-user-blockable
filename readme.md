# Laravel 5 User Block System

This package helps you to add user block system to your project.

* So simply and easy.
* Use "ON DELETE CASCADE" in block relationships table.

## Caution
- *Support Laravel 5.4~*  
- *Required php >=7.0*

## Installation

First, pull in the package through Composer.

Run `composer require hareku/laravel-user-blockable`

And then, include the service provider within `config/app.php`.

```php
'providers' => [
    Hareku\LaravelBlockable\BlockableServiceProvider::class,
];
```

Publish the config file. (blockable.php)

```sh
$ php artisan vendor:publish --provider="Hareku\LaravelBlockable\BlockableServiceProvider"
```

Finally, use Blockable trait in User model.

```php
use Hareku\LaravelBlockable\Traits\Blockable;

class User extends Model
{
    use Blockable;
}
```

## Usage

### Block a user or users

```php
$user->block(1);
$user->block([1,2,3,4]);
```

### Add blockers

```php
$user->addBlockers(1);
$user->addBlockers([1,2,3,4]);
```

### Unblock a user or users

```php
$user->unblock(1);
$user->unblock([1,2,3,4]);
```

### Get blocker users / blocked by users

```php
// blocker users
$user->blockerUsers()->get(); // Get blocker user models.
$user->blockerRelationships()->get(); // Get blocker relationship models.

// blocked by users
$user->blockingUsers()->get();
$user->blockingRelationships()->get();
```

### Check if it is blocking
```php
$user->isBlocking(1);
$user->isBlocking([1,2,3,4]);
```

### Check if it is being blocked

```php
$user->isBlockedBy(1);
$user->isBlockedBy([1,2,3,4]);
```

### Check if it is mutual block

```php
$user->isMutualBlock(1);
$user->isMutualBlock([1,2,3,4]);
```

### Get blocker/blocked IDs

```php
$user->block([1,2,3]);

$user->blockerIds(); // [1,2,3]
$user->blockingIds();
```

### Reject user ids

```php
$user->blockerUsers()->pluck('id')->all(); // [1,2,3]
$user->rejectNotBlocker([1,2,3,4,5]); // [1,2,3]
```

```php
$user->block([1,2,3]);
$user->rejectNotBlocking([1,2,3,4,5]); // [1,2,3]
```

## License

MIT

## Author

hareku (hareku908@gmail.com)
