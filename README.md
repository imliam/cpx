![Run Composer packages, efortlessly.](./banner.png)

# cpx - Composer Package Executor

Run any command from any composer package, even if it's not installed in your project.

cpx is to Composer what npx is to npm.

## Installation

Using [Composer](https://getcomposer.org/doc/00-intro.md), you can install cpx by running:

```bash
composer global require cpx/cpx
```

## Usage

You can run a command using cpx by passing through the package name and the command you want to run:

> Note: A package name is what you'd use to require the package in your `composer.json` file, e.g. `friendsofphp/php-cs-fixer`
> You can also use constraints to specify a version, e.g. `friendsofphp/php-cs-fixer:^3.0`

```bash
cpx <package-name> <command> [arguments]
# Example: cpx friendsofphp/php-cs-fixer php-cs-fixer fix ./src
```

If the package only has one command, or the command name is the same as the package's name, you can omit the command from the end:

```bash
cpx <package-name> [arguments]
# Example: cpx friendsofphp/php-cs-fixer fix ./src
```

Behind the scenes, cpx will install the package into a separate directory and run the command, keeping it separate from both your project and global Composer dependencies. Subsequent runs of the same package will use the same installation and run quickly, unless you specify a different version or there is an update to the package available.

---

As different projects may use different tools to do the same job, cpx provides a set of commands to normalise common tasks, using the packages a project has installed; `cpx check`, `cpx format` and `cpx test`.

For each of these commands, you can continue to pass through additional arguments and flags to the underlying tool as if you were running it directly.

### cpx check

`cpx check` (or the aliases `cpx analyze` or `cpx analyse`) runs a static analysis tool on your codebase (e.g. PHPStan, Psalm or Phan).

### cpx format

`cpx format` (or the alias `cpx fmt`) Runs a code formatting tool on your codebase (e.g. PHP-CS-Fixer, Pint or PHP_CodeSniffer).

You can pass the `--dry-run` flag to see what changes would be made without actually making them.

### cpx test

`cpx test` runs a testing framework on your codebase (e.g. Pest, PHPUnit or Codeception).

### cpx aliases

`cpx aliases` will show a list of popular packages that have been aliased to make them easier to run. You can use these aliases to run a package without needing to remember the full vendor, package and command name.

For example, `cpx php-cs-fixer` is an alias for `cpx friendsofphp/php-cs-fixer`, and `cpx laravel` is an alias for `cpx laravel/installer`.

### cpx list

`cpx list` shows all the packages you have run via cpx and have installed.

### cpx update

While cpx will automatically check for updates to a Composer package when you run a command, you can also manually update packages.

`cpx update` will update the local version of all packages run via cpx to the latest version, according to their version constraints.

`cpx update <vendor>` or `cpx update <vendor>/<package>` will update only the specified packages.

### cpx clean

`cpx clean` will remove all the packages you have run via cpx but haven't used recently. `cpx clean --all` will remove all packages regardless of when they have run.

### cpx exec and cpx tinker

cpx gives you multiple ways to run PHP code quickly, perfect for running scratch files or quickly running code in your project.

- `cpx exec <file.php>` will run a plain PHP file.
- `cpx exec -r <raw php code>` will execute the given PHP code.
- `cpx tinker` will open an interactive REPL in the terminal for your project.

### cpx new

`cpx new <vendor>/<package> <project-name>` or `cpx new <vendor>/<package>:<version> <project-name>` will create a new project from the specified package. This is useful for quickly creating a new project without needing to install the package globally.

When using these commands, you get the following benefits:

- **Automatic Autoloaders** - When running a PHP file, it will automatically detect and use Composer's autoloader if it exists in the current or a parent directory
- **Class Aliasing** - If a class is used in the file but the namespace isn't imported, cpx will try to find an appropriate one to alias.
- **Laravel Bootstrapping** - If the autoloader directory happens to be a Laravel project directory, cpx will bootstrap the application, setting up service providers and such.
- **composer_require()** - You can use the function like `composer_require('vendor/package')` in the executed script and those packages will be autoloaded into the file.

### cpx help

`cpx help` will show a list of all the commands available in cpx.

## Advanced Usage:

### Specifying a custom repository

If you want to use a custom repository to install packages from, you can specify it using the `--repo` flag:

1. composer registries: `cpx <vend>/<pack> --repo=https://composer.example.com`
   - supported schemes: `http:`, `https:`
2. git repos: `cpx <vend>/<pack>  --repo=git+https://github.com/<vendor>/<repo>`
   - supported schemes: `git+http:`, `git+https:`, `ssh:`, `git+ssh:`
3. local paths: `cpx <vend>/<pack>  --repo=path:/some/place/on/disk`
   - supported schemes: `file:`, `path:`

This also extends to the new command:

`cpx new <vend>/<pack> project-name --repo=git+https://gitlab.com/<vendor>/<repo>`

## FAQ:

### Why not just use global composer?

Installing packages with `composer global require` is a great way to install packages that you want to use globally, but it has some downsides:

- You can get conflicts with other global dependencies (especially tooling using common dependencies like `nikic/php-parser` and `symfony/console`)
- You might need to switch between versions of the package between runs, but can only have one version installed globally
- You need to remember to update your global packages if you are using them long-term
- You might only use a package's command once, and don't want to install it globally

### What kind of one-off commands might I want to run with cpx?

There are a few reasons you might want to run a one-off command with cpx:

- Applying code-style fixes using a tool like `php-cs-fixer` or `rector`
- Running analysis of your codebase using a tool like `phploc` or `phpstan`
- Creating files or stubs using a tool like `laravel/installer`

### Can I use multiple different versions of a tool with cpx?

Yes, cpx will manage the package versions for you, so you can run any version of the package you want.

### Why does the source code of cpx have no dependencies?

The code is deliberately written in a way that it doesn't need any dependencies to run, so it has no chance of conflicting with your global composer dependencies if you use them for other things, as this is one of the problems cpx is trying to solve.

## Credits

- [Liam Hammett](https://github.com/imliam)
- [All Contributors](https://github.com/imliam/cpx/contributors)

```

```
