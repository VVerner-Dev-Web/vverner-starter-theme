<?php defined('ABSPATH') || exit;

function isVVernerUser(): bool
{
  $data = get_userdata(get_current_user_id());
  return $data && str_contains((string) $data->user_email, 'vverner');
}

function vvernerThemeInDev(): bool
{
  return 'DEV' === vvernerThemeEnv();
}

function vvernerThemeEnv(): string
{
  $url        = home_url();
  $currentEnv = 'PRD';
  $knownPaths = [
    '.dev'          => 'DEV',
    '-sandbox.com'  => 'DEV',
    'kinsta.cloud'  => 'DEV'
  ];

  foreach ($knownPaths as $path => $env) :
    if (strpos((string) $url, $path)) :
      $currentEnv = $env;
      break;
    endif;
  endforeach;

  return $currentEnv;
}
