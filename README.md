# cpx - Execute Composer Package Binaries

cpx is a package runner for Composer packages, just like [npx](https://www.npmjs.com/package/npx) is for npm.

It allows you to easily run one-off commands reliably, without having to install the tool globally or 

## Installation

cpx can be installed like any other global package through Composer:

```bash
composer global require imliam/cpx
```

Or if you don't want to use Composer, you can install the binary directly:

```bash
curl https://githubusercontent.com/imliam/cpx/... >> /bin/cpx
```

## Example Usage

```bash
cpx laravel/installer new blog

cpx laravel/ui preset vue

cpx tuqqu/killposer

cpx https://gist.github.com/imliam/...

cpx laravel/ui@5.8 preset vue # Specify a version

cpx psalm/psalm
```

Other commands you could run:

```
envoy
killposer
laravel
laravel-zero
php-cs-fixer
phpcs
phpmd
phpstan
psalm
valet
```
