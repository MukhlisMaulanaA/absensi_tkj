<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use App\Services\TimesheetService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutoCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_it_finalizes_only_overdue_open_attendances_at_the_next_day_0630_cutoff(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 9, 15, 6, 30, 0, 'Asia/Jakarta'));

        $user = $this->makeUser('cutoff-user');
        $overdue = Attendance::create([
            'user_id' => $user->id,
            'check_in_time' => Carbon::create(2026, 9, 14, 20, 0, 0, 'Asia/Jakarta'),
        ]);
        $stillOpen = Attendance::create([
            'user_id' => $user->id,
            'check_in_time' => Carbon::create(2026, 9, 15, 5, 0, 0, 'Asia/Jakarta'),
        ]);
        $manualCheckout = Attendance::create([
            'user_id' => $this->makeUser('manual-user')->id,
            'check_in_time' => Carbon::create(2026, 9, 14, 20, 0, 0, 'Asia/Jakarta'),
            'check_out_time' => Carbon::create(2026, 9, 15, 2, 0, 0, 'Asia/Jakarta'),
            'check_out_source' => 'manual',
        ]);

        $this->artisan('attendance:auto-checkout')->assertSuccessful();

        $this->assertSame('2026-09-15 06:30:00', $overdue->fresh()->check_out_time->format('Y-m-d H:i:s'));
        $this->assertSame('system', $overdue->fresh()->check_out_source);
        $this->assertNull($stillOpen->fresh()->check_out_time);
        $this->assertSame('manual', $manualCheckout->fresh()->check_out_source);
        $this->assertSame('2026-09-15 02:00:00', $manualCheckout->fresh()->check_out_time->format('Y-m-d H:i:s'));

        $timesheet = app(TimesheetService::class)->prepareTimesheetData(
            $user,
            Carbon::create(2026, 9, 14, 0, 0, 0, 'Asia/Jakarta'),
            Carbon::create(2026, 9, 14, 0, 0, 0, 'Asia/Jakarta'),
        );

        $this->assertSame('CHECK-OUT OLEH SISTEM', $timesheet['rows'][0]['keterangan']);
    }

    public function test_it_uses_the_original_0630_deadline_when_recovering_after_scheduler_downtime(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 9, 16, 9, 0, 0, 'Asia/Jakarta'));

        $attendance = Attendance::create([
            'user_id' => $this->makeUser('recovery-user')->id,
            'check_in_time' => Carbon::create(2026, 9, 14, 8, 0, 0, 'Asia/Jakarta'),
        ]);

        $this->artisan('attendance:auto-checkout')->assertSuccessful();

        $this->assertSame('2026-09-15 06:30:00', $attendance->fresh()->check_out_time->format('Y-m-d H:i:s'));
        $this->assertSame('system', $attendance->fresh()->check_out_source);
    }

    private function makeUser(string $username): User
    {
        return User::create([
            'name' => $username,
            'username' => $username,
            'email' => "{$username}@example.test",
            'password' => 'password',
        ]);
    }
}
