# virtual-machine

[![Build Status](https://github.com/innmind/virtual-machine/workflows/CI/badge.svg?branch=master)](https://github.com/innmind/virtual-machine/actions?query=workflow%3ACI)
[![codecov](https://codecov.io/gh/innmind/virtual-machine/branch/develop/graph/badge.svg)](https://codecov.io/gh/innmind/virtual-machine)
[![Type Coverage](https://shepherd.dev/github/innmind/virtual-machine/coverage.svg)](https://shepherd.dev/github/innmind/virtual-machine)

Small abstraction on top of `innmind/cli` to manage processes and filesystem within the context of a project.

This is intended for CLI applications where the binary must be run within the context of the project.

**Note**: do **not** use this package for CLI tools that you can run from anywhere in your operating system.

## Installation

```sh
composer require innmind/virtual-machine
```

## Usage

```php
# some/binary.php

use Innmind\CLI\{
    Main,
    Environment,
};
use Innmind\OperatingSystem\OperatingSystem;
use Innmind\VirtualMachine\{
    VirtualMachine,
    Command,
};
use Innmind\Server\Status;
use Innmind\Server\Control;
use Innmind\Immutable\Set;

new class extends Main
{
    protected function main(Environment $env, OperatingSystem $os): void
    {
        $vm = VirtualMachine::of($env, $os);

        if ($env->workingDirectory()->toString() !== __DIR__.'/') {
            throw new \Exception('binary.php must me executed from within the "some/" directory')
        }

        // all required paths are resolved from the working directory so you don't
        // have to do the resolution yourself, and it's safe to require a file
        // from anywhere within your app
        $vm->filesystem()->require(Path::of('other_file.php'));

        // this is similar to $os->status()->processes()->all() but here the set
        // will only contain processes that are running whithin the working
        // directory for the same "binary.php" bin
        /** @var Set<Status\Server\Process> */
        $processes = $vm->processes()->all();

        // this is a shortcut to start a new process with the command
        // "php binary.php 'some-command'" started within the same working
        // directory. So you don't have to repeat the the binary name and
        // specify the working directory. The process is started in the foreground.
        /** @var Control\Server\Process */
        $process = $vm->processes()->execute(Command::of('some-command'));

        // Same as the line above except the process is started in the background
        $vm->processes()->daemon(Command::of('some-daemon'));
    }
}
```

**Note**: of course you can name your bin file anyway you want, not just `binary.php`.
