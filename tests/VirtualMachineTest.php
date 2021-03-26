<?php
declare(strict_types = 1);

namespace Tests\Innmind\VirtualMachine;

use Innmind\VirtualMachine\{
    VirtualMachine,
    Processes,
    Filesystem,
};
use Innmind\CLI\Environment;
use Innmind\OperatingSystem\OperatingSystem;
use PHPUnit\Framework\TestCase;

class VirtualMachineTest extends TestCase
{
    public function testProcesses()
    {
        $vm = VirtualMachine::of(
            $this->createMock(Environment::class),
            $this->createMock(OperatingSystem::class),
        );

        $this->assertInstanceOf(Processes::class, $vm->processes());
    }

    public function testFilesystem()
    {
        $vm = VirtualMachine::of(
            $this->createMock(Environment::class),
            $this->createMock(OperatingSystem::class),
        );

        $this->assertInstanceOf(Filesystem::class, $vm->filesystem());
    }
}
