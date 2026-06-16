<?php

namespace App\SupportDesk\Model;

enum TicketStatus: string
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case Closed = 'closed';
}
