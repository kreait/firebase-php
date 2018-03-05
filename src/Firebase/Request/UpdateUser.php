<?php

declare(strict_types=1);

namespace Kreait\Firebase\Request;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Request;

final class UpdateUser implements Request
{
    const DISPLAY_NAME = 'DISPLAY_NAME';
    const PHOTO_URL = 'PHOTO_URL';

    use EditUserTrait;

    /**
     * @var array
     */
    private $attributesToDelete = [];

    private function __construct()
    {
    }

    public static function new(): self
    {
        return new self();
    }

    /**
     * @throws InvalidArgumentException when invalid properties have been provided
     */
    public static function withProperties(array $properties): self
    {
        $request = self::withEditableProperties(new self(), $properties);

        foreach ($properties as $key => $value) {
            switch (strtolower(preg_replace('/[^a-z]/i', '', $key))) {
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
                        switch (strtolower(preg_replace('/[^a-z]/i', '', $deleteAttribute))) {
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
            }
        }

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

    public function jsonSerialize()
    {
        if (!$this->hasUid()) {
            throw new InvalidArgumentException('A uid is required to update an existing user.');
        }

        $data = $this->prepareJsonSerialize();

        if (\count($this->attributesToDelete)) {
            $data['deleteAttribute'] = array_unique($this->attributesToDelete);
        }

        return $data;
    }
}
