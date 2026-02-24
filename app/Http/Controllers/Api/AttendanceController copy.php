<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Carbon\Carbon;


class AttendanceController extends Controller
{
    /**
     * Liste des présences avec filtres
     */
    public function index(Request $request)
    {
        $query = Attendance::with(['employee']);

        // Filtre par employé
        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Filtre par date
        if ($request->has('date')) {
            $query->whereDate('date', $request->date);
        }

        // Filtre par période (date début et fin)
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        // Filtre par statut
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $attendances = $query->orderBy('date', 'desc')->get();

        return response()->json([
            'data' => $attendances
        ]);
    }

    /**
     * Enregistrer une présence (check-in/check-out)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'date' => 'required|date',
            'check_in' => 'required|date_format:H:i:s',
            'check_out' => 'nullable|date_format:H:i:s',
            'status' => 'required|in:Present,Absent,Late,Half-day,Remote'
        ]);

        // Vérifier si une présence existe déjà pour cet employé à cette date
        $existingAttendance = Attendance::where('employee_id', $validated['employee_id'])
            ->whereDate('date', $validated['date'])
            ->first();

        if ($existingAttendance) {
            return response()->json([
                'message' => 'Une présence existe déjà pour cet employé à cette date'
            ], 409);
        }

        $attendance = Attendance::create($validated);

        return response()->json([
            'message' => 'Présence enregistrée avec succès',
            'data' => $attendance->load('employee')
        ], 201);
    }

    /**
     * Détails d'une présence
     */
    public function show($id)
    {
        $attendance = Attendance::with(['employee'])->findOrFail($id);

        return response()->json([
            'data' => $attendance
        ]);
    }

    /**
     * Mettre à jour une présence (ex: ajouter check-out)
     */
    public function update(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $validated = $request->validate([
            'check_in' => 'sometimes|date_format:H:i:s',
            'check_out' => 'nullable|date_format:H:i:s',
            'status' => 'sometimes|in:Present,Absent,Late,Half-day,Remote'
        ]);

        $attendance->update($validated);

        return response()->json([
            'message' => 'Présence modifiée avec succès',
            'data' => $attendance->load('employee')
        ]);
    }

    /**
     * Supprimer une présence
     */
    public function destroy($id)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->delete();

        return response()->json([
            'message' => 'Présence supprimée avec succès'
        ]);
    }

    /**
     * Check-in rapide (pointage d'arrivée)
     */
    public function checkIn(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,employee_id'
        ]);

        $today = Carbon::today()->toDateString();
        $now = Carbon::now()->toTimeString();

        // Vérifier si déjà pointé aujourd'hui
        $existingAttendance = Attendance::where('employee_id', $validated['employee_id'])
            ->whereDate('date', $today)
            ->first();

        if ($existingAttendance) {
            return response()->json([
                'message' => 'Vous avez déjà pointé aujourd\'hui',
                'data' => $existingAttendance
            ], 409);
        }

        // Déterminer le statut (Late si après 9h00)
        $checkInTime = Carbon::parse($now);
        $lateThreshold = Carbon::parse('09:00:00');
        $status = $checkInTime->greaterThan($lateThreshold) ? 'Late' : 'Present';

        $attendance = Attendance::create([
            'employee_id' => $validated['employee_id'],
            'date' => $today,
            'check_in' => $now,
            'check_out' => null,
            'status' => $status
        ]);

        return response()->json([
            'message' => 'Pointage d\'arrivée enregistré avec succès',
            'data' => $attendance->load('employee')
        ], 201);
    }

    /**
     * Check-out rapide (pointage de départ)
     */
    public function checkOut(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,employee_id'
        ]);

        $today = Carbon::today()->toDateString();
        $now = Carbon::now()->toTimeString();

        $attendance = Attendance::where('employee_id', $validated['employee_id'])
            ->whereDate('date', $today)
            ->first();

        if (!$attendance) {
            return response()->json([
                'message' => 'Aucun pointage d\'arrivée trouvé pour aujourd\'hui'
            ], 404);
        }

        if ($attendance->check_out) {
            return response()->json([
                'message' => 'Vous avez déjà pointé votre départ',
                'data' => $attendance
            ], 409);
        }

        $attendance->update([
            'check_out' => $now
        ]);

        return response()->json([
            'message' => 'Pointage de départ enregistré avec succès',
            'data' => $attendance->load('employee')
        ]);
    }

    /**
     * Rapport de présence par employé
     */
    public function reportByEmployee($employee_id, Request $request)
    {
        $query = Attendance::where('employee_id', $employee_id);

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        $attendances = $query->orderBy('date', 'desc')->get();

        $stats = [
            'total' => $attendances->count(),
            'present' => $attendances->where('status', 'Present')->count(),
            'late' => $attendances->where('status', 'Late')->count(),
            'absent' => $attendances->where('status', 'Absent')->count(),
            'half_day' => $attendances->where('status', 'Half-day')->count(),
            'remote' => $attendances->where('status', 'Remote')->count(),
        ];

        return response()->json([
            'employee_id' => $employee_id,
            'stats' => $stats,
            'data' => $attendances
        ]);
    }
}
