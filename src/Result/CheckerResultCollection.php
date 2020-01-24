<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace OAT\Library\HealthCheck\Result;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;

class CheckerResultCollection implements IteratorAggregate, Countable, JsonSerializable
{
    /** @var CheckerResult[] */
    private $results = [];

    public function add(string $identifier, CheckerResult $result): self
    {
        $this->results[$identifier] = $result;

        return $this;
    }

    public function hasErrors(): bool
    {
        foreach ($this as $result) {
            if (!$result->isSuccess()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return CheckerResult[]|ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->results);
    }

    public function count(): int
    {
        return $this->getIterator()->count();
    }

    public function jsonSerialize(): array
    {
        return [
            'success' => !$this->hasErrors(),
            'checkers' => array_map(
                function (CheckerResult $result): array {
                    return [
                        'success' => $result->isSuccess(),
                        'message' => $result->getMessage(),
                    ];
                },
                $this->results
            )
        ];
    }
}
