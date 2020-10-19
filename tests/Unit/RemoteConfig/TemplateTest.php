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

/**
 * @internal
 */
class TemplateTest extends UnitTestCase
{
    public function testGetDefaultEtag(): void
    {
        $this->assertSame('*', Template::new()->etag());
    }

    public function testDefaultVersionIsNull(): void
    {
        $this->assertNull(Template::new()->version());
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

        $condition = $template->conditions()['foo'];
        $this->assertSame('foo', $condition->name());
        $this->assertSame('"true"', $condition->expression());

        $this->assertSame('foo', $template->parameters()['param']->conditionalValues()[0]->conditionName());
    }

    /**
     * @test
     */
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

        $this->assertSame($german, $template->conditions()['lang_german']);
        $this->assertSame($french, $template->conditions()['lang_french']);
        $this->assertSame($welcomeMessageParameter, $template->parameters()['welcome_message']);
        $this->assertSame($uiColors, $template->parameterGroups()['ui_colors']);
    }
}
