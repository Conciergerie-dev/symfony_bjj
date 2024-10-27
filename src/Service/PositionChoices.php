<?php

namespace App\Service;

class PositionChoices
{
    public static function getBasePositions(): array
    {
        return [
            // Top Positions
            'Top Closed Guard' => 'Top Closed Guard',
            'Top Open Guard' => 'Top Open Guard',
            'Top Half Guard' => 'Top Half Guard',
            'Top Knee on Belly' => 'Top Knee on Belly',
            'Top Mount' => 'Top Mount',
            'Top Side Control' => 'Top Side Control',
            'Top North-South' => 'Top North-South',
            'Top Turtle' => 'Top Turtle',
            'Top Reverse De La Riva' => 'Top Reverse De La Riva',
            'Top X-Guard' => 'Top X-Guard',
            'Top 50/50 Guard' => 'Top 50/50 Guard',
            'Top Spider Guard' => 'Top Spider Guard',
            'Top Lasso Guard' => 'Top Lasso Guard',
            'Top Deep Half Guard' => 'Top Deep Half Guard',

            // Bottom Positions
            'Bottom Closed Guard' => 'Bottom Closed Guard',
            'Bottom Open Guard' => 'Bottom Open Guard',
            'Bottom Half Guard' => 'Bottom Half Guard',
            'Bottom De La Riva Guard' => 'Bottom De La Riva Guard',
            'Bottom Reverse De La Riva Guard' => 'Bottom Reverse De La Riva Guard',
            'Bottom Spider Guard' => 'Bottom Spider Guard',
            'Bottom Lasso Guard' => 'Bottom Lasso Guard',
            'Bottom X-Guard' => 'Bottom X-Guard',
            'Bottom 50/50 Guard' => 'Bottom 50/50 Guard',
            'Bottom Deep Half Guard' => 'Bottom Deep Half Guard',
            'Bottom Butterfly Guard' => 'Bottom Butterfly Guard',
            'Bottom Single Leg X-Guard' => 'Bottom Single Leg X-Guard',
            'Bottom Z-Guard (Knee Shield)' => 'Bottom Z-Guard (Knee Shield)',
            'Bottom Mount' => 'Bottom Mount',
            'Bottom Side Control' => 'Bottom Side Control',
            'Bottom North-South' => 'Bottom North-South',
            'Bottom Turtle' => 'Bottom Turtle',

            // Additional Option
            'Other' => 'Other',
        ];
    }

    public static function getEndingPositions(): array
    {
        // Get the base positions
        $basePositions = self::getBasePositions();

        // Add specific ending choices
        $endingChoices = [
            'Submission' => 'Submission',
            'Defense' => 'Defense',
        ];

        // Merge the base positions into the ending choices
        return array_merge($endingChoices, $basePositions);
    }
}
