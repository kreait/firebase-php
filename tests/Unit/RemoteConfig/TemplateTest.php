<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\RemoteConfig;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\RemoteConfig\Condition;
use Kreait\Firebase\RemoteConfig\ConditionalValue;
use Kreait\Firebase\RemoteConfig\Parameter;
use Kreait\Firebase\RemoteConfig\ParameterGroup;
use Kreait\Firebase\RemoteConfig\TagColor;
use Kreait\Firebase\RemoteConfig\Template;
use Kreait\Firebase\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\Test;

use function array_map;

/**
 * @internal
 */
final class TemplateTest extends UnitTestCase
{
    #[Test]
    public function getDefaultEtag(): void
    {
        $this->assertSame('*', Template::new()->etag());
    }

    #[Test]
    public function defaultVersionIsNull(): void
    {
        $this->assertNull(Template::new()->version());
    }

    #[Test]
    public function createWithInvalidConditionalValue(): void
    {
        $parameter = Parameter::named('foo')
            ->withConditionalValue(ConditionalValue::basedOn('non_existing_condition'))
        ;

        $this->expectException(InvalidArgumentException::class);
        Template::new()->withParameter($parameter);
    }

    /**
     * @see https://github.com/kreait/firebase-php/issues/218
     */
    #[Test]
    public function conditionNamesAreImportedCorrectlyWhenUsingFromArray(): void
    {
        $given = ['conditions' => [['name' => 'foo', 'expression' => '"true"']]];

        $template = Template::fromArray($given);

        $parameter = Parameter::named('param')->withConditionalValue(ConditionalValue::basedOn('foo'));

        $template = $template->withParameter($parameter);

        $condition = $template->conditions()[0];
        $this->assertSame('foo', $condition->name());
        $this->assertSame('"true"', $condition->expression());

        $this->assertSame('foo', $template->parameters()['param']->conditionalValues()[0]->conditionName());
    }

    #[Test]
    public function withFluidConfiguration(): void
    {
        $german = Condition::named('lang_german')
            ->withExpression("device.language in ['de', 'de_AT', 'de_CH']")
            ->withTagColor(TagColor::ORANGE)
        ;

        $french = Condition::named('lang_french')
            ->withExpression("device.language in ['fr', 'fr_CA', 'fr_CH']")
            ->withTagColor(TagColor::GREEN)
        ;

        $germanWelcomeMessage = ConditionalValue::basedOn($german)->withValue('Willkommen!');
        $frenchWelcomeMessage = ConditionalValue::basedOn($french)->withValue('Bienvenu!');

        $welcomeMessageParameter = Parameter::named('welcome_message')
            ->withDefaultValue('Welcome!')
            ->withDescription('This is a welcome message')
            ->withConditionalValue($germanWelcomeMessage)
            ->withConditionalValue($frenchWelcomeMessage)
        ;

        $uiColors = ParameterGroup::named('ui_colors')
            ->withDescription('Some colors for the UI')
            ->withParameter(Parameter::named('primary_color')->withDefaultValue('blue'))
            ->withParameter(Parameter::named('secondary_color')->withDefaultValue('green'))
        ;

        $template = Template::new()
            ->withCondition($german)
            ->withCondition($french)
            ->withParameter($welcomeMessageParameter)
            ->withParameterGroup($uiColors)
        ;

        $conditionNames = array_map(static fn(Condition $c): string => $c->name(), $template->conditions());

        $this->assertContains('lang_german', $conditionNames);
        $this->assertContains('lang_french', $conditionNames);
        $this->assertSame($welcomeMessageParameter, $template->parameters()['welcome_message']);
        $this->assertSame($uiColors, $template->parameterGroups()['ui_colors']);
    }

    #[Test]
    public function parametersCanBeRemoved(): void
    {
        $template = Template::new()
            ->withParameter(Parameter::named('foo'))
            ->withRemovedParameter('foo')
        ;

        $this->assertCount(0, $template->parameters());
    }

    #[Test]
    public function parameterGroupsCanBeRemoved(): void
    {
        $template = Template::new()
            ->withParameterGroup(ParameterGroup::named('group'))
            ->withRemovedParameterGroup('group')
        ;

        $this->assertCount(0, $template->parameterGroups());
    }

    #[Test]
    public function personalizationValuesAreImportedInDefaultValues(): void
    {
        $data = [
            'parameters' => [
                'foo' => [
                    'defaultValue' => [
                        'personalizationValue' => [
                            'personalizationId' => 'id',
                        ],
                    ],
                ],
            ],
        ];

        $template = Template::fromArray($data);
        $this->assertArrayHasKey('foo', $parameters = $template->parameters());
        $this->assertNotNull($defaultValue = $parameters['foo']->defaultValue());

        $this->assertArrayHasKey('personalizationValue', $array = $defaultValue->toArray());
        $this->assertSame('id', $array['personalizationValue']['personalizationId']);
    }

    #[Test]
    public function personalizationValuesAreImportedInConditionalValues(): void
    {
        $data = [
            'conditions' => [
                [
                    'name' => 'condition',
                    'expression' => "device.language in ['de', 'de_AT', 'de_CH']",
                ],
            ],
            'parameters' => [
                'foo' => [
                    'conditionalValues' => [
                        'condition' => [
                            'personalizationValue' => [
                                'personalizationId' => 'id',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $template = Template::fromArray($data);
        $this->assertArrayHasKey('foo', $parameters = $template->parameters());

        $conditionalValues = $parameters['foo']->conditionalValues();
        $this->assertArrayHasKey(0, $conditionalValues);

        $this->assertArrayHasKey('personalizationValue', $array = $conditionalValues[0]->toArray());
        $this->assertSame('id', $array['personalizationValue']['personalizationId']);
    }

    #[Test]
    public function itProvidesConditionNames(): void
    {
        $this->assertEqualsCanonicalizing(
            ['first', 'second', 'third'],
            Template::new()
                ->withCondition(Condition::named('first'))
                ->withCondition(Condition::named('second'))
                ->withCondition(Condition::named('third'))
                ->conditionNames(),
        );
    }

    #[Test]
    public function conditionsCanBeRemoved(): void
    {
        $this->assertEqualsCanonicalizing(
            ['first', 'third'],
            Template::new()
                ->withCondition(Condition::named('first'))
                ->withCondition(Condition::named('second'))
                ->withCondition(Condition::named('third'))
                ->withRemovedCondition('second')
                ->conditionNames(),
        );
    }
}
