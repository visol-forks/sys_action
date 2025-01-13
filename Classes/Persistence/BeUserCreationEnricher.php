<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace TYPO3\CMS\SysAction\Persistence;

use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Fill the "cruser_id" for new sys_action.
 */
class BeUserCreationEnricher
{
    /**
     * @param array $incomingFieldArray
     * @param string $table
     * @param string $id
     */
    public function processDatamap_preProcessFieldArray(&$incomingFieldArray, $table, $id, DataHandler $dataHandler): void
    {
        // Not within sys_action
        if ($table !== 'be_users') {
            return;
        }
        // Existing record, nothing to change
        if (MathUtility::canBeInterpretedAsInteger($id)) {
            return;
        }
        if (isset($incomingFieldArray['cruser_id'])) {
            return;
        }

        $incomingFieldArray['cruser_id'] = $dataHandler->BE_USER->user['uid'] ?? 0;
    }
}
