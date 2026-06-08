<?php

namespace App\Http\Controllers;

use App\Models\StudySchedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $schedules = StudySchedule::where('user_id', $request->user()->id)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
        return response()->json($schedules);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'day_of_week' => 'required|integer|min:1|max:7',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
        ]);

        $schedule = StudySchedule::create([
            'user_id'     => $request->user()->id,
            'title'       => $request->title,
            'description' => $request->description,
            'day_of_week' => $request->day_of_week,
            'start_time'  => $request->start_time,
            'end_time'    => $request->end_time,
            'is_active'   => $request->is_active ?? true,
        ]);

        return response()->json($schedule, 201);
    }

    public function update(Request $request, $id)
    {
        $schedule = StudySchedule::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $request->validate([
            'title'       => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'day_of_week' => 'sometimes|required|integer|min:1|max:7',
            'start_time'  => 'sometimes|required|date_format:H:i',
            'end_time'    => 'sometimes|required|date_format:H:i',
            'is_active'   => 'sometimes|boolean',
        ]);

        $schedule->update($request->only([
            'title', 'description', 'day_of_week',
            'start_time', 'end_time', 'is_active',
        ]));

        return response()->json($schedule);
    }

    public function destroy(Request $request, $id)
    {
        $schedule = StudySchedule::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $schedule->delete();
        return response()->json(['message' => 'Jadwal berhasil dihapus']);
    }

    public function toggle(Request $request, $id)
    {
        $schedule = StudySchedule::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $schedule->update(['is_active' => $request->is_active]);
        return response()->json($schedule);
    }
}