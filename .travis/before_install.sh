#!/usr/bin/env bash
set -ev

if [ "$TRAVIS_SECURE_ENV_VARS" == "true" ]; then
  echo "Decrypting test credentials"
  openssl aes-256-cbc -K $encrypted_8bfcfbcad9c4_key -iv $encrypted_8bfcfbcad9c4_iv -in test_credentials.json.enc -out tests/_fixtures/test_credentials.json -d;
else
  echo "Not decrypting test credentials"
fi
