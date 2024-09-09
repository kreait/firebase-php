<?php

declare(strict_types=1);

namespace Kreait\Firebase\Database;

use JsonSerializable;

use function array_key_exists;

class RuleSet implements JsonSerializable
{
    /**
     * @var array<string, array<mixed>>
     */
    private readonly array $rules;

    /**
     * @param array<string, array<mixed>> $rules
     */
    private function __construct(array $rules)
    {
        if (!array_key_exists('rules', $rules)) {
            $rules = ['rules' => $rules];
        }

        $this->rules = $rules;
    }

    /**
     * The default rules require Authentication. They allow full read and write access
     * to authenticated users of your app. They are useful if you want data open to
     * all users of your app but don't want it open to the world.
     *
     * @see https://firebase.google.com/docs/database/security/quickstart#sample-rules
     */
    public static function default(): self
    {
        return new self([
            'rules' => [
                '.read' => 'auth != null',
                '.write' => 'auth != null',
            ],
        ]);
    }

    /**
     * During development, you can use the public rules in place of the default rules to set
     * your files publicly readable and writable. This can be useful for prototyping,
     * as you can get started without setting up Authentication.
     *
     * This level of access means anyone can read or write to your database. You should
     * configure more secure rules before launching your app.
     *
     * @see https://firebase.google.com/docs/database/security/quickstart#sample-rules
     */
    public static function public(): self
    {
        return new self([
            'rules' => [
                '.read' => true,
                '.write' => true,
            ],
        ]);
    }

    /**
     * Private rules disable read and write access to your database by users. With these rules,
     * you can only access the database through the Firebase console and an Admin SDK.
     *
     * @see https://firebase.google.com/docs/database/security/quickstart#sample-rules
     */
    public static function private(): self
    {
        return new self([
            'rules' => [
                '.read' => false,
                '.write' => false,
            ],
        ]);
    }

    /**
     * @param array<string, array<mixed>> $rules
     */
    public static function fromArray(array $rules): self
    {
        return new self($rules);
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    public function jsonSerialize(): array
    {
        return $this->rules;
    }
}
