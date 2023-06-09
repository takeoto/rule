# The rule
### Abstraction for data validation rules
#### The value verifier

```php
use Takeoto\Rule\Builder\RuleBuilder;
use Takeoto\Rule\Utility\Claim;
use Takeoto\Rule\Verifier;

$verifier = new Verifier(new RuleBuilder());
$verifier->verify(Claim::int(), 1)->isOk(); # true

$verifier->verify('ABCDE6', Claim::string()->min(1)->max(5))->isOk(); # false
$verifier->verify('ABCDE6', Claim::string()->min(1)->max(5))->getMessages()->getErrors()->first();

$verifier->verify('ABCDE6', Claim::string()->min(1)->max(6))->isOk(); # true
```

#### The rule builder

```php
use Takeoto\Rule\Builder\RuleBuilder;
use Takeoto\Rule\Utility\Claim;

# --- Rule builder

$ruleBuilder = new RuleBuilder();
# Building the rule by claim.
$rule = $ruleBuilder->build(Claim::array([
    'someArrayKey0' => Claim::string()->min(5),
    'someArrayKey1' => Claim::array([
        'someArrayKey1.0' => Claim::int(),
        'someArrayKey1.1' => Claim::bool(),
    ]),
]));
# Verifying the value.
$state = $rule->verify([
    'someArrayKey0' => '01234',
    'someArrayKey1' => [
        'someArrayKey1.0' => 1,
        'someArrayKey1.1' => false,
    ],
]);
$state->isOk(); # true

# Verifying the value.
$state = $rule->verify([
    'someArrayKey1' => [
        'someArrayKey1.0' => 1,
        'someArrayKey1.1' => false,
    ],
]);
$state->isOk(); # false
```

#### Custom rules

```php
use Takeoto\Rule\Builder\RuleBuilder;
use Takeoto\Rule\Claim\RAWClaim;
use Takeoto\Rule\Contract\ClaimInterface;
use Takeoto\Rule\RAWRule;

$ruleBuilder = new RuleBuilder();
# Registering a custom rule,
$ruleBuilder->register(
    'hasWordPikachu',
     RAWRule::new(fn(mixed $v) => is_string($v) && str_contains($v, 'pikachu') ?: 'The word "Pikachu" not found!')
 );
# Creating the rule claim.
$claim = new RAWClaim([
    ClaimDict::CLAIM_TYPE => ,
]);
# Building rule by the claim
$rule = $ruleBuilder->build('hasWordPikachu');

$rule->verify('Hello Mars!')->isOk(); # false
$rule->verify('Hello Pikachu!')->isOk(); # true

# Registering a custom rule,
$ruleBuilder->register(
    'hasWord',
     fn(ClaimInterface $claim) => RAWRule::new(
         fn(mixed $v) => is_string($v) && str_contains($v, $seek = $claim->getAttr('word'))
            ?: sprintf('The word "%s" not found!', $seek)
    )
);
# Creating the rule claim.
$claim = new RAWClaim('hasWord', ['word' => 'Pikachu']);
# Building rule by the claim
$rule = $ruleBuilder->build($claim);

$rule->verify('Hello Mars!')->isOk(); # false
$rule->verify('Hello Pikachu!')->isOk(); # true

```
