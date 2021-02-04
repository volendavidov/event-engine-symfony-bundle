<?php

declare(strict_types=1);

namespace ADS\Bundle\EventEngineBundle\Type;

use LogicException;

use function class_exists;
use function is_string;
use function preg_replace;

trait DefaultTypeName
{
    /**
     * @return class-string|null
     */
    private static function getTypeClassNameForState(): ?string
    {
        $stateClass = static::class;

        $typeClass = preg_replace('/(\w)+$/', 'Type', $stateClass);

        if (! class_exists($typeClass)) {
            return null;
        }

        return $typeClass;
    }

    public static function __type(): string
    {
        $typeClassNameForState = static::getTypeClassNameForState();
        if (! is_string($typeClassNameForState)) {
            throw new LogicException('Unable to auto detect the type class for ' . static::class);
        }

        return $typeClassNameForState::typeRefName();
    }
}
