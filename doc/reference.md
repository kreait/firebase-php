# Working with References

A reference is a shortcut to a subtree of your Firebase data.

### Get a reference

```php
use Kreait\Firebase\Firebase;

$firebase = new Firebase('https://myapp.firebaseio.com');
$users = $firebase->getReference('users/data');
```

### Get a child references

```php
$homer = $users->getReference('homer');
```

### Get data

```php
$data = $homer->getData();
/*
    [
        'name' => 'Homer Simpson',
        'email' => 'homer@simpson.com'
    ]
*/
$name = $homer['name'];
$email = $homer['email'];
```


### Set data

Overwrite the complete data of the given reference

```php
$homer->set([
    'job' => 'Security Inspector'
])
/*
    $homer->getData();
    [
        'job' => 'Security Inspector'
    ]
*/
```

### Update data

```php
$homer->update(['address' => 'Evergreen Terrace']);
// or
$homer['address'] = 'Evergreen Terrace';
```

### Add child

Adding a child returns a reference to this child

```php
$maggie = $users->push(['name' => 'Maggie Simpson', 'email' => 'maggie@simpson.com']);

echo $maggie->getKey();
// -Zxjshd7ad34lkh

print_r($maggie->getData());
/*
    [
        'name' => 'Maggie Simpson',
        'email' => 'maggie@simpson.com'
    ]
*/
```

### Delete reference

```php
$homer = $firebase->getReference('users/data/homer');
$homer->delete();
```