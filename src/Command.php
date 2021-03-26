<?php
declare(strict_types = 1);

namespace Innmind\VirtualMachine;

use Innmind\Server\Control\Server\Command as Concrete;
use Innmind\Stream\Readable;

final class Command
{
    /** @var non-empty-list<callable(Concrete): Concrete> */
    private array $maps;

    /**
     * @param callable(Concrete): Concrete $command
     */
    private function __construct(callable $command)
    {
        $this->maps = [$command];
    }

    public static function of(string $command): self
    {
        return new self(
            static fn(Concrete $concrete): Concrete => $concrete->withArgument($command),
        );
    }

    public function withArgument(string $value): self
    {
        $self = clone $this;
        $self->maps[] = static fn(Concrete $command): Concrete => $command->withArgument($value);

        return $self;
    }

    public function withOption(string $key, string $value = null): self
    {
        $self = clone $this;
        $self->maps[] = static fn(Concrete $command): Concrete => $command->withOption($key, $value);

        return $self;
    }

    public function withShortOption(string $key, string $value = null): self
    {
        $self = clone $this;
        $self->maps[] = static fn(Concrete $command): Concrete => $command->withShortOption($key, $value);

        return $self;
    }

    public function withInput(Readable $input): self
    {
        $self = clone $this;
        $self->maps[] = static fn(Concrete $command): Concrete => $command->withInput($input);

        return $self;
    }

    /**
     * @internal
     */
    public function map(Concrete $command): Concrete
    {
        /**
         * @psalm-suppress MixedInferredReturnType
         * @psalm-suppress MixedReturnStatement
         */
        return \array_reduce(
            $this->maps,
            static fn(Concrete $command, callable $map): Concrete => $map($command),
            $command,
        );
    }
}
