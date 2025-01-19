<?php

declare(strict_types=1);

namespace Framework\Contracts;

interface RuleInterface
{
    public function validate(array $data, string $field, array $params): bool; // validacija za jedno polje, single field

    public function getMessage(array $data, string $field, array $params): string; // sluzi za generisanje greske
}
