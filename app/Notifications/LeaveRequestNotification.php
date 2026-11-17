<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\LeaveRequest;

class LeaveRequestNotification extends AbstractBaseNotification
{
    protected LeaveRequest $leaveRequest;

    public function __construct(LeaveRequest $leaveRequest, string $title, string $body)
    {
        parent::__construct($title, $body);
        $this->leaveRequest = $leaveRequest;
        $this->data = [
            'leave_request_id' => $leaveRequest->id,
            'employee_id' => $leaveRequest->employee_id,
            'start_date' => $leaveRequest->start_date->toDateString(),
            'end_date' => $leaveRequest->end_date->toDateString(),
            'total_days' => $leaveRequest->total_days,
            'status' => $leaveRequest->status,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return array_merge(parent::toArray($notifiable), [
            'type' => 'leave_request',
            'leave_request' => $this->leaveRequest,
        ]);
    }
}
