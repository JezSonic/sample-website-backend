<?php

namespace App\Utils\Enums;
/**
 * Enum representing the status of a user data export
 */
enum UserDataExportStatus: string {
    /**
     * Data export is queued for processing
     * @var string
     */
    case QUEUED = "queued";

    /**
     * Data export is currently being processed
     * @var string
     */
    case PROCESSING = "processing";

    /**
     * Data export has been completed successfully
     * @var string
     */
    case COMPLETED = "completed";

    /**
     * Data export has failed
     * @var string
     */
    case FAILED = "failed";

    /**
     * Data export is not found
     * @var string
     */
    case NOT_FOUND = "not_found";
}
