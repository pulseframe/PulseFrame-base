<?php

namespace PulseFrame\Console\Extra;

use PulseFrame\Facades\Config;
use PulseFrame\Foundation\Application as PulseApplication;
use Symfony\Component\Console\Output\OutputInterface;

class Logo
{
  public function __construct(OutputInterface $output)
  {
    $blue = "\033[1;34m";
    $reset = "\033[0m";

    $output->writeln($blue . "   _____            _                ______                                   
  |  __ \          | |              |  ____|                                  
  | |__) |  _   _  | |  ___    ___  | |__     _ __    __ _   _ __ ___     ___ 
  |  ___/  | | | | | | / __|  / _ \ |  __|   | '__|  / _` | | '_ ` _ \   / _ \
  | |      | |_| | | | \__ \ |  __/ | |      | |    | (_| | | | | | | | |  __/
  |_|       \__,_| |_| |___/  \___| |_|      |_|     \__,_| |_| |_| |_|  \___|" . $reset . " <info>v" . PulseApplication::$VERSION . "-" . Config::get('app', 'stage') . "<info>");
    $output->writeln("");
  }
}

