<?php
if (!file_exists('vendor')) {
  `composer install`;
}
`./vendor/bin/drush make -v ./dkan.make docroot --no-recursion`;

