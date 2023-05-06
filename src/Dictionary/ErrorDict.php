<?php

declare(strict_types=1);

namespace Takeoto\Rule\Dictionary;

class ErrorDict
{

    public const NOT_SAME = 'rule.error.not-same';
    public const NOT_ONE_OF = 'rule.error.not-one-of';

    public const NOT_INT = 'rule.error.not-int';
    public const NOT_INT_LESS_OR_EQ = 'rule.error.int.less-or-eq';
    public const NOT_INT_MORE_OR_EQ = 'rule.error.int.more-or-eq';

    public const NOT_BOOL = 'rule.error.not-bool';

    public const NOT_STRING = 'rule.error.not-string';
    public const NOT_STRING_REGEX = 'rule.error.string.not-regex';
    public const NOT_STRING_LENGTH_LESS_OR_EQ = 'rule.error.string.less-or-eq';
    public const NOT_STRING_LENGTH_MORE_OR_EQ = 'rule.error.string.more-or-eq';

    public const NOT_OBJECT = 'rule.error.not-object';
    public const NOT_OBJECT_INSTANCE_OF = 'rule.error.object.not-instanceOf';

    public const NOT_ARRAY = 'rule.error.not-array';
    public const ARRAY_KEY_MISSING = 'rule.error.array.key-missing';
    public const ARRAY_KEY_EXTRA = 'rule.error.array.key-extra';

    public const NOT_NULL = 'rule.error.not-null';
    public const NOT_FLOAT = 'rule.error.not-float';
    public const NOT_NUMERIC = 'rule.error.not-numeric';
    public const NOT_SCALAR = 'rule.error.not-scalar';
    public const NOT_ITERABLE = 'rule.error.not-iterable';
    public const NOT_COUNTABLE = 'rule.error.not-countable';
    public const NOT_CALLABLE = 'rule.error.not-callable';
    public const NOT_RESOURCE = 'rule.error.not-resource';
}