<?php

namespace PulseFrame\Http\Controllers;

class MaintenanceController {
  public function index($uuid)
  {
    if (file_exists($_ENV['storage_path'] . '/framework/maintenance.flag')) {
      if (!preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $uuid)) {
        return json_encode(["status" => "error", "message" => 'Invalid UUID format: ' . $uuid]);
      }    
  
      $_SESSION['maintenanceUUID'] = $uuid;

      echo "<script>
        setTimeout(function() {
          window.location = '/';
        }, 500);
      </script>";
      return json_encode(["status" => "success", "message" => 'UUID successfully set: ' . $uuid]);
    } else {
      echo "<script>window.location='/'</script>";
    }
  }
}