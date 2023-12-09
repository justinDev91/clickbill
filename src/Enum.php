<?php

declare(strict_types=1);

namespace App\Enum;

enum BillType: string
{
    case enCours = 'en cours';
    case valide = 'validé';
    case annule = 'annulé';

    /**
     * @return array<string,string>
     */
    public static function getAsArray(): array
    {
        return array_reduce(
            self::cases(),
            static fn (array $choices, BillType $type) => $choices + [$type->name => $type->value],
            [],
        );
    }
}