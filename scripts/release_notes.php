<?php
if (isset($argv[1])) {
  $previous_version = $argv[1];
}
else {
  throw new \Exception("The first argument should be the previous DKAN version");
}

print_r($previous_version . PHP_EOL);

if (!file_exists("dkan")) {
  `git clone git@github.com:GetDKAN/dkan.git`;
}
else {
  print_r("Got a clone of the repo." . PHP_EOL);
}
$output = NULL;
exec("cd dkan && git log --pretty=oneline {$previous_version}...HEAD",$output);

foreach ($output as $line) {
  $pieces = explode(" ", $line);
  unset($pieces[0]);
  $new = implode(" ", $pieces);
  print_r(" - {$new}" . PHP_EOL);
}

foreach ($output as $line) {
  $new = str_replace(["(", ")"], "", $line);
  $pieces = explode(" ", $new);
  unset($pieces[0]);
  $new = implode(" ", $pieces);
  $pieces = explode("#", $new);
  if (isset($pieces[1])) {
    print_r(" - #{$pieces[1]} {$pieces[0]}" . PHP_EOL);
  }
  else {
    print_r(" - {$pieces[0]}" . PHP_EOL);
  }
}
