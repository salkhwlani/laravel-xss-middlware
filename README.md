# A XSS middleware for Laravel

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![StyleCI][ico-styleCi]][link-styleCi]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

A Laravel middleware to cleaning all inputs/data of request from XSS and embed elements, its used [Security Core](https://github.com/GrahamCampbell/Security-Core) package by [Graham Campbell](https://github.com/GrahamCampbell) under the hook. 

## Install

`composer require alkhwlani/xss-middleware`

## Usage

That's it! by default package automatic register a global middleware to cleaning all string inputs for all requests.

if you are not using automatic package discovery, then add the service provider in `config/app.php`:

```php
\Alkhwlani\XssMiddleware\ServiceProvider::class,
```

## Optional

if you want customizes configuration you can publish the configuration

```bash
$ php artisan vendor:publish --provider="\Alkhwlani\XssMiddleware\ServiceProvider"
```

Then check the content of the published config file `config/xss-middleware.php`.

## Testing
Run the tests with:

``` bash
vendor/bin/phpunit
```

## Contributing
Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security
If you discover any security-related issues, please email alkhwlani@yandex.com instead of using the issue tracker.

## Credits

- [Salah Alkhwlani][link-author]
- [Graham Campbell](https://github.com/GrahamCampbell)
- [All Contributors][link-contributors]

## License
The MIT License (MIT). Please see [License File](/LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/alkhwlani/xss-middleware.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/yemenifree/xss-middleware/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/yemenifree/xss-middleware.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/yemenifree/xss-middleware.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/alkhwlani/xss-middleware.svg?style=flat-square
[ico-styleCi]: https://styleci.io/repos/172194440/shield?branch=master&style=flat

[link-packagist]: https://packagist.org/packages/alkhwlani/xss-middleware
[link-travis]: https://travis-ci.org/alkhwlani/xss-middleware
[link-scrutinizer]: https://scrutinizer-ci.com/g/yemenifree/xss-middleware/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/yemenifree/xss-middleware
[link-downloads]: https://packagist.org/packages/alkhwlani/xss-middleware
[link-author]: https://github.com/yemenifree
[link-contributors]: ../../contributors
[link-styleCi]: https://styleci.io/repos/172194440