<?php

namespace Kreait\Firebase\Auth;

interface UserInfo
{
    public function setDisplayName(string $displayName);

    public function setEmail(string $email);

    public function setPhoneNumber(string $phoneNumber);

    public function setPhotoURL(string $photoURL);

    public function setProviderId(string $providerId);

    public function setUid(string $uid);

    /**
     * @return string
     */
    public function getDisplayName();

    /**
     * @return string
     */
    public function getEmail();

    /**
     * @return string
     */
    public function getPhoneNumber();

    /**
     * @return string
     */
    public function getPhotoURL();

    /**
     * @return string
     */
    public function getProviderId();

    /**
     * @return string
     */
    public function getUid();
}
