<?php

declare(strict_types=1);

namespace Takeoto\Rule\Dictionary;

final class ClaimDict
{
    public const CLAIM_TYPE = 'claim.type';
    public const CLAIM_ERROR_MESSAGE = 'claim.error-message';

    public const OBJECT = 'claim.type.object';
    public const OBJECT_INSTANCE = 'claim.type.object.instance';

    public const ONE_OF = 'claim.type.one-of';
    public const ONE_OF_ITEMS = 'claim.type.one-of.items';

    public const ARRAY = 'claim.type.array';
    public const ARRAY_STRUCTURE = 'claim.type.array.fields';
    public const ARRAY_ALLOWED_EXTRA_FIELDS = 'claim.type.array.allow-extra';
    public const ARRAY_ALLOWED_MISSING_FIELDS = 'claim.type.array.missing-extra';
    public const ARRAY_OPTIONAL_FIELD = 'claim.type.array.optional-field';
    public const ARRAY_REQUIRED_FIELD = 'claim.type.array.required-field';
    public const ARRAY_EACH = 'claim.type.array.each';

    public const STRING = 'claim.type.string';
    public const STRING_PATTERN = 'claim.type.string.pattern';
    public const STRING_LENGTH_MIN = 'claim.type.string.min';
    public const STRING_LENGTH_MAX = 'claim.type.string.max';

    public const CALLBACK = 'claim.type.callback';
    public const CALLBACK_CLOSURE = 'claim.type.callback.closure';

    public const INT = 'claim.type.int';
    public const INT_SOFT = 'claim.type.int.soft';
    public const INT_MIN = 'claim.type.int.min';
    public const INT_MAX = 'claim.type.int.max';

    public const BOOL = 'claim.type.bool';
    public const FLOAT = 'claim.type.float';
    public const NUMERIC = 'claim.type.numeric';
    public const SCALAR = 'claim.type.scalar';
    public const ITERABLE = 'claim.type.iterable';
    public const COUNTABLE = 'claim.type.countable';
    public const CALLABLE = 'claim.type.callable';
    public const RESOURCE = 'claim.type.resource';
    public const NULL = 'claim.type.null';

    public const COMPARE = 'claim.type.same';
    public const COMPARE_VALUE = 'claim.type.same.value';
    public const COMPARE_STRICT = 'claim.type.same.strict';
}