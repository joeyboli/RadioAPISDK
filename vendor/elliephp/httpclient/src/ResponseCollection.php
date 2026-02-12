<?php

namespace ElliePHP\Components\HttpClient;

readonly class ResponseCollection
{
    public function __construct(private array $items)
    {
    }

    public function all(): array
    {
        return $this->items;
    }

    public function first(): mixed
    {
        return $this->items[0] ?? null;
    }

    public function last(): mixed
    {
        return $this->items[array_key_last($this->items)] ?? null;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }

    public function pluck(string $key): array
    {
        return array_map(
            static fn($item) => is_array($item) ? ($item[$key] ?? null) : $item->$key ?? null,
            $this->items
        );
    }

    public function where(string $key, mixed $value): self
    {
        $filtered = array_filter($this->items, static function ($item) use ($key, $value) {
            $itemValue = is_array($item) ? ($item[$key] ?? null) : $item->$key ?? null;
            return $itemValue === $value;
        });

        return new self(array_values($filtered));
    }

    public function map(callable $callback): self
    {
        return new self(array_map($callback, $this->items));
    }

    public function filter(callable $callback): self
    {
        return new self(array_values(array_filter($this->items, $callback)));
    }

    public function toArray(): array
    {
        return $this->items;
    }

    public function toJson(): string
    {
        return json_encode($this->items);
    }
}