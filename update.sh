#!/usr/bin/env bash

COMMIT_MESSAGE=$(php bin/easy-platinums games:fetch caro3c-gabber9,Fluttezuhher,chris_play1,IBadDriverI,roots52,The-Ricksterr,Hakoom,ikemenzi)

if [[ -z "$COMMIT_MESSAGE" ]]
then
  echo "No new games"
  exit 0
fi

php bin/easy-platinums files:update

git add .
git status
git diff --staged --quiet || git commit -m"$COMMIT_MESSAGE"
git push