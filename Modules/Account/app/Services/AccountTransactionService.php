<?php

namespace Modules\Account\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\Account\Models\AccountAccount;
use Modules\Account\Models\AccountCategory;
use Modules\Account\Models\AccountProductProfitSnapshot;
use Modules\Account\Models\AccountTransaction;
use Modules\Catalog\Models\ProductVariant;
use Modules\Order\Models\Order;
use Modules\Order\Models\OrderItem;
use Modules\Order\Models\Payment;
use Modules\Order\Models\Refund;
use Modules\Pos\Models\PosSale;
use Modules\Inventory\Models\PurchaseOrder;

class AccountTransactionService
{
    public function postPayment(Payment $payment): ?AccountTransaction
    {
        if (!in_array($payment->status, ['authorized', 'captured'], true)) {
            return null;
        }

        return DB::transaction(function () use ($payment) {
            $existing = $this->findPostedSourceTransaction($payment, 'sale');
            if ($existing) {
                return $existing;
            }

            $account = $this->resolveAccountForPaymentMethod($payment->method);
            $salesCategory = $this->ensureCategory('sales-income', 'Sales Income', 'income');

            $transaction = $this->createPostedTransaction([
                'type' => 'sale',
                'source' => $payment,
                'currency_code' => $payment->currency_code ?: 'BDT',
                'amount' => (float) $payment->amount,
                'account_id' => $account->id,
                'category_id' => $salesCategory->id,
                'account_entry_type' => 'debit',
                'category_entry_type' => 'credit',
                'transaction_date' => $payment->paid_at ?? $payment->created_at ?? now(),
                'reference_no' => $payment->provider_payment_id,
                'description' => 'Sales payment received',
                'metadata' => [
                    'provider' => $payment->provider,
                    'method' => $payment->method,
                    'order_id' => $payment->order_id,
                ],
            ]);

            $this->increaseAccountBalance($account, (float) $payment->amount);
            $this->syncOrderProfitSnapshots($payment->order);

            return $transaction;
        });
    }

    public function postRefund(Refund $refund): ?AccountTransaction
    {
        if ($refund->status !== 'completed') {
            return null;
        }

        return DB::transaction(function () use ($refund) {
            $existing = $this->findPostedSourceTransaction($refund, 'refund');
            if ($existing) {
                return $existing;
            }

            $method = $refund->payment?->method ?? 'cash';
            $account = $this->resolveAccountForPaymentMethod($method);
            $refundCategory = $this->ensureCategory('sales-refunds', 'Sales Refunds', 'expense');

            $transaction = $this->createPostedTransaction([
                'type' => 'refund',
                'source' => $refund,
                'currency_code' => $refund->payment?->currency_code ?: 'BDT',
                'amount' => (float) $refund->amount,
                'account_id' => $account->id,
                'category_id' => $refundCategory->id,
                'account_entry_type' => 'credit',
                'category_entry_type' => 'debit',
                'transaction_date' => $refund->processed_at ?? $refund->created_at ?? now(),
                'reference_no' => $refund->order?->order_number,
                'description' => 'Sales refund paid',
                'metadata' => [
                    'payment_id' => $refund->payment_id,
                    'order_id' => $refund->order_id,
                    'reason' => $refund->reason,
                ],
            ]);

            $this->decreaseAccountBalance($account, (float) $refund->amount);

            return $transaction;
        });
    }

    public function postPurchaseOrder(PurchaseOrder $purchaseOrder): ?AccountTransaction
    {
        if ($purchaseOrder->payment_status !== 'paid') {
            return null;
        }

        return DB::transaction(function () use ($purchaseOrder) {
            $existing = $this->findPostedSourceTransaction($purchaseOrder, 'purchase');
            if ($existing) {
                return $existing;
            }

            $account = AccountAccount::whereIn('type', ['bank', 'cash'])
                ->where('is_active', true)
                ->orderByRaw("FIELD(type, 'bank', 'cash')")
                ->orderByDesc('is_default')
                ->first() ?: $this->resolveAccountForPaymentMethod('cash');
            $category = $this->ensureCategory('product-purchase', 'Product Purchase', 'cost_of_goods_sold');
            $amount = (float) $purchaseOrder->total_amount;

            $transaction = $this->createPostedTransaction([
                'type' => 'purchase',
                'source' => $purchaseOrder,
                'currency_code' => 'BDT',
                'amount' => $amount,
                'account_id' => $account->id,
                'category_id' => $category->id,
                'account_entry_type' => 'credit',
                'category_entry_type' => 'debit',
                'transaction_date' => $purchaseOrder->received_date ?? $purchaseOrder->order_date ?? now(),
                'reference_no' => $purchaseOrder->po_number,
                'description' => 'Purchase order paid',
                'metadata' => [
                    'supplier_id' => $purchaseOrder->supplier_id,
                    'store_id' => $purchaseOrder->store_id,
                ],
            ]);

            $this->decreaseAccountBalance($account, $amount);

            return $transaction;
        });
    }

    public function postPosSale(PosSale $sale): ?AccountTransaction
    {
        if ($sale->status !== 'completed' || !in_array($sale->payment_status, ['paid', 'partial'], true)) {
            return null;
        }

        return DB::transaction(function () use ($sale) {
            $existing = $this->findPostedSourceTransaction($sale, 'sale');
            if ($existing) {
                return $existing;
            }

            $salesCategory = $this->ensureCategory('sales-income', 'Sales Income', 'income');
            $transaction = AccountTransaction::create([
                'transaction_no' => $this->generateTransactionNo('SALE'),
                'type' => 'sale',
                'status' => 'posted',
                'source_type' => $sale::class,
                'source_id' => $sale->id,
                'currency_code' => 'BDT',
                'total_debit' => (float) $sale->total,
                'total_credit' => (float) $sale->total,
                'transaction_date' => $sale->created_at ?? now(),
                'reference_no' => $sale->receipt_number,
                'description' => 'POS sale received',
                'metadata' => [
                    'cash_amount' => (float) $sale->cash_amount,
                    'card_amount' => (float) $sale->card_amount,
                    'other_amount' => (float) $sale->other_amount,
                ],
                'created_by' => auth()->id(),
                'posted_by' => auth()->id(),
                'posted_at' => now(),
            ]);

            foreach ([
                'cash' => (float) $sale->cash_amount,
                'card' => (float) $sale->card_amount,
                'other' => (float) $sale->other_amount,
            ] as $method => $amount) {
                if ($amount <= 0) {
                    continue;
                }

                $account = $this->resolveAccountForPaymentMethod($method);
                $transaction->lines()->create([
                    'account_id' => $account->id,
                    'entry_type' => 'debit',
                    'amount' => $amount,
                    'memo' => ucfirst($method) . ' received',
                ]);
                $this->increaseAccountBalance($account, $amount);
            }

            $transaction->lines()->create([
                'category_id' => $salesCategory->id,
                'entry_type' => 'credit',
                'amount' => (float) $sale->total,
                'memo' => 'POS sales income',
            ]);

            $this->syncPosSaleProfitSnapshots($sale);

            return $transaction;
        });
    }

    public function postExpense(Model $expense, AccountAccount $account, AccountCategory $category, float $amount): AccountTransaction
    {
        return DB::transaction(function () use ($expense, $account, $category, $amount) {
            $existing = $this->findPostedSourceTransaction($expense, 'expense');
            if ($existing) {
                return $existing;
            }

            $transaction = $this->createPostedTransaction([
                'type' => 'expense',
                'source' => $expense,
                'currency_code' => $expense->currency_code ?: $account->currency_code,
                'amount' => $amount,
                'account_id' => $account->id,
                'category_id' => $category->id,
                'account_entry_type' => 'credit',
                'category_entry_type' => 'debit',
                'transaction_date' => $expense->expense_date ?? now(),
                'reference_no' => $expense->reference_no,
                'description' => $expense->note ?: 'Manual expense',
            ]);

            $this->decreaseAccountBalance($account, $amount);

            return $transaction;
        });
    }

    public function postTransfer(Model $transfer, AccountAccount $fromAccount, AccountAccount $toAccount): AccountTransaction
    {
        return DB::transaction(function () use ($transfer, $fromAccount, $toAccount) {
            $existing = $this->findPostedSourceTransaction($transfer, 'transfer');
            if ($existing) {
                return $existing;
            }

            $amount = (float) $transfer->amount;
            $fee = (float) $transfer->transfer_fee;
            $transaction = AccountTransaction::create([
                'transaction_no' => $this->generateTransactionNo('TRF'),
                'type' => 'transfer',
                'status' => 'posted',
                'source_type' => $transfer::class,
                'source_id' => $transfer->id,
                'currency_code' => $transfer->currency_code ?: $fromAccount->currency_code,
                'total_debit' => $amount + $fee,
                'total_credit' => $amount + $fee,
                'transaction_date' => $transfer->transferred_at,
                'reference_no' => $transfer->reference_no,
                'description' => $transfer->note ?: 'Account transfer',
                'created_by' => auth()->id(),
                'posted_by' => auth()->id(),
                'posted_at' => now(),
            ]);

            $transaction->lines()->create([
                'account_id' => $toAccount->id,
                'entry_type' => 'debit',
                'amount' => $amount,
                'memo' => 'Transfer in',
            ]);

            $transaction->lines()->create([
                'account_id' => $fromAccount->id,
                'entry_type' => 'credit',
                'amount' => $amount + $fee,
                'memo' => 'Transfer out',
            ]);

            if ($fee > 0) {
                $feeCategory = $this->ensureCategory('bank-charge', 'Bank Charge', 'expense');
                $transaction->lines()->create([
                    'category_id' => $feeCategory->id,
                    'entry_type' => 'debit',
                    'amount' => $fee,
                    'memo' => 'Transfer fee',
                ]);
            }

            $this->decreaseAccountBalance($fromAccount, $amount + $fee);
            $this->increaseAccountBalance($toAccount, $amount);

            return $transaction;
        });
    }

    public function syncOrderProfitSnapshots(?Order $order): void
    {
        if (!$order) {
            return;
        }

        $order->loadMissing(['items.variant', 'store']);

        foreach ($order->items as $item) {
            $unitCost = (float) ($item->unit_cost ?: $item->variant?->cost_price ?: 0);
            $quantity = (int) $item->quantity;
            $netSales = (float) $item->line_total;
            $costTotal = $unitCost * $quantity;
            $grossProfit = $netSales - $costTotal;
            $margin = $netSales > 0 ? ($grossProfit / $netSales) * 100 : 0;

            if (Schema::hasColumn('order_items', 'unit_cost')) {
                $item->forceFill([
                    'unit_cost' => $unitCost,
                    'cost_total' => $costTotal,
                    'gross_profit' => $grossProfit,
                    'profit_margin' => $margin,
                ])->save();
            }

            AccountProductProfitSnapshot::updateOrCreate(
                ['order_item_id' => $item->id],
                [
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'store_id' => $order->store_id,
                    'sku' => $item->sku,
                    'product_name' => $item->product_name,
                    'variant_name' => $item->variant_name,
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'unit_sale_price' => (float) $item->unit_price,
                    'gross_sales' => ((float) $item->unit_price) * $quantity,
                    'discount_total' => (float) $item->discount_total,
                    'tax_total' => (float) $item->tax_total,
                    'net_sales' => $netSales,
                    'cost_total' => $costTotal,
                    'gross_profit' => $grossProfit,
                    'profit_margin' => $margin,
                    'currency_code' => $order->currency_code ?: 'BDT',
                    'sold_at' => $order->placed_at ?? $order->created_at ?? now(),
                ]
            );
        }
    }

    public function syncPosSaleProfitSnapshots(PosSale $sale): void
    {
        $sale->loadMissing(['items.product.variants', 'register.store']);

        foreach ($sale->items as $item) {
            $variant = $item->variant_id
                ? ProductVariant::find($item->variant_id)
                : $item->product?->variants?->first();
            $quantity = (float) $item->quantity;
            $unitCost = (float) ($variant?->cost_price ?? 0);
            $netSales = (float) $item->total;
            $costTotal = $unitCost * $quantity;
            $grossProfit = $netSales - $costTotal;
            $margin = $netSales > 0 ? ($grossProfit / $netSales) * 100 : 0;

            AccountProductProfitSnapshot::updateOrCreate(
                ['pos_sale_item_id' => $item->id],
                [
                    'pos_sale_id' => $sale->id,
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id ?: $variant?->id,
                    'store_id' => $sale->register?->store_id,
                    'sku' => $item->sku,
                    'product_name' => $item->product_name,
                    'variant_name' => $variant?->name,
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'unit_sale_price' => (float) $item->unit_price,
                    'gross_sales' => (float) $item->subtotal,
                    'discount_total' => (float) $item->discount_amount,
                    'tax_total' => (float) $item->tax_amount,
                    'net_sales' => $netSales,
                    'cost_total' => $costTotal,
                    'gross_profit' => $grossProfit,
                    'profit_margin' => $margin,
                    'currency_code' => 'BDT',
                    'sold_at' => $sale->created_at ?? now(),
                ]
            );
        }
    }

    public function ensureCategory(string $systemKey, string $name, string $type): AccountCategory
    {
        return AccountCategory::withTrashed()->updateOrCreate(
            ['system_key' => $systemKey],
            [
                'name' => $name,
                'slug' => Str::slug($systemKey),
                'type' => $type,
                'is_system' => true,
                'is_active' => true,
                'deleted_at' => null,
            ]
        );
    }

    public function resolveAccountForPaymentMethod(string $method): AccountAccount
    {
        $type = match ($method) {
            'cash', 'cod' => 'cash',
            'bank_transfer' => 'bank',
            'card' => 'card',
            'wallet' => 'mobile_banking',
            default => 'gateway',
        };

        $account = AccountAccount::where('type', $type)
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->first();

        if ($account) {
            return $account;
        }

        return AccountAccount::create([
            'name' => Str::headline($type) . ' Account',
            'code' => strtoupper($type) . '-DEFAULT',
            'type' => $type,
            'currency_code' => 'BDT',
            'opening_balance' => 0,
            'current_balance' => 0,
            'is_default' => true,
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);
    }

    private function createPostedTransaction(array $data): AccountTransaction
    {
        $amount = (float) $data['amount'];
        $transaction = AccountTransaction::create([
            'transaction_no' => $this->generateTransactionNo(strtoupper(substr($data['type'], 0, 4))),
            'type' => $data['type'],
            'status' => 'posted',
            'source_type' => isset($data['source']) ? $data['source']::class : null,
            'source_id' => $data['source']->id ?? null,
            'currency_code' => $data['currency_code'] ?? 'BDT',
            'total_debit' => $amount,
            'total_credit' => $amount,
            'transaction_date' => $data['transaction_date'] ?? now(),
            'reference_no' => $data['reference_no'] ?? null,
            'description' => $data['description'] ?? null,
            'metadata' => $data['metadata'] ?? null,
            'created_by' => auth()->id(),
            'posted_by' => auth()->id(),
            'posted_at' => now(),
        ]);

        $transaction->lines()->create([
            'account_id' => $data['account_id'],
            'entry_type' => $data['account_entry_type'],
            'amount' => $amount,
        ]);

        $transaction->lines()->create([
            'category_id' => $data['category_id'],
            'entry_type' => $data['category_entry_type'],
            'amount' => $amount,
        ]);

        return $transaction;
    }

    private function findPostedSourceTransaction(Model $source, string $type): ?AccountTransaction
    {
        return AccountTransaction::where('source_type', $source::class)
            ->where('source_id', $source->id)
            ->where('type', $type)
            ->where('status', 'posted')
            ->first();
    }

    private function increaseAccountBalance(AccountAccount $account, float $amount): void
    {
        $account->increment('current_balance', $amount);
    }

    private function decreaseAccountBalance(AccountAccount $account, float $amount): void
    {
        $account->decrement('current_balance', $amount);
    }

    private function generateTransactionNo(string $prefix): string
    {
        do {
            $number = $prefix . '-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(5));
        } while (AccountTransaction::where('transaction_no', $number)->exists());

        return $number;
    }
}
