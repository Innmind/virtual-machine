<?php
declare(strict_types = 1);

namespace Innmind\VirtualMachine;

use Innmind\CLI\Environment;
use Innmind\OperatingSystem\OperatingSystem;

final class VirtualMachine
{
    private Filesystem $filesystem;
    private Processes $processes;

    private function __construct(Environment $env, OperatingSystem $os)
    {
        $this->filesystem = new Filesystem($env->workingDirectory());
        $this->processes = new Processes($env, $os);
    }

    public static function of(Environment $env, OperatingSystem $os): self
    {
        return new self($env, $os);
    }

    public function processes(): Processes
    {
        return $this->processes;
    }

    public function filesystem(): Filesystem
    {
        return $this->filesystem;
    }
}
