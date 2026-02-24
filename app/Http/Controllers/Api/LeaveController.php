<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Models\Employee;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LeaveController extends Controller
{
    /**
     * Liste des congés avec filtres
     */
    public function index(Request $request)
    {
        $query = Leave::with(['employee']);

        // Filtre par employé
        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Filtre par type de congé
        if ($request->has('leave_type')) {
            $query->where('leave_type', $request->leave_type);
        }

        // Filtre par statut
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filtre par période
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('start_date', [$request->start_date, $request->end_date]);
        }

        // Filtre par année
        if ($request->has('year')) {
            $query->whereYear('start_date', $request->year);
        }

        $leaves = $query->orderBy('start_date', 'desc')->get();

        return response()->json([
            'data' => $leaves
        ]);
    }

    /**
     * Créer une demande de congé
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'leave_type' => 'required|in:annual,sick,maternity,unpaid,other',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'sometimes|in:pending,approved,rejected,cancelled'
        ]);

        // Par défaut, le statut est "pending"
        $validated['status'] = $validated['status'] ?? 'pending';

        $leave = Leave::create($validated);

        return response()->json([
            'message' => 'Demande de congé créée avec succès',
            'data' => $leave->load('employee')
        ], 201);
    }

    /**
     * Détails d'un congé
     */
    public function show($id)
    {
        $leave = Leave::with(['employee'])->findOrFail($id);

        return response()->json([
            'data' => $leave
        ]);
    }

    /**
     * Mettre à jour un congé
     */
    public function update(Request $request, $id)
    {
        $leave = Leave::findOrFail($id);

        $validated = $request->validate([
            'leave_type' => 'sometimes|in:annual,sick,maternity,unpaid,other',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'status' => 'sometimes|in:pending,approved,rejected,cancelled'
        ]);

        $leave->update($validated);

        return response()->json([
            'message' => 'Congé modifié avec succès',
            'data' => $leave->load('employee')
        ]);
    }

    /**
     * Supprimer un congé
     */
    public function destroy($id)
    {
        $leave = Leave::findOrFail($id);
        $leave->delete();

        return response()->json([
            'message' => 'Congé supprimé avec succès'
        ]);
    }

    /**
     * Approuver un congé
     */
    public function approve($id)
    {
        $leave = Leave::findOrFail($id);

        if ($leave->status !== 'pending') {
            return response()->json([
                'message' => 'Seules les demandes en attente peuvent être approuvées'
            ], 400);
        }

        $leave->update(['status' => 'approved']);

        return response()->json([
            'message' => 'Congé approuvé avec succès',
            'data' => $leave->load('employee')
        ]);
    }

    /**
     * Rejeter un congé
     */
    public function reject($id)
    {
        $leave = Leave::findOrFail($id);

        if ($leave->status !== 'pending') {
            return response()->json([
                'message' => 'Seules les demandes en attente peuvent être rejetées'
            ], 400);
        }

        $leave->update(['status' => 'rejected']);

        return response()->json([
            'message' => 'Congé rejeté',
            'data' => $leave->load('employee')
        ]);
    }

    /**
     * Annuler un congé (par l'employé)
     */
    public function cancel($id)
    {
        $leave = Leave::findOrFail($id);

        if (in_array($leave->status, ['rejected', 'cancelled'])) {
            return response()->json([
                'message' => 'Ce congé ne peut pas être annulé'
            ], 400);
        }

        $leave->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Congé annulé avec succès',
            'data' => $leave->load('employee')
        ]);
    }

    /**
     * Statistiques des congés par employé
     */
    public function statsByEmployee($employee_id, Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);

        $leaves = Leave::where('employee_id', $employee_id)
            ->whereYear('start_date', $year)
            ->get();

        // Calculer le nombre de jours pour chaque congé
        $totalDays = 0;
        foreach ($leaves as $leave) {
            $startDate = Carbon::parse($leave->start_date);
            $endDate = Carbon::parse($leave->end_date);
            $days = $startDate->diffInDays($endDate) + 1; // +1 pour inclure le dernier jour
            $totalDays += $leave->duration_days;
        }

        $stats = [
            'year' => $year,
            'total_leaves' => $leaves->count(),
            'total_days' => $totalDays,
            'by_type' => [
                'annual' => $leaves->where('leave_type', 'annual')->count(),
                'sick' => $leaves->where('leave_type', 'sick')->count(),
                'maternity' => $leaves->where('leave_type', 'maternity')->count(),
                'unpaid' => $leaves->where('leave_type', 'unpaid')->count(),
                'other' => $leaves->where('leave_type', 'other')->count(),
            ],
            'by_status' => [
                'pending' => $leaves->where('status', 'pending')->count(),
                'approved' => $leaves->where('status', 'approved')->count(),
                'rejected' => $leaves->where('status', 'rejected')->count(),
                'cancelled' => $leaves->where('status', 'cancelled')->count(),
            ],
            'days_by_type' => [
                'annual' => $leaves->where('leave_type', 'annual')->sum('duration_days'),
                'sick' => $leaves->where('leave_type', 'sick')->sum('duration_days'),
                'maternity' => $leaves->where('leave_type', 'maternity')->sum('duration_days'),
                'unpaid' => $leaves->where('leave_type', 'unpaid')->sum('duration_days'),
                'other' => $leaves->where('leave_type', 'other')->sum('duration_days'),
            ]
        ];

        return response()->json([
            'employee_id' => $employee_id,
            'stats' => $stats,
            'data' => $leaves
        ]);
    }

    /**
     * Congés en attente (pour validation)
     */
    public function pending()
    {
        $leaves = Leave::with(['employee'])
            ->where('status', 'pending')
            ->orderBy('start_date', 'asc')
            ->get();

        return response()->json([
            'data' => $leaves
        ]);
    }

    /**
     * Calendrier des congés approuvés
     */
    public function calendar(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $leaves = Leave::with(['employee'])
            ->where('status', 'approved')
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function($q) use ($startDate, $endDate) {
                          $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                      });
            })
            ->orderBy('start_date', 'asc')
            ->get();

        return response()->json([
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'data' => $leaves
        ]);
    }
}
