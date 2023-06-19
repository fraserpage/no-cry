<?php

namespace App\Commands\Concerns;

use Illuminate\Support\Facades\File;

trait GetConfigFile
{
  public function getConfig()
  {
    if (File::exists('.no-cry.json')){
      $config = File::get('.no-cry.json');
      if(!empty($config)){
        return json_decode($config);
      }
    }
    return [];
  }
}