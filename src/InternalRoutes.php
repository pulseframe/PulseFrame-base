<?php

use PulseFrame\Facades\Route;
use PulseFrame\Http\Controllers\MaintenanceController;

return function () {
  Route::get('/{uuid}', [MaintenanceController::class, 'index']);
};
