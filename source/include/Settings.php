<?php

class Settings {
  public $schedule;

  function __construct() {
    // If receive schedule changes
    if (isset($_POST["schedule"])) {
      self::saveSchedule(@constant("Schedule::".$_POST["schedule"]));
    }

    $this->schedule = self::getSchedule();
  }

  private static function getSchedule() {
    // Reads the cron file and extract the schedule using regex
    preg_match(Plugin::CRON_REGEX, @file_get_contents(Plugin::CRON_FILE), $matches);
    $schedule = Schedule::reflection($matches[1]);

    // If not found or invalid force disable to ensure safefy
    if (!$schedule) {
      self::saveSchedule(null);
    }

    return $schedule;
  }

  private static function saveSchedule($schedule) {
    $fileContents = "";

    if ($schedule) {
      $fileContents .= "# Generated by un.recyclarr".PHP_EOL;
      $fileContents .= $schedule." ".Plugin::CRON_COMMAND;
    }

    // Override the cron file
    file_put_contents(Plugin::CRON_FILE, $fileContents);

    // Reloads to /etc/cron.d/root file
    exec("/usr/local/sbin/update_cron");
  }
}
