<?php 

#1>> /dev/null 2>&1

require __DIR__ . '/Built/config.php';
require _VENDOR_DIR . 'autoload.php';
require __DIR__ . '/legacy.php';


use App\Domains\Dashboard\View as DashboardView;
use GO\Scheduler;

// Create a new scheduler
/*$scheduler = new Scheduler();

$scheduler->call(
    function () {
        DashboardView::createBreakdownRevenueTable();
        return true;
    },
    [],
    'createBreakdownRevenueTable')->daily();

$scheduler->run();*/