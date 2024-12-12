<?php

declare(strict_types=1);

use Tests\Helper\CustomPerformanceBenchmarker;

require_once 'vendor/autoload.php';

(new CustomPerformanceBenchmarker())->test();
// (new CustomPerformanceBenchmarker())->testDirs();
