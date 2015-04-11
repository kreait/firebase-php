# Working with the `Firebase` class

The `Firebase` class is a wrapper around the [Firebase REST API](https://www.firebase.com/docs/rest/api/).

### Initialize a Firebase

```php
use Kreait\Firebase\Firebase;

$firebase = new Firebase('https://myapp.firebaseio.com');
```

### Get data

```php
$userData = $firebase->get('data/users');
print_r($userData);
/*
[
    'homer' => [
        'name' => 'Homer Simpson',
        'email' => 'homer@simpsons.com',
    ],
    'marge' => [
        'name' => 'Marge Simpson',
        'email' => 'marge@simpson.com',
    ]
]
*/
```

### Set data

```php
$result = $firebase->set(
    [
        'name' => 'Lisa Simpson',
        'email' => 'lisa@simpson.com'
    ],
    'data/users/lisa'
);
print_r($result)
/*
[
    'name' => 'Lisa Simpson',
    'email' => 'lihsa@simpson.com'
]
*/
```

### Update data

```php
$result = $firebase->update(
    [
        'email' => 'lisa@simpson.com'
    ],
    'data/users/lisa'
);
print_r($result)
/*
[
    'email' => 'lisa@doe.com'
]
*/
```

### Delete location

```php
$firebase->delete('data/users/lisa');
```

### Add child

```php
$childKey = $firebase->push(
    [
        'name' => 'Bart Simpson',
        'email' => 'bart@simpson.com'
    ],
    'data/users'
);
echo $childKey;
// -XCS576SDF7OIP
```
