<?php

namespace App\Policies;

class HandoverPaperPolicy extends SnipePermissionsPolicy
{
    protected function columnName()
    {
        return 'handoverpaper';
    }
}