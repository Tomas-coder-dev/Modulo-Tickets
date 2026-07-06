<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketStatusHistory;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TicketController extends Controller
{
    public function index(): Response
    {
        $tickets = Ticket::with([
                'user',
                'assignedTo',
                'statusHistories' => fn ($q) => $q->orderByDesc('created_at')->with('changedBy'),
            ])
            ->withCount(['statusHistories as comments_count' => function ($query) {
                $query->whereNotNull('comment')->where('comment', '!=', '');
            }])
            ->latest()
            ->get();

        $board = collect(Ticket::STATUSES)->map(function (string $label, string $status) use ($tickets) {
            return [
                'status' => $status,
                'label' => $label,
                'tickets' => $tickets->where('status', $status)->values(),
            ];
        })->values();

        return Inertia::render('Tickets/Board', [
            'board' => $board,
            'tiUsers' => User::where('role', 'ti')->get(['id', 'name']),
        ]);
    }

    public function myTickets(): Response
    {
        $tickets = Ticket::with([
                'user',
                'assignedTo',
                'statusHistories' => fn ($q) => $q->orderByDesc('created_at')->with('changedBy'),
            ])
            ->where('user_id', Auth::id())
            ->withCount(['statusHistories as comments_count' => function ($query) {
                $query->whereNotNull('comment')->where('comment', '!=', '');
            }])
            ->latest()
            ->get();

        return Inertia::render('Tickets/MyTickets', [
            'tickets' => $tickets,
        ]);
    }

    public function show(Ticket $ticket): JsonResponse
    {
        if ($ticket->user_id !== Auth::id() && ! Gate::allows('viewTicketBoard')) {
            abort(403);
        }

        $ticket->load([
            'user',
            'assignedTo',
            'statusHistories' => fn ($q) => $q->orderBy('created_at')->with('changedBy'),
        ]);

        return response()->json([
            'ticket' => $ticket,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'issue_type' => 'required|string',
            'module' => 'required|string',
            'affected_user' => 'required|string',
            'description' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,mp4,pdf,doc,docx|max:10240',
        ]);

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('attachments', 'public');
        }

        $ticket = Ticket::create([
            'user_id' => Auth::id(),
            'issue_type' => $request->issue_type,
            'module' => $request->module,
            'affected_user' => $request->affected_user,
            'description' => $request->description,
            'attachment' => $path,
            'status' => Ticket::STATUS_PENDIENTE,
        ]);

        TicketStatusHistory::create([
            'ticket_id' => $ticket->id,
            'status' => Ticket::STATUS_PENDIENTE,
            'type' => 'status_change',
            'comment' => 'Ticket registrado',
            'changed_by' => Auth::id(),
        ]);

        return redirect()->back();
    }

    public function assign(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'priority' => 'required|in:1,2,3',
            'due_date' => 'nullable|date',
            'comment' => 'nullable|string',
        ]);

        $ticket->update([
            'status' => Ticket::STATUS_ASIGNADO_TI,
            'assigned_to' => $validated['assigned_to'],
            'priority' => $validated['priority'],
            'due_date' => $validated['due_date'] ?? now()->addDays(2),
        ]);

        TicketStatusHistory::create([
            'ticket_id' => $ticket->id,
            'status' => Ticket::STATUS_ASIGNADO_TI,
            'type' => 'status_change',
            'comment' => $validated['comment'] ?? 'Asignado a TI',
            'changed_by' => Auth::id(),
        ]);

        return back();
    }

    public function updateStatus(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|string',
            'comment' => 'nullable|string',
        ]);

        $ticket->update(['status' => $validated['status']]);

        TicketStatusHistory::create([
            'ticket_id' => $ticket->id,
            'status' => $validated['status'],
            'type' => 'status_change',
            'comment' => $validated['comment'] ?? 'Estado actualizado',
            'changed_by' => Auth::id(),
        ]);

        return back();
    }

    public function addComment(Request $request, Ticket $ticket): RedirectResponse
    {
        if ($ticket->user_id !== Auth::id() && ! Gate::allows('viewTicketBoard')) {
            abort(403);
        }

        $validated = $request->validate([
            'comment' => 'required|string',
        ]);

        TicketStatusHistory::create([
            'ticket_id' => $ticket->id,
            'status' => $ticket->status,
            'type' => 'comment',
            'comment' => $validated['comment'],
            'changed_by' => Auth::id(),
        ]);

        return back();
    }
}