<?php
declare(strict_types = 1);

namespace Tests\Innmind\VirtualMachine;

use Innmind\VirtualMachine\Filesystem;
use Innmind\Url\Path;
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class FilesystemTest extends TestCase
{
    use BlackBox;

    public function testRequire()
    {
        $this
            ->forAll(
                new Set\Either(
                    Set\Integers::any(),
                    Set\RealNumbers::any(),
                    Set\Strings::any(),
                    Set\Elements::of(true, false),
                ),
                Set\Elements::of(
                    Path::of('data/foo'),
                    Path::of('data/bar'),
                ),
            )
            ->then(function($value, $path) {
                $filesystem = new Filesystem(Path::of(\getcwd().'/'));
                $data = \var_export($value, true);
                $code = <<<CODE
                <?php

                return $data;
                CODE;
                \file_put_contents($path->toString(), $code);

                $this->assertSame($value, $filesystem->require($path));
            });
    }
}
