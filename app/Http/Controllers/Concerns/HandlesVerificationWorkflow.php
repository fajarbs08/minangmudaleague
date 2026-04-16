<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

trait HandlesVerificationWorkflow
{
    private function validateReviewPayload(Request $request): array
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,revision,rejected'],
            'verification_notes' => ['nullable', 'string'],
        ]);

        if (in_array($validated['status'], ['revision', 'rejected'], true)) {
            $request->validate([
                'verification_notes' => ['required', 'string'],
            ]);
        }

        return $validated;
    }

    private function submitForVerification(Model $model, string $successMessage)
    {
        abort_unless(method_exists($model, 'canBeSubmittedByClub') && $model->canBeSubmittedByClub(), 422);

        if (method_exists($model, 'validateForSubmission')) {
            $model->validateForSubmission();
        }

        $model->update([
            'verification_status' => 'submitted',
            'submitted_at' => now(),
            'verification_notes' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ]);

        return redirect()->back()->with('status', $successMessage);
    }

    private function reviewSubmission(Model $model, string $status, ?string $notes, string $successMessage)
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        abort_unless(method_exists($model, 'canBeReviewedByAdmin') && $model->canBeReviewedByAdmin(), 422);

        if (($model->verification_status ?? null) === 'approved' && in_array($status, ['approved', 'rejected'], true)) {
            throw ValidationException::withMessages([
                'status' => 'Data yang sudah diterima hanya bisa diubah ke revisi bila memang perlu perbaikan.',
            ]);
        }

        $model->update([
            'verification_status' => $status,
            'verification_notes' => $notes,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return redirect()->back()->with('status', $successMessage);
    }

    private function bulkReviewSubmissions(Request $request, $query, string $successMessage)
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $validated = $this->validateReviewPayload($request);
        $selectedIds = $request->validate([
            'selected_ids' => ['required', 'array', 'min:1'],
            'selected_ids.*' => ['integer'],
        ])['selected_ids'];

        $models = (clone $query)
            ->whereKey($selectedIds)
            ->get()
            ->filter(fn (Model $model) => method_exists($model, 'canBeReviewedByAdmin') && $model->canBeReviewedByAdmin())
            ->values();

        if ($models->isEmpty()) {
            throw ValidationException::withMessages([
                'selected_ids' => 'Tidak ada data yang bisa direview dari pilihan tersebut.',
            ]);
        }

        if (in_array($validated['status'], ['approved', 'rejected'], true)) {
            $models = $models
                ->reject(fn (Model $model) => ($model->verification_status ?? null) === 'approved')
                ->values();

            if ($models->isEmpty()) {
                throw ValidationException::withMessages([
                    'selected_ids' => 'Semua data yang dipilih sudah diterima. Data yang sudah diterima hanya bisa diubah ke revisi bila memang perlu perbaikan.',
                ]);
            }
        }

        $models->each(function (Model $model) use ($validated) {
            $model->update([
                'verification_status' => $validated['status'],
                'verification_notes' => $validated['verification_notes'] ?? null,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);
        });

        return redirect()->back()->with('status', str_replace(':count', (string) $models->count(), $successMessage));
    }
}
