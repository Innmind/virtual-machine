<?php
declare(strict_types = 1);

namespace Innmind\VirtualMachine;

use Innmind\CLI\Environment;
use Innmind\OperatingSystem\OperatingSystem;
use Innmind\Server\Control\Server\{
    Process,
    Command as Concrete,
};
use Innmind\Server\Status\Server as Status;
use Innmind\Immutable\{
    Set,
    Str,
    RegExp,
};

final class Processes
{
    private Environment $env;
    private OperatingSystem $os;

    public function __construct(Environment $env, OperatingSystem $os)
    {
        $this->env = $env;
        $this->os = $os;
    }

    public function execute(Command $command): Process
    {
        return $this->os->control()->processes()->execute(
            $command
                ->map(Concrete::foreground($this->bin()))
                ->withWorkingDirectory($this->env->workingDirectory()),
        );
    }

    /**
     * This will execute the given command in the background
     */
    public function daemon(Command $command): void
    {
        $this->os->control()->processes()->execute(
            $command
                ->map(Concrete::background($this->bin()))
                ->withWorkingDirectory($this->env->workingDirectory()),
        );
    }

    /**
     * @return Set<Process>
     */
    public function all(): Set
    {
        // this will list all PIDs that have a relation with the working directory
        // usually this will represent all processes having this directory as
        // their working directory
        $lsof = $this->os->control()->processes()->execute(
            Concrete::foreground('lsof')
                ->withArgument('+D')
                ->withArgument($this->env->workingDirectory()->toString())
                ->withShortOption('t'),
        );
        $lsof->wait();
        $pids = Str::of($lsof->output()->toString())
            ->split("\n")
            ->map(static fn($line) => $line->trim())
            ->filter(static fn($line) => !$line->empty())
            ->mapTo('int', static fn($line) => (int) $line->toString());

        return $this
            ->os
            ->status()
            ->processes()
            ->all()
            ->filter(function(int $_, Status\Process $process): bool {
                return $process->command()->matches(RegExp::of("~{$this->bin()}~"));
            })
            ->filter(static function(int $pid) use ($pids): bool {
                return $pids->contains($pid);
            })
            ->values()
            ->toSetOf(Status\Process::class);
    }

    private function bin(): string
    {
        // when running a PHP script like "./bin/console" both variables below
        // will contain this string but if you run the script like "php bin/console"
        // then $bin will contain full path to the php binary and $argument will
        // contain 'bin/console'
        $bin = $this->env->variables()->get('_');
        $argument = $this->env->arguments()->first();

        if ($argument === $bin) {
            return $bin;
        }

        return "$bin $argument";
    }
}
