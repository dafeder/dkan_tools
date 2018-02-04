<?php

if (isset($argv[1])) {
  $dkan_version = $argv[1];
}
else {
  throw new \Exception("The first argument should be the dkan version we are packaging");
}

$file_name = "{$dkan_version}.zip";

if (!file_exists($file_name)) {
  `wget -O {$file_name} https://github.com/GetDKAN/dkan/archive/{$file_name}`;
}
else {
  print_r("Already got the file {$file_name}" . PHP_EOL);
}

$folder_name = "dkan-{$dkan_version}";
if (!file_exists($folder_name)) {
  print_r("Extracting {$file_name}" . PHP_EOL);
  `unzip {$file_name}`;
  print_r("{$file_name} was extracted" . PHP_EOL);
}
else {
  print_r("The file {$file_name} has already been extracted to {$folder_name}" . PHP_EOL);
}

$readme = file_get_contents("{$folder_name}/README.md");
if (substr_count($readme, $dkan_version) == 0) {
  print_r("Adding version to README file" . PHP_EOL);
  $new_readme = str_replace("# DKAN Open Data Platform", "# DKAN Open Data Platform ({$dkan_version})", $readme);
  file_put_contents("{$folder_name}/README.md", $new_readme);
  print_r("Added version to README file" . PHP_EOL);
}
else {
  print_r("Version has already been added to the README file" . PHP_EOL);
}

$files = get_all_files_with_extension($folder_name, "info");
foreach ($files as $file) {
  add_version_to_info_file($file, $dkan_version);
}

$tar_file_name = "{$dkan_version}.tar.gz";
if (!file_exists($tar_file_name)) {
  print_r("Compressing {$folder_name}" . PHP_EOL);
  `zip -9 -r {$file_name} {$folder_name}`;
  `tar -zcvf {$tar_file_name} {$folder_name}`;
  print_r("{$folder_name} zip and tar.gz archives were created" . PHP_EOL);
}
else {
  print_r("{$folder_name} has already been compressed");
}

function add_version_to_info_file($path, $version) {
  $content = file_get_contents($path);
  if (substr_count($content, "version") == 0) {
    print_r("Adding version number to {$path}" . PHP_EOL);
    $content = trim($content);
    $content .= PHP_EOL . "version = {$version}" . PHP_EOL;
    file_put_contents($path, $content);
    print_r("Adding version number to {$path}" . PHP_EOL);

  }
  else {
    print_r("Version number already added to {$path}" . PHP_EOL);
  }
}

function get_all_files_with_extension($path, $ext) {
  $files_with_extension = [];
  $subs = get_all_subdirectories($path);
  foreach ($subs as $sub) {
    $files = get_files_with_extension($sub, $ext);
    $files_with_extension = array_merge($files_with_extension, $files);
  }
  return $files_with_extension;
}

function get_files_with_extension($path, $ext) {
  $files_with_extension = [];
  $files = get_files($path);
  foreach ($files as $file) {
    $e = pathinfo($file, PATHINFO_EXTENSION);
    if ($ext == $e) {
      $files_with_extension[] = $file;
    }
  }
  return $files_with_extension;
}

function get_all_subdirectories($path) {
  $all_subs = [];
  $stack = [$path];
  while (!empty($stack)) {
    $sub = array_shift($stack);
    $all_subs[] = $sub;
    $subs = get_subdirectories($sub);
    $stack = array_merge($stack, $subs);
  }
  return $all_subs;
}


function get_subdirectories($path) {
  $directories_info = shell_table_to_array(`ls {$path} -lha | grep '^dr'`);
  $subs = [];
  foreach ($directories_info as $di) {
    if (isset($di[8])) {
      $dir = trim($di[8]);
      if ($dir != "." && $dir != "..") {
        $subs[] = "{$path}/{$dir}";
      }
    }
  }
  return $subs;
}

function get_files($path) {
  $files_info = shell_table_to_array(`ls {$path} -lha | grep -v '^dr'`);
  $files = [];
  foreach ($files_info as $fi) {
    if (isset($fi[8])) {
      $file = trim($fi[8]);
      $files[] = "{$path}/{$file}";
    }
  }
  return $files;
}


function shell_table_to_array($shell_table) {
  $final = [];
  $lines = explode(PHP_EOL, $shell_table);

  foreach ($lines as $line) {
    $parts = preg_split('/\s+/', $line);
    if (!empty($parts)) {
      $final[] = $parts;
    }
  }

  return $final;
}


