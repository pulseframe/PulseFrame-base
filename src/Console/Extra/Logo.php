<?php

namespace PulseFrame\Console\Extra;

use PulseFrame\Facades\Config;
use PulseFrame\Facades\Env;
use PulseFrame\Foundation\Application;
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
  |_|       \__,_| |_| |___/  \___| |_|      |_|     \__,_| |_| |_| |_|  \___|" . $reset . " <info>v" . Application::VERSION . "-" . Application::STAGE . "<info>");
    $output->writeln('');
    $output->writeln("<comment>Application:</comment> " . Env::get('app.name') . '-v' . Config::get('app', 'version') . "-" . Config::get('app', 'stage'));
    $output->writeln('<comment>Is app in debug?</comment> ' . (Env::get('app.settings.debug') ? "Yes" : "No"));
    $output->writeln('');
  }
}
