<?php

namespace Modules\History\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Modules\History\Models\History;

class HistoryObserver
{
    public function created(Model $model): void
    {
        $this->record($model, 'created', null, $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        $changes = $model->getChanges();
        unset($changes['updated_at']);

        if ($changes === []) {
            return;
        }

        // Skip non-meaningful writes (e.g. last_login_at updated on every
        // login, remember_token rotations) so the audit trail stays clean.
        $noiseKeys = ['last_login_at', 'remember_token'];
        if (count(array_diff(array_keys($changes), $noiseKeys)) === 0) {
            return;
        }

        $old = [];
        foreach (array_keys($changes) as $field) {
            $old[$field] = $model->getOriginal($field);
        }

        $this->record($model, 'updated', $old, $changes);
    }

    public function deleted(Model $model): void
    {
        $this->record($model, 'deleted', $model->getAttributes(), null);
    }

    public function restored(Model $model): void
    {
        $this->record($model, 'restored', ['deleted_at' => $model->getOriginal('deleted_at')], ['deleted_at' => null]);
    }

    private function record(Model $model, string $action, ?array $old, ?array $new): void
    {
        if ($model instanceof History) {
            return;
        }

        History::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'entity_type' => $model::class,
            'entity_id' => $model->getKey(),
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'description' => sprintf('%s %s #%s', class_basename($model), $action, $model->getKey()),
        ]);
    }
}
