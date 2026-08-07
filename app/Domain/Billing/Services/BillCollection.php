<?php

namespace App\Domain\Billing\Services;

use App\Domain\Billing\DTOs\BillData;
use ArrayIterator;
use Countable;
use IteratorAggregate;

final readonly class BillCollection implements Countable, IteratorAggregate
{
    /**
     * @param  array<int, BillData>  $items
     */
    private function __construct(private array $items)
    {
    }

    public static function from(array $items): self
    {
        return new self($items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @return ArrayIterator<int, BillData>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    public function filter(callable $callback): self
    {
        return new self(array_values(array_filter($this->items, $callback)));
    }

    public function map(callable $callback): self
    {
        return new self(array_map($callback, $this->items));
    }

    /**
     * @return array<int, BillData>
     */
    public function all(): array
    {
        return $this->items;
    }
}
