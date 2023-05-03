<?php

declare(strict_types=1);

namespace Takeoto\Rule\Dictionary;

final class ClaimDict
{
    public const TYPE = 'takeoto.claim.type';

    public const TYPE_OBJECT = 'takeoto.claim.type.object';
    public const TYPE_OBJECT_INSTANCE = 'takeoto.claim.type.object.instance';

    public const TYPE_ONE_OF = 'takeoto.claim.type.one-of';
    public const TYPE_ONE_OF_ITEMS = 'takeoto.claim.type.one-of.items';

    public const TYPE_ARRAY = 'takeoto.claim.type.array';
    public const TYPE_ARRAY_STRUCTURE = 'takeoto.claim.type.array.fields';
    public const TYPE_ARRAY_ALLOWED_EXTRA_FIELDS = 'takeoto.claim.type.array.allow-extra';
    public const TYPE_ARRAY_ALLOWED_MISSING_FIELDS = 'takeoto.claim.type.array.missing-extra';
    public const TYPE_ARRAY_OPTIONAL_FIELD = 'takeoto.claim.type.array.optional-field';
    public const TYPE_ARRAY_REQUIRED_FIELD = 'takeoto.claim.type.array.required-field';
    public const TYPE_ARRAY_EACH = 'takeoto.claim.type.array.each';

    public const TYPE_STRING = 'takeoto.claim.type.string';
    public const TYPE_STRING_PATTERN = 'takeoto.claim.type.string.pattern';
    public const TYPE_STRING_PATTERN_EMAIL = 'takeoto.claim.type.string.pattern.email';
    public const TYPE_STRING_PATTERN_JSON = 'takeoto.claim.type.string.pattern.json';
    public const TYPE_STRING_PATTERN_URL = 'takeoto.claim.type.string.pattern.url';
    public const TYPE_STRING_MIN = 'takeoto.claim.type.string.min';
    public const TYPE_STRING_MAX = 'takeoto.claim.type.string.max';

    public const TYPE_CALLBACK = 'takeoto.claim.type.callback';
    public const TYPE_CALLBACK_CLOSURE = 'takeoto.claim.type.callback.closure';

    public const TYPE_INT = 'takeoto.claim.type.int';
    public const TYPE_INT_SOFT = 'takeoto.claim.type.int.soft';
    public const TYPE_INT_MIN = 'takeoto.claim.type.int.min';
    public const TYPE_INT_MAX = 'takeoto.claim.type.int.max';

    public const TYPE_BOOL = 'takeoto.claim.type.bool';
    public const TYPE_FLOAT = 'takeoto.claim.type.float';
    public const TYPE_NUMERIC = 'takeoto.claim.type.numeric';
    public const TYPE_SCALAR = 'takeoto.claim.type.scalar';
    public const TYPE_ITERABLE = 'takeoto.claim.type.iterable';
    public const TYPE_COUNTABLE = 'takeoto.claim.type.countable';
    public const TYPE_CALLABLE = 'takeoto.claim.type.callable';
    public const TYPE_RESOURCE = 'takeoto.claim.type.resource';
    public const TYPE_NULL = 'takeoto.claim.type.null';
}