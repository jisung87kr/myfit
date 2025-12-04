<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WeightLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WeightLogController extends Controller
{
    /**
     * Get weight log for a specific date
     */
    public function show(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->error('Validation failed', $validator->errors(), 422);
        }

        $weightLog = WeightLog::forUser(auth()->id())
            ->forDate($request->date)
            ->first();

        if (!$weightLog) {
            return response()->error('No weight log found for this date', null, 404);
        }

        return response()->success('Weight log retrieved successfully', $weightLog);
    }

    /**
     * Store a new weight log
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'weight' => 'required|numeric|min:20|max:300',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->error('Validation failed', $validator->errors(), 422);
        }

        // Check if entry already exists for this date
        $existingLog = WeightLog::forUser(auth()->id())
            ->forDate($request->date)
            ->first();

        if ($existingLog) {
            return response()->error(
                'Weight log already exists for this date. Use update endpoint to modify.',
                null,
                409
            );
        }

        $data = $validator->validated();
        $data['user_id'] = auth()->id();

        $weightLog = WeightLog::create($data);

        // Get previous weight to calculate change
        $previousLog = WeightLog::forUser(auth()->id())
            ->where('date', '<', $request->date)
            ->orderBy('date', 'desc')
            ->first();

        $response = $weightLog->toArray();

        if ($previousLog) {
            $response['weight_change'] = round($weightLog->weight - $previousLog->weight, 2);
            $response['previous_weight'] = $previousLog->weight;
            $response['previous_date'] = $previousLog->date->format('Y-m-d');
        }

        return response()->success('Weight logged successfully', $response, 201);
    }

    /**
     * Update a weight log
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $weightLog = WeightLog::find($id);

        if (!$weightLog) {
            return response()->notFound('Weight log not found');
        }

        // Check authorization
        if ($weightLog->user_id !== auth()->id()) {
            return response()->forbidden('You do not have access to this weight log');
        }

        $validator = Validator::make($request->all(), [
            'weight' => 'sometimes|required|numeric|min:20|max:300',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->error('Validation failed', $validator->errors(), 422);
        }

        $weightLog->update($validator->validated());

        return response()->success('Weight log updated successfully', $weightLog);
    }

    /**
     * Delete a weight log
     */
    public function destroy(int $id): JsonResponse
    {
        $weightLog = WeightLog::find($id);

        if (!$weightLog) {
            return response()->notFound('Weight log not found');
        }

        // Check authorization
        if ($weightLog->user_id !== auth()->id()) {
            return response()->forbidden('You do not have access to this weight log');
        }

        $weightLog->delete();

        return response()->success('Weight log deleted successfully');
    }

    /**
     * Get latest weight
     */
    public function latest(): JsonResponse
    {
        $latestLog = WeightLog::getLatestWeight(auth()->id());

        if (!$latestLog) {
            return response()->error('No weight logs found', null, 404);
        }

        return response()->success('Latest weight retrieved successfully', $latestLog);
    }

    /**
     * Get weight history
     */
    public function history(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'days' => 'nullable|integer|min:1|max:365',
        ]);

        if ($validator->fails()) {
            return response()->error('Validation failed', $validator->errors(), 422);
        }

        $days = $request->days ?? 30;

        $history = WeightLog::getHistory(auth()->id(), $days);

        return response()->success('Weight history retrieved successfully', [
            'period_days' => $days,
            'entry_count' => $history->count(),
            'weights' => $history,
        ]);
    }

    /**
     * Get weight progress
     */
    public function progress(): JsonResponse
    {
        $progress = WeightLog::getProgress(auth()->id());

        if (!$progress['has_data']) {
            return response()->error('No weight logs found', null, 404);
        }

        return response()->success('Weight progress retrieved successfully', $progress);
    }

    /**
     * Get statistics for a date range
     */
    public function statistics(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->error('Validation failed', $validator->errors(), 422);
        }

        $statistics = WeightLog::getStatistics(
            auth()->id(),
            $request->start_date,
            $request->end_date
        );

        if (!$statistics['has_data']) {
            return response()->error('No weight logs found for the specified period', null, 404);
        }

        return response()->success('Weight statistics retrieved successfully', $statistics);
    }
}
