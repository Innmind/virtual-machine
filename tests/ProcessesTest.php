<?php
declare(strict_types = 1);

namespace Tests\Innmind\VirtualMachine;

use Innmind\VirtualMachine\{
    Processes,
    Command,
};
use Innmind\OperatingSystem\OperatingSystem;
use Innmind\CLI\Environment;
use Innmind\Server\Control\Server;
use Innmind\Server\Status\Server as Status;
use Innmind\TimeContinuum\PointInTime;
use Innmind\Immutable\{
    Map,
    Sequence,
    Set as ISet,
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};
use Fixtures\Innmind\Url\Path;

class ProcessesTest extends TestCase
{
    use BlackBox;

    public function testExecuteWithSelfLaunchedBin()
    {
        $this
            ->forAll(Set\Strings::madeOf(Set\Chars::alphanumerical())->between(1, 10))
            ->then(function($argument) {
                $env = $this->createMock(Environment::class);
                $env
                    ->method('arguments')
                    ->willReturn(Sequence::strings('./bin/console'));
                $env
                    ->method('variables')
                    ->willReturn(Map::of('string', 'string')('_', './bin/console'));
                $os = $this->createMock(OperatingSystem::class);
                $os
                    ->method('control')
                    ->willReturn($server = $this->createMock(Server::class));
                $server
                    ->method('processes')
                    ->willReturn($serverProcesses = $this->createMock(Server\Processes::class));
                $serverProcesses
                    ->expects($this->once())
                    ->method('execute')
                    ->with($this->callback(static function($command) use ($argument) {
                        return $command->toBeRunInBackground() === false &&
                            $command->toString() === Server\Command::foreground('./bin/console')->withArgument($argument)->toString();
                    }))
                    ->willReturn($expected = $this->createMock(Server\Process::class));
                $processes = new Processes($env, $os);

                $this->assertSame($expected, $processes->execute(Command::of($argument)));
            });
    }

    public function testExecuteWithPHPBin()
    {
        $this
            ->forAll(Set\Strings::madeOf(Set\Chars::alphanumerical())->between(1, 10))
            ->then(function($argument) {
                $env = $this->createMock(Environment::class);
                $env
                    ->method('arguments')
                    ->willReturn(Sequence::strings('bin/console'));
                $env
                    ->method('variables')
                    ->willReturn(Map::of('string', 'string')('_', 'php'));
                $os = $this->createMock(OperatingSystem::class);
                $os
                    ->method('control')
                    ->willReturn($server = $this->createMock(Server::class));
                $server
                    ->method('processes')
                    ->willReturn($serverProcesses = $this->createMock(Server\Processes::class));
                $serverProcesses
                    ->expects($this->once())
                    ->method('execute')
                    ->with($this->callback(static function($command) use ($argument) {
                        return $command->toBeRunInBackground() === false &&
                            $command->toString() === Server\Command::foreground('php bin/console')->withArgument($argument)->toString();
                    }))
                    ->willReturn($expected = $this->createMock(Server\Process::class));
                $processes = new Processes($env, $os);

                $this->assertSame($expected, $processes->execute(Command::of($argument)));
            });
    }

    public function testDaemonWithSelfLaunchedBin()
    {
        $this
            ->forAll(Set\Strings::madeOf(Set\Chars::alphanumerical())->between(1, 10))
            ->then(function($argument) {
                $env = $this->createMock(Environment::class);
                $env
                    ->method('arguments')
                    ->willReturn(Sequence::strings('./bin/console'));
                $env
                    ->method('variables')
                    ->willReturn(Map::of('string', 'string')('_', './bin/console'));
                $os = $this->createMock(OperatingSystem::class);
                $os
                    ->method('control')
                    ->willReturn($server = $this->createMock(Server::class));
                $server
                    ->method('processes')
                    ->willReturn($serverProcesses = $this->createMock(Server\Processes::class));
                $serverProcesses
                    ->expects($this->once())
                    ->method('execute')
                    ->with($this->callback(static function($command) use ($argument) {
                        return $command->toBeRunInBackground() === true &&
                            $command->toString() === Server\Command::background('./bin/console')->withArgument($argument)->toString();
                    }));
                $processes = new Processes($env, $os);

                $this->assertNull($processes->daemon(Command::of($argument)));
            });
    }

    public function testDaemonWithPHPBin()
    {
        $this
            ->forAll(Set\Strings::madeOf(Set\Chars::alphanumerical())->between(1, 10))
            ->then(function($argument) {
                $env = $this->createMock(Environment::class);
                $env
                    ->method('arguments')
                    ->willReturn(Sequence::strings('bin/console'));
                $env
                    ->method('variables')
                    ->willReturn(Map::of('string', 'string')('_', 'php'));
                $os = $this->createMock(OperatingSystem::class);
                $os
                    ->method('control')
                    ->willReturn($server = $this->createMock(Server::class));
                $server
                    ->method('processes')
                    ->willReturn($serverProcesses = $this->createMock(Server\Processes::class));
                $serverProcesses
                    ->expects($this->once())
                    ->method('execute')
                    ->with($this->callback(static function($command) use ($argument) {
                        return $command->toBeRunInBackground() === true &&
                            $command->toString() === Server\Command::background('php bin/console')->withArgument($argument)->toString();
                    }));
                $processes = new Processes($env, $os);

                $this->assertNull($processes->daemon(Command::of($argument)));
            });
    }

    public function testListProcesses()
    {
        $this
            ->forAll(
                Path::any(),
                Set\Strings::any(),
            )
            ->then(function($workingDirectory, $random) {
                $processesList = Map::of('int', Status\Process::class)
                    (
                        3,
                        new Status\Process(
                            new Status\Process\Pid(3),
                            new Status\Process\User('self'),
                            new Status\Cpu\Percentage(1),
                            new Status\Process\Memory(1),
                            $this->createMock(PointInTime::class),
                            new Status\Process\Command('bin/console'),
                        ),
                    )
                    (
                        4,
                        new Status\Process(
                            new Status\Process\Pid(4),
                            new Status\Process\User('self'),
                            new Status\Cpu\Percentage(1),
                            new Status\Process\Memory(1),
                            $this->createMock(PointInTime::class),
                            new Status\Process\Command('bin/console'),
                        ),
                    )
                    (
                        42,
                        new Status\Process(
                            new Status\Process\Pid(42),
                            new Status\Process\User('self'),
                            new Status\Cpu\Percentage(1),
                            new Status\Process\Memory(1),
                            $this->createMock(PointInTime::class),
                            new Status\Process\Command('-bash'),
                        ),
                    )
                    (
                        5,
                        new Status\Process(
                            new Status\Process\Pid(5),
                            new Status\Process\User('self'),
                            new Status\Cpu\Percentage(1),
                            new Status\Process\Memory(1),
                            $this->createMock(PointInTime::class),
                            new Status\Process\Command('bin/console'),
                        ),
                    );
                $env = $this->createMock(Environment::class);
                $env
                    ->method('workingDirectory')
                    ->willReturn($workingDirectory);
                $env
                    ->method('variables')
                    ->willReturn(Map::of('string', 'string')('_', 'bin/console'));
                $env
                    ->method('arguments')
                    ->willReturn(Sequence::strings('bin/console'));
                $os = $this->createMock(OperatingSystem::class);
                $os
                    ->method('control')
                    ->willReturn($control = $this->createMock(Server::class));
                $control
                    ->method('processes')
                    ->willReturn($controlProcesses = $this->createMock(Server\Processes::class));
                $controlProcesses
                    ->expects($this->exactly(3))
                    ->method('execute')
                    ->withConsecutive(
                        [$this->callback(static function($command) {
                            return $command->toString() === "lsof '-p' '3' | 'grep' 'cwd'";
                        })],
                        [$this->callback(static function($command) {
                            return $command->toString() === "lsof '-p' '4' | 'grep' 'cwd'";
                        })],
                        [$this->callback(static function($command) {
                            return $command->toString() === "lsof '-p' '5' | 'grep' 'cwd'";
                        })],
                    )
                    ->will($this->onConsecutiveCalls(
                        $process1 = $this->createMock(Server\Process::class),
                        $process2 = $this->createMock(Server\Process::class),
                        $process3 = $this->createMock(Server\Process::class),
                    ));
                $process1
                    ->method('output')
                    ->willReturn($output1 = $this->createMock(Server\Process\Output::class));
                $output1
                    ->method('toString')
                    ->willReturn($random);
                $process2
                    ->method('output')
                    ->willReturn($output2 = $this->createMock(Server\Process\Output::class));
                $output2
                    ->method('toString')
                    ->willReturn($random.$workingDirectory->toString().$random);
                $process3
                    ->method('output')
                    ->willReturn($output3 = $this->createMock(Server\Process\Output::class));
                $output3
                    ->method('toString')
                    ->willReturn($random);
                $os
                    ->method('status')
                    ->willReturn($status = $this->createMock(Status::class));
                $status
                    ->method('processes')
                    ->willReturn($statusProcesses = $this->createMock(Status\Processes::class));
                $statusProcesses
                    ->method('all')
                    ->willReturn($processesList);
                $processes = new Processes($env, $os);

                $all = $processes->all();

                $this->assertInstanceOf(ISet::class, $all);
                $this->assertSame(Status\Process::class, $all->type());
                $this->assertCount(1, $all);
                $this->assertSame($processesList->get(4), $all->find(static fn() => true));
            });
    }
}
