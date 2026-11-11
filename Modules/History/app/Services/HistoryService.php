<?php

namespace Modules\History\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Illuminate\Support\Facades\DB;
use Modules\Cart\Models\Campaign;
use Modules\Store\Support\CurrentStore;
use Modules\History\Models\History;

class HistoryService
{
    public function canPermanentlyDelete(History $history): bool
    {
        if (CurrentStore::id() !== null || ! auth()->user()?->hasPermission('histories.restore')) {
            return false;
        }
        return $this->canRestore($history);
    }

    public function canRestore(History $history): bool
    {
        $modelClass = $history->entity_type;
        return $history->action === 'deleted'
            && in_array($modelClass, config('history.models', []), true)
            && in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($modelClass), true)
            && $modelClass::onlyTrashed()->whereKey($history->entity_id)->exists();
    }

    public function permanentlyDelete(int $id): void
    {
        abort_unless(CurrentStore::id() === null && auth()->user()?->hasPermission('histories.restore'), 403);

        DB::transaction(function () use ($id) {
            $history = $this->show($id);
            $modelClass = $history->entity_type;
            if ($history->action !== 'deleted'
                || ! in_array($modelClass, config('history.models', []), true)
                || ! in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($modelClass), true)) {
                throw new InvalidArgumentException('This record cannot be permanently deleted.');
            }

            $model = $modelClass::onlyTrashed()->lockForUpdate()->find($history->entity_id);
            if (! $model) {
                throw new InvalidArgumentException('This record has already been restored or permanently deleted.');
            }

            // Honor model events and the database's cascade/restrict rules.
            if (! $model->forceDelete()) {
                throw new InvalidArgumentException('This record could not be permanently deleted.');
            }
        });
    }

    public function list(Request $request)
    {
        return History::query()
            ->with('user')
            ->when($request->filled('action'), fn (Builder $query) => $query->where('action', (string) $request->string('action')))
            ->when($request->filled('entity_type'), fn (Builder $query) => $query->where('entity_type', (string) $request->string('entity_type')))
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

        if ($history->action !== 'deleted'
            || ! in_array($modelClass, config('history.models', []), true)
            || ! in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($modelClass), true)) {
            throw new InvalidArgumentException('This history record cannot be restored.');
        }

        $model = $modelClass::onlyTrashed()->find($history->entity_id);
        if (! $model || ! method_exists($model, 'restore')) {
            throw new InvalidArgumentException('The original record is no longer available.');
        }

        if ($model instanceof Campaign) {
            abort_if(CurrentStore::id() !== null && (int) $model->store_id !== CurrentStore::id(), 404);
        }

        $model->restore();

        return $history->fresh('user');
    }
}
