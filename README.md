
# A Symfony bundle that formats the JSON api problem

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Github Actions][ico-gh-actions]][link-gh-actions]
[![Codecov][ico-codecov]][link-codecov]
[![Total Downloads][ico-downloads]][link-downloads]
[![Software License][ico-license]](LICENSE.md)

Standardize error responses in your Symfony application using [RFC7807](https://datatracker.ietf.org/doc/html/rfc7807) Problem details or any custom error format.

## Installation

You can install the package via composer:

```bash
composer require jenky/api-error-bundle-bunder
```

If you are not using `symfony/flex`, you'll have to manually add the bundle to your bundles file:

```php
// config/bundles.php

return [
    // ...
    Jenky\Bundle\ApiError\ApiErrorBundle::class => ['all' => true],
];
```

## Configuration

### Generic Error Response Format

By default all thrown exceptions will be transformed into the following format:

```js
{
    'message' => '{message}', // The exception message
    'status' => '{status_code}', // The corresponding HTTP status code, defaults to 500
    'code' => '{code}' // The exception int code
    'debug' => '{debug}', // The debug information
}
```

> The debug information only available when application debug mode (`kernel.debug`) is on.

Example:

```shell
curl --location --request GET 'http://myapp.test/api/not-found' \
--header 'Accept: application/json'
```

```json
{
  "message": "Not Found",
  "status": 404,
  "code": 0,
}
```

### RFC7807 Problem details

You will need to create an alias from `Jenky\ApiError\Formatter\ErrorFormatter` interface to `api_error.error_formatter.rfc7807`.

```yaml
# config/services.yaml
services:
    # ...
    Jenky\ApiError\Formatter\ErrorFormatter: '@api_error.error_formatter.rfc7807'
```

> For more information, please visit https://symfony.com/doc/current/service_container/autowiring.html#dealing-with-multiple-implementations-of-the-same-type.

### Custom Error Format

Create your own custom formatter that implements [`ErrorFormatter`](https://github.com/jenky/api-error/blob/main/src/Formatter/ErrorFormatter.php). Alternatively, you can extend the [`AbstractErrorFormatter`](https://github.com/jenky/api-error/blob/main/src/Formatter/AbstractErrorFormatter.php), provided for the sake of convenience, and define your own error format in the `getFormat` method.

Register your service if needed, in case `autowire` and `autoconfigure` is disabled, then follow [RFC7807 Problem details guide](#rfc7807-problem-details) to create the alias.

### [Exception Transformations](https://github.com/jenky/api-error?tab=readme-ov-file#exception-transformations)

If you want to add custom transformations, you should create a new class that implements the [`ExceptionTransformer`](ExceptionTransformer). With `autoconfigured` **enabled**, you're all set. Otherwise, register it in Symfony container with the `api_error.exception_transformer` tag.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email contact@lynh.me instead of using the issue tracker.

## Credits

- [Lynh](https://github.com/jenky)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/jenky/api-error-bundle.svg?style=for-the-badge
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=for-the-badge
[ico-gh-actions]: https://img.shields.io/github/actions/workflow/status/jenky/api-error-bundle/testing.yml?branch=main&label=actions&logo=github&style=for-the-badge
[ico-codecov]: https://img.shields.io/codecov/c/github/jenky/api-error-bundle?logo=codecov&style=for-the-badge
[ico-downloads]: https://img.shields.io/packagist/dt/jenky/api-error-bundle.svg?style=for-the-badge

[link-packagist]: https://packagist.org/packages/jenky/api-error-bundle
[link-gh-actions]: https://github.com/jenky/api-error-bundle
[link-codecov]: https://codecov.io/gh/jenky/api-error-bundle
[link-downloads]: https://packagist.org/packages/jenky/api-error-bundle

