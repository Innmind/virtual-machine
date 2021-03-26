<?php
declare(strict_types = 1);

use Innmind\CLI\{
    Command,
    Command\Arguments,
    Command\Options,
    Environment,
};

return new class implements Command
{
    public function __invoke(Environment $env, Arguments $arguments, Options $options): void
    {
        sleep(10);
    }

    public function toString(): string
    {
        return 'worker';
    }
};
