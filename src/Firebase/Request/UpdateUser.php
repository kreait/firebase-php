<?php

declare(strict_types=1);

namespace Kreait\Firebase\Request;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Request;
use Kreait\Firebase\Util\JSON;
use Kreait\Firebase\Value\Provider;

final class UpdateUser implements Request
{
    const DISPLAY_NAME = 'DISPLAY_NAME';
    const PHOTO_URL = 'PHOTO_URL';

    use EditUserTrait;

    /**
     * @var array
     */
    private $attributesToDelete = [];

    /**
     * @var Provider[]
     */
    private $providersToDelete = [];

    /**
     * @var array|null
     */
    private $customAttributes;

    private function __construct()
    {
    }

    public static function new(): self
    {
        return new self();
    }

    /**
     * @param array $properties
     *
     * @throws InvalidArgumentException when invalid properties have been provided
     */
    public static function withProperties(array $properties): self
    {
        $request = self::withEditableProperties(new self(), $properties);

        foreach ($properties as $key => $value) {
            switch (\strtolower(\preg_replace('/[^a-z]/i', '', $key))) {
                case 'deletephoto':
                case 'deletephotourl':
                case 'removephoto':
                case 'removephotourl':
                    $request = $request->withRemovedPhotoUrl();
                    break;
                case 'deletedisplayname':
                case 'removedisplayname':
                    $request = $request->withRemovedDisplayName();
                    break;

                case 'deleteattribute':
                case 'deleteattributes':
                    foreach ((array) $value as $deleteAttribute) {
                        switch (\strtolower(\preg_replace('/[^a-z]/i', '', $deleteAttribute))) {
                            case 'displayname':
                                $request = $request->withRemovedDisplayName();
                                break;
                            case 'photo':
                            case 'photourl':
                                $request = $request->withRemovedPhotoUrl();
                                break;
                        }
                    }
                    break;
                case 'customattributes':
                case 'customclaims':
                    $request = $request->withCustomAttributes($value);
                    break;
                case 'phonenumber':
                case 'phone':
                    if (!$value) {
                        $request = $request->withRemovedPhoneNumber();
                    }
                    break;
                case 'deletephone':
                case 'deletephonenumber':
                case 'removephone':
                case 'removephonenumber':
                    $request = $request->withRemovedPhoneNumber();
                    break;
                case 'deleteprovider':
                case 'deleteproviders':
                case 'removeprovider':
                case 'removeproviders':
                    $request = \array_reduce((array) $value, static function (self $request, $provider) {
                        return $request->withRemovedProvider($provider);
                    }, $request);
                    break;
            }
        }

        return $request;
    }

    public function withRemovedPhoneNumber(): self
    {
        $request = clone $this;
        $request->phoneNumber = null;

        return $request->withRemovedProvider('phone');
    }

    public function withRemovedProvider($provider): self
    {
        $provider = $provider instanceof Provider ? $provider : new Provider($provider);

        $request = clone $this;
        $request->providersToDelete[] = $provider;

        return $request;
    }

    public function withRemovedDisplayName(): self
    {
        $request = clone $this;
        $request->displayName = null;
        $request->attributesToDelete[] = self::DISPLAY_NAME;

        return $request;
    }

    public function withRemovedPhotoUrl(): self
    {
        $request = clone $this;
        $request->photoUrl = null;
        $request->attributesToDelete[] = self::PHOTO_URL;

        return $request;
    }

    public function withCustomAttributes(array $customAttributes): self
    {
        $request = clone $this;
        $request->customAttributes = $customAttributes;

        return $request;
    }

    public function jsonSerialize()
    {
        if (!$this->hasUid()) {
            throw new InvalidArgumentException('A uid is required to update an existing user.');
        }

        $data = $this->prepareJsonSerialize();

        if (\is_array($this->customAttributes)) {
            if (empty($this->customAttributes)) {
                $data['customAttributes'] = '{}';
            } else {
                $data['customAttributes'] = JSON::encode($this->customAttributes);
            }
        }

        if (!empty($this->attributesToDelete)) {
            $data['deleteAttribute'] = \array_unique($this->attributesToDelete);
        }

        if (!empty($this->providersToDelete)) {
            $data['deleteProvider'] = $this->providersToDelete;
        }

        return $data;
    }
}
