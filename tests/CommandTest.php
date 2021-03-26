<?php
declare(strict_types = 1);

namespace Tests\Innmind\VirtualMachine;

use Innmind\VirtualMachine\Command;
use Innmind\Server\Control\Server\Command as Concrete;
use Innmind\Stream\Readable;
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class CommandTest extends TestCase
{
    use BlackBox;

    public function testOf()
    {
        $this
            ->forAll(
                Set\Strings::madeOf(Set\Chars::alphanumerical())->between(1, 10),
                Set\Strings::madeOf(Set\Chars::alphanumerical())->between(1, 10),
            )
            ->then(function($bin, $command) {
                $this->assertSame(
                    Concrete::foreground($bin)->withArgument($command)->toString(),
                    Command::of($command)->map(Concrete::foreground($bin))->toString(),
                );
                $this->assertSame(
                    Concrete::background($bin)->withArgument($command)->toString(),
                    Command::of($command)->map(Concrete::background($bin))->toString(),
                );
            });
    }

    public function testWithArgument()
    {
        $this
            ->forAll(
                Set\Strings::madeOf(Set\Chars::alphanumerical())->between(1, 10),
                Set\Strings::madeOf(Set\Chars::alphanumerical())->between(1, 10),
                Set\Strings::madeOf(Set\Chars::alphanumerical())->between(1, 10),
            )
            ->then(function($bin, $command, $argument) {
                $this->assertSame(
                    Concrete::foreground($bin)
                        ->withArgument($command)
                        ->withArgument($argument)
                        ->toString(),
                    Command::of($command)
                        ->withArgument($argument)
                        ->map(Concrete::foreground($bin))
                        ->toString(),
                );
            });
    }

    public function testWithOption()
    {
        $this
            ->forAll(
                Set\Strings::madeOf(Set\Chars::alphanumerical())->between(1, 10),
                Set\Strings::madeOf(Set\Chars::alphanumerical())->between(1, 10),
                Set\Strings::madeOf(Set\Chars::alphanumerical())->between(1, 10),
                Set\Strings::madeOf(Set\Chars::alphanumerical())->between(1, 10),
            )
            ->then(function($bin, $command, $option, $value) {
                $this->assertSame(
                    Concrete::foreground($bin)
                        ->withArgument($command)
                        ->withOption($option)
                        ->toString(),
                    Command::of($command)
                        ->withOption($option)
                        ->map(Concrete::foreground($bin))
                        ->toString(),
                );
                $this->assertSame(
                    Concrete::foreground($bin)
                        ->withArgument($command)
                        ->withOption($option, $value)
                        ->toString(),
                    Command::of($command)
                        ->withOption($option, $value)
                        ->map(Concrete::foreground($bin))
                        ->toString(),
                );
            });
    }

    public function testWithShortOption()
    {
        $this
            ->forAll(
                Set\Strings::madeOf(Set\Chars::alphanumerical())->between(1, 10),
                Set\Strings::madeOf(Set\Chars::alphanumerical())->between(1, 10),
                Set\Strings::madeOf(Set\Chars::alphanumerical())->between(1, 10),
                Set\Strings::madeOf(Set\Chars::alphanumerical())->between(1, 10),
            )
            ->then(function($bin, $command, $option, $value) {
                $this->assertSame(
                    Concrete::foreground($bin)
                        ->withArgument($command)
                        ->withShortOption($option)
                        ->toString(),
                    Command::of($command)
                        ->withShortOption($option)
                        ->map(Concrete::foreground($bin))
                        ->toString(),
                );
                $this->assertSame(
                    Concrete::foreground($bin)
                        ->withArgument($command)
                        ->withShortOption($option, $value)
                        ->toString(),
                    Command::of($command)
                        ->withShortOption($option, $value)
                        ->map(Concrete::foreground($bin))
                        ->toString(),
                );
            });
    }

    public function testWithInput()
    {
        $this
            ->forAll(
                Set\Strings::madeOf(Set\Chars::alphanumerical())->between(1, 10),
                Set\Strings::madeOf(Set\Chars::alphanumerical())->between(1, 10),
            )
            ->then(function($bin, $command) {
                $input = $this->createMock(Readable::class);

                $this->assertSame(
                    $input,
                    Command::of($command)
                        ->withInput($input)
                        ->map(Concrete::foreground($bin))
                        ->input(),
                );
            });
    }
}
