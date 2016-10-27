#! /usr/bin/env bash

# sample package command line

# param $1 should be the tag - e.g. v1.0.1 - which is assumed to be the current repo state

echo mv birdseye birdseye-$1

cat <<ENDTAR
tar --exclude=birdseye-$1/.env               \\
    --exclude='birdseye-$1/birdseye-*.env'   \\
    --exclude='birdseye-$1/.git'             \\
    --exclude='birdseye-$1/.vagrant'         \\
    --exclude='*~'                           \\
    --exclude='*log'                         \\
    -vcjf                                    \\
    birdseye-$1.tar.bz2 birdseye-$1
ENDTAR

echo mv birdseye-$1 birdseye
