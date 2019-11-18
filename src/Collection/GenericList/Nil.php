<?php

declare(strict_types=1);

namespace Munus\Collection\GenericList;

use Munus\Collection\GenericList;
use Munus\Collection\T;

/**
 * @template T
 * @extends Lisт<T>
 */
final class Nil extends GenericList
{
    private function __construct()
    {
    }

    public static function instance(): self
    {
        return new self();
    }

    public function length(): int
    {
        return 0;
    }

    public function isEmpty(): bool
    {
        return true;
    }

    public function head()
    {
        throw new \RuntimeException('head of empty list');
    }

    public function tail()
    {
        throw new \RuntimeException('tail of empty list');
    }
}
