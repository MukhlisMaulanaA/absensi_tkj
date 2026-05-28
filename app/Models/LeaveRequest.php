<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Carbon\CarbonPeriod;
use Carbon\Carbon;

class LeaveRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'type',
        'start_date',
        'end_date',
        'reason',
        'attachment',
        'status',
        'approved_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Approve the leave request and create/update attendance records
     *
     * @param int $approverId
     * @throws \Exception
     * @return $this
     */
    public function approveBy(int $approverId)
    {
        if ($this->isApproved()) {
            return $this;
        }

        DB::transaction(function () use ($approverId) {
            $period = CarbonPeriod::create($this->start_date, $this->end_date);

            // Validate no existing 'present' records inside the period
            foreach ($period as $date) {
                $existsPresent = Attendance::where('user_id', $this->user_id)
                    ->whereDate('check_in_time', $date->toDateString())
                    ->where('status', 'present')
                    ->exists();

                if ($existsPresent) {
                    throw new \Exception('Cannot approve leave: present attendance exists on ' . $date->toDateString());
                }
            }

            // Create or update attendance per day
            foreach ($period as $date) {
                $attendance = Attendance::where('user_id', $this->user_id)
                    ->whereDate('check_in_time', $date->toDateString())
                    ->first();

                $attrs = [
                    'status' => $this->type,
                    'reason' => $this->reason,
                    'late_minutes' => 0,
                    'check_in_time' => Carbon::parse($date)->startOfDay(),
                    'check_out_time' => null,
                    'check_in_photo' => null,
                    'check_out_photo' => null,
                ];

                if ($attendance) {
                    $attendance->update($attrs);
                } else {
                    Attendance::create(array_merge([
                        'user_id' => $this->user_id,
                        'location_id' => null,
                        'is_within_radius' => true,
                    ], $attrs));
                }
            }

            $this->status = 'approved';
            $this->approved_by = $approverId;
            $this->save();
        });

        return $this;
    }
}
