<?php

namespace Kreait\Firebase\Auth;

trait UserFields
{
    /**
     * @var Display Name
     */
    protected $displayName;

    /**
     * @var Email
     */
    protected $email;

    /**
     * @var Phone Number
     */
    protected $phoneNumber;

    /**
     * @var Photo URL
     */
    protected $photoURL;

    /**
     * @var Provider Id
     */
    protected $providerId;

    /**
     * @var User ID
     */
    protected $uid;

    public function setDisplayName(string $displayName)
    {
        $this->displayName = $displayName;
    }

    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    public function setPhoneNumber(string $phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    public function setPhotoURL(string $photoURL)
    {
        $this->photoURL = $photoURL;
    }

    public function setProviderId(string $providerId)
    {
        $this->providerId = $providerId;
    }

    public function setUid(string $uid)
    {
        $this->uid = $uid;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->getPhoneNumber;
    }

    /**
     * @return string
     */
    public function getPhotoURL()
    {
        return $this->photoURL;
    }

    /**
     * @return string
     */
    public function getProviderId()
    {
        return $this->providerId;
    }

    /**
     * @return string
     */
    public function getUid()
    {
        return $this->uid;
    }
}
