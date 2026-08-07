<?php

namespace App\Enums;

enum OpportunityEngagementStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Replied = 'replied';
    case MeetingScheduled = 'meeting_scheduled';
    case Won = 'won';
    case Lost = 'lost';
}
