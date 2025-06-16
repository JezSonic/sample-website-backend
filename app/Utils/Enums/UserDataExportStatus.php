<?php

namespace App\Utils\Enums;
enum UserDataExportStatus: string {
    case QUEUED = "queued";
    case PROCESSING = "processing";
    case COMPLETED = "completed";
    case FAILED = "failed";
    case NOT_FOUND = "not_found";
}
