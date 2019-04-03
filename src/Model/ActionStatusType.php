<?php declare(strict_types=1);

namespace Wesnick\Workflow\Model;

use MyCLabs\Enum\Enum;

/**
 * The status of an Action.
 *
 * @see http://schema.org/ActionStatusType Documentation on Schema.org
 *
 * @author Wesley O. Nichols <spanishwes@gmail.com>
 */
class ActionStatusType extends Enum
{
    /**
     * @var string a description of an action that is supported
     */
    const POTENTIAL_ACTION_STATUS = 'PotentialActionStatus';

    /**
     * @var string An in-progress action (e.g, while watching the movie, or driving to a location).
     */
    const ACTIVE_ACTION_STATUS = 'ActiveActionStatus';

    /**
     * @var string an action that has already taken place
     */
    const COMPLETED_ACTION_STATUS = 'CompletedActionStatus';

    /**
     * @var string An action that failed to complete. The action's error property and the HTTP return code contain more information about the failure.
     */
    const FAILED_ACTION_STATUS = 'FailedActionStatus';
}
