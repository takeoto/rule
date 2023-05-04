<?php

declare(strict_types=1);

namespace Takeoto\Rule\Dictionary;

final class ClaimDict
{
    public const TYPE = 'claim.type';
    public const ERROR_MESSAGE = 'claim.error-message';

    public const TYPE_OBJECT = 'claim.type.object';
    public const TYPE_OBJECT_INSTANCE = 'claim.type.object.instance';

    public const TYPE_ONE_OF = 'claim.type.one-of';
    public const TYPE_ONE_OF_ITEMS = 'claim.type.one-of.items';

    public const TYPE_ARRAY = 'claim.type.array';
    public const TYPE_ARRAY_STRUCTURE = 'claim.type.array.fields';
    public const TYPE_ARRAY_ALLOWED_EXTRA_FIELDS = 'claim.type.array.allow-extra';
    public const TYPE_ARRAY_ALLOWED_MISSING_FIELDS = 'claim.type.array.missing-extra';
    public const TYPE_ARRAY_OPTIONAL_FIELD = 'claim.type.array.optional-field';
    public const TYPE_ARRAY_REQUIRED_FIELD = 'claim.type.array.required-field';
    public const TYPE_ARRAY_EACH = 'claim.type.array.each';

    public const TYPE_STRING = 'claim.type.string';
    public const TYPE_STRING_PATTERN = 'claim.type.string.pattern';
    public const TYPE_STRING_LENGTH_MIN = 'claim.type.string.min';
    public const TYPE_STRING_LENGTH_MAX = 'claim.type.string.max';

    public const TYPE_CALLBACK = 'claim.type.callback';
    public const TYPE_CALLBACK_CLOSURE = 'claim.type.callback.closure';

    public const TYPE_INT = 'claim.type.int';
    public const TYPE_INT_SOFT = 'claim.type.int.soft';
    public const TYPE_INT_MIN = 'claim.type.int.min';
    public const TYPE_INT_MAX = 'claim.type.int.max';

    public const TYPE_BOOL = 'claim.type.bool';
    public const TYPE_FLOAT = 'claim.type.float';
    public const TYPE_NUMERIC = 'claim.type.numeric';
    public const TYPE_SCALAR = 'claim.type.scalar';
    public const TYPE_ITERABLE = 'claim.type.iterable';
    public const TYPE_COUNTABLE = 'claim.type.countable';
    public const TYPE_CALLABLE = 'claim.type.callable';
    public const TYPE_RESOURCE = 'claim.type.resource';
    public const TYPE_NULL = 'claim.type.null';
}