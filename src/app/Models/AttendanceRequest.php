<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'requested_clock_in',
        'requested_clock_out',
        'reason',
        'status',
    ];

    /**
     * この勤怠修正申請を行ったユーザーを取得する。
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * この勤怠修正申請の対象となる勤怠情報を取得する。
     *
     * @return BelongsTo
     */
    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    /**
     * この勤怠修正申請に紐づく休憩時間の修正内容を取得する。
     *
     * @return HasMany
     */
    public function attendanceRequestBreaks(): HasMany
    {
        return $this->hasMany(AttendanceRequestBreak::class);
    }
}