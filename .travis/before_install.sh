#!/usr/bin/env bash
set -evx

if [ "$TRAVIS_SECURE_ENV_VARS" == "true" ]; then
  echo "Decrypting test credentials"
  openssl aes-256-cbc -K $encrypted_8bfcfbcad9c4_key -iv $encrypted_8bfcfbcad9c4_iv -in test_credentials.json.enc -out tests/_fixtures/test_credentials.json -d;
  echo "Decrypting test devices"
  openssl aes-256-cbc -K $encrypted_d51fa485ef84_key -iv $encrypted_d51fa485ef84_iv -in test_devices.json.enc -out tests/_fixtures/test_devices.json -d;
else
  echo "Not decrypting test credentials or test devices"
fi

