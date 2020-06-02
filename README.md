# Very short description of the package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jakejames/automated-repositories.svg?style=flat-square)](https://packagist.org/packages/jakejames/automated-repositories)
[![Build Status](https://travis-ci.com/JakeJames97/Automated-Repositories.svg?token=RZLqTCZSeqTmbxpWj5Dg&branch=master)](https://travis-ci.com/JakeJames97/Automated-Repositories)
[![Total Downloads](https://img.shields.io/packagist/dt/jakejames/automated-repositories.svg?style=flat-square)](https://packagist.org/packages/jakejames/automated-repositories)

This is a simple package for generating standard templates for the repository pattern.

## Installation

You can install the package via composer:

```bash
composer require jakejames/automated-repositories --dev
```

## Usage

``` php
Run the following command:

php artisan make:repository { name }

We recommend using the following pattern for naming:
- RegisterRepository
- LoginRepository

This will use the word before 'Repository'
as your contract name and service provider name

E.g
'LoginRepository' will generate the following files:
LoginRepository,
Login (contract),
LoginServiceProvider

Once the files have been generated, the command will attempt to
register the new service provider inside your config/app.php
```

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email jake@jump24.co.uk instead of using the issue tracker.

## Credits

- [Jake James](https://github.com/jakejames)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
