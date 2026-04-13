<?php

namespace Modules\Shared\DataTransferObjects;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

abstract class DataTransferObject implements Arrayable, JsonSerializable
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = get_object_vars($this);

        return array_map(
            fn (mixed $value): mixed => $this->normalizeValue($value),
            $data
        );
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    protected function normalizeValue(mixed $value): mixed
    {
        if ($value instanceof Arrayable) {
            return $value->toArray();
        }

        if ($value instanceof JsonSerializable) {
            return $value->jsonSerialize();
        }

        if (is_array($value)) {
            return array_map(fn (mixed $item): mixed => $this->normalizeValue($item), $value);
        }

        return $value;
    }
}
