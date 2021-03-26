<?php
declare(strict_types = 1);

use Innmind\VirtualMachine as VM;
use Innmind\CLI\{
    Command,
    Command\Arguments,
    Command\Options,
    Environment,
};
use Innmind\Immutable\RegExp;

return static fn(VM\VirtualMachine $vm) => new class($vm) implements Command
{
    private VM\VirtualMachine $vm;

    public function __construct(VM\VirtualMachine $vm)
    {
        $this->vm = $vm;
    }

    public function __invoke(Environment $env, Arguments $arguments, Options $options): void
    {
        $this->vm->processes()->daemon(VM\Command::of('worker'));
        $this
            ->vm
            ->processes()
            ->all()
            ->foreach(fn($process) => print($process->pid()->toString().' '.$process->command()->toString()."\n"));
    }

    public function toString(): string
    {
        return 'supervisor';
    }
};
