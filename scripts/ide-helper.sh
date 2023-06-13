#!/bin/bash

set -e

if [ $# -lt 1 ]; then
  echo "Usage: ide-helper ds_account_name ..."
  exit 1
fi

DIR="$(git rev-parse --show-toplevel)"

$DIR/shartisan "$1" ide-helper:generate
$DIR/shartisan "$1" ide-helper:meta
$DIR/shartisan "$1" ide-helper:models --nowrite

if [ ! -L "$DIR/.phan/stubs/_ide_helper.php" ]; then
    ln -s "$DIR/_ide_helper.php" "$DIR/.phan/stubs/_ide_helper.php"
fi
