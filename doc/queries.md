# Working with Queries

You can query a location by using the `query` method of the `Firebase` or `Reference` class.

```php
use Kreait\Firebase\Firebase;
use Kreait\Firebase\Query;

$firebase = new Firebase('https://myapp.firebaseio.com');

$reference = $firebase->getReference('path/to/my/location');

$reference->set([
    'a' => 'a',
    'b' => 'b',
    'c' => 'c',
]);

$query = new Query();
$query
    ->orderByKey()
    ->startAt('a')
    ->endAt('b');

$data = $reference->query($query);

print_r($data);

// Output:
// Array
// (
//     [a] => a
//     [b] => b
// )
```

See https://www.firebase.com/docs/rest/guide/retrieving-data.html#section-rest-queries for further information.

### `startAt($start)`

Starts at the given entrypoint.

### `endAt($end)`

Ends at the given ending pint.

### `limitToFirst($limit)`

Limits a result to the first x entries.

### `limitToLast($limit)`

Limits a result to the last x entries.

### `orderByChildKey($childKey)`

Orders a result by the given child key.

### `orderByKey()`

Orders a result by key.

### `orderByPriority()`

Orders a result by priority.

### `shallow($shallow = true)`

Returns the keys of the first result level only (each value is set to `true).
