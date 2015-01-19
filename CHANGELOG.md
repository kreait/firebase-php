# CHANGELOG

## 0.2.1 - 2015-01-19

- The Firebase library now has its own base URL handling so that a stable version of the HTTP adapter can be used.

## 0.2 - 2015-01-13

- Better handling of server errors
    + Instead of using hard coded exception messages for assumed server errors, a single server error exception now includes the server's error message, if available.
- It is now possible to use the `shallow` parameter when performing a GET request. See [the Firebase Docs on Query Parameters](https://www.firebase.com/docs/rest/api/#section-query-parameters) for a detailed description.
- `Firebase::push()` now returns the generated key as a string (not as an array `['name' => '...']`) anymore

## 0.1.1 - 2015-01-09

- The logger output now is less verbose and includes full URLs
- The README now includes an usage example

## 0.1 - 2015-01-09

- Initial release