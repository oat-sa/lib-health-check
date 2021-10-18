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

use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Throwable;

class CheckerResult
{
    /** @var bool */
    private $success;

    /** @var ?string */
    private $message;

    /** @var array */
    private $context;

    public function __construct(bool $success = true, string $message = null, array $context = [])
    {
        $this->success = $success;
        $this->message = $message;
        $this->context = $context;
    }

    public static function createFromThrowable(Throwable $exception): self
    {
        return (new static())
            ->setSuccess(false)
            ->setMessage($exception->getMessage())
            ->setContextFromThrowable($exception);
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function setSuccess(bool $success): self
    {
        $this->success = $success;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function setContext(array $context): self
    {
        $this->context = $context;

        return $this;
    }

    public function setContextFromThrowable(Throwable $exception): self
    {
        $flattenedException = FlattenException::createFromThrowable($exception);

        return $this->setContext([
            'class' => $flattenedException->getClass(),
            'file'  => $flattenedException->getFile(),
            'line'  => $flattenedException->getLine(),
            'trace' => $flattenedException->getTrace(),
        ]);
    }
}
