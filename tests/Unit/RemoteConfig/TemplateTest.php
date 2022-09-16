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

use function array_map;

/**
 * @internal
 */
final class TemplateTest extends UnitTestCase
{
    public function testGetDefaultEtag(): void
    {
        self::assertSame('*', Template::new()->etag());
    }

    public function testDefaultVersionIsNull(): void
    {
        self::assertNull(Template::new()->version());
    }

    public function testCreateWithInvalidConditionalValue(): void
    {
        $parameter = Parameter::named('foo')->withConditionalValue(new ConditionalValue('non_existing_condition', 'false'));

        $this->expectException(InvalidArgumentException::class);
        Template::new()->withParameter($parameter);
    }

    /**
     * @see https://github.com/kreait/firebase-php/issues/218
     */
    public function testConditionNamesAreImportedCorrectlyWhenUsingFromArray(): void
    {
        $given = ['conditions' => [['name' => 'foo', 'expression' => '"true"']]];

        $template = Template::fromArray($given);

        $parameter = Parameter::named('param')->withConditionalValue(ConditionalValue::basedOn('foo'));

        $template = $template->withParameter($parameter);

        $condition = $template->conditions()[0];
        self::assertSame('foo', $condition->name());
        self::assertSame('"true"', $condition->expression());

        self::assertSame('foo', $template->parameters()['param']->conditionalValues()[0]->conditionName());
    }

    public function testWithFluidConfiguration(): void
    {
        $german = Condition::named('lang_german')
            ->withExpression("device.language in ['de', 'de_AT', 'de_CH']")
            ->withTagColor(TagColor::ORANGE);

        $french = Condition::named('lang_french')
            ->withExpression("device.language in ['fr', 'fr_CA', 'fr_CH']")
            ->withTagColor(TagColor::GREEN);

        $germanWelcomeMessage = ConditionalValue::basedOn($german)->withValue('Willkommen!');
        $frenchWelcomeMessage = new ConditionalValue('lang_french', 'Bienvenu!');

        $welcomeMessageParameter = Parameter::named('welcome_message')
            ->withDefaultValue('Welcome!')
            ->withDescription('This is a welcome message')
            ->withConditionalValue($germanWelcomeMessage)
            ->withConditionalValue($frenchWelcomeMessage);

        $uiColors = ParameterGroup::named('ui_colors')
            ->withDescription('Some colors for the UI')
            ->withParameter(Parameter::named('primary_color')->withDefaultValue('blue'))
            ->withParameter(Parameter::named('secondary_color')->withDefaultValue('green'));

        $template = Template::new()
            ->withCondition($german)
            ->withCondition($french)
            ->withParameter($welcomeMessageParameter)
            ->withParameterGroup($uiColors);

        $conditionNames = array_map(static fn (Condition $c) => $c->name(), $template->conditions());

        self::assertContains('lang_german', $conditionNames);
        self::assertContains('lang_french', $conditionNames);
        self::assertSame($welcomeMessageParameter, $template->parameters()['welcome_message']);
        self::assertSame($uiColors, $template->parameterGroups()['ui_colors']);
    }

    public function testParametersCanBeRemoved(): void
    {
        $template = Template::new()
            ->withParameter(Parameter::named('foo'))
            ->withRemovedParameter('foo');

        self::assertCount(0, $template->parameters());
    }

    public function testParameterGroupsCanBeRemoved(): void
    {
        $template = Template::new()
            ->withParameterGroup(ParameterGroup::named('group'))
            ->withRemovedParameterGroup('group');

        self::assertCount(0, $template->parameterGroups());
    }

    public function testPersonalizationValuesAreImportedInDefaultValues(): void
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
        self::assertArrayHasKey('foo', $parameters = $template->parameters());
        self::assertNotNull($parameter = $parameters['foo']);
        self::assertNotNull($defaultValue = $parameter->defaultValue());

        self::assertArrayHasKey('personalizationValue', $array = $defaultValue->toArray());
        self::assertArrayHasKey('personalizationId', $personalizationIdArray = $array['personalizationValue']);
        self::assertSame('id', $personalizationIdArray['personalizationId']);
    }

    public function testPersonalizationValuesAreImportedInConditionalValues(): void
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
        self::assertArrayHasKey('foo', $parameters = $template->parameters());
        self::assertNotNull($parameter = $parameters['foo']);

        $conditionalValues = $parameter->conditionalValues();
        self::assertArrayHasKey(0, $conditionalValues);

        self::assertArrayHasKey('personalizationValue', $array = $conditionalValues[0]->toArray());
        self::assertArrayHasKey('personalizationId', $personalizationIdArray = $array['personalizationValue']);
        self::assertSame('id', $personalizationIdArray['personalizationId']);
    }

    public function testItProvidesConditionNames(): void
    {
        self::assertEquals(
            ['first', 'second', 'third'],
            Template::new()
                ->withCondition(Condition::named('first'))
                ->withCondition(Condition::named('second'))
                ->withCondition(Condition::named('third'))
                ->conditionNames(),
        );
    }

    public function testConditionsCanBeRemoved(): void
    {
        self::assertEquals(
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
