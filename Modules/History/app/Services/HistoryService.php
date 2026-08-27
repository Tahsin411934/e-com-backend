<?php

namespace Modules\History\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Modules\History\Models\History;

class HistoryService
{
    public function list(Request $request)
    {
        return History::query()
            ->with('user')
            ->when($request->filled('action'), fn (Builder $query) => $query->where('action', $request->string('action')))
            ->when($request->filled('entity_type'), fn (Builder $query) => $query->where('entity_type', $request->string('entity_type')))
            ->when($request->filled('entity_id'), fn (Builder $query) => $query->where('entity_id', $request->integer('entity_id')))
            ->when($request->filled('from'), fn (Builder $query) => $query->whereDate('created_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn (Builder $query) => $query->whereDate('created_at', '<=', $request->date('to')))
            ->latest()
            ->paginate($request->integer('per_page', 25));
    }

    public function show(int $id): History
    {
        return History::with('user')->findOrFail($id);
    }

    public function restore(int $id): History
    {
        $history = $this->show($id);
        $modelClass = $history->entity_type;

        if (! in_array($modelClass, config('history.models', []), true)) {
            throw new InvalidArgumentException('This history record cannot be restored.');
        }

        $model = $modelClass::withTrashed()->find($history->entity_id);
        if (! $model || ! method_exists($model, 'restore')) {
            throw new InvalidArgumentException('The original record is no longer available.');
        }

        $model->restore();

        return $history->fresh('user');
    }
}
