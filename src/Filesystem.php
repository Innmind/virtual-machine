<?php
declare(strict_types = 1);

namespace Innmind\VirtualMachine;

use Innmind\Url\{
    Path,
    RelativePath,
};

final class Filesystem
{
    private Path $workingDirectory;

    public function __construct(Path $workingDirectory)
    {
        $this->workingDirectory = $workingDirectory;
    }

    /**
     * Relative to the working directory
     */
    public function require(RelativePath $path): mixed
    {
        /** @psalm-suppress UnresolvableInclude */
        return require $this->workingDirectory->resolve($path)->toString();
    }
}
