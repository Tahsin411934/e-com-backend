<?php

namespace Modules\Account\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Account\Models\AccountAccount;
use Modules\Account\Models\AccountCategory;
use Modules\Account\Models\AccountDailySummary;
use Modules\Account\Models\AccountExpense;
use Modules\Account\Models\AccountTransfer;

class AccountDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $creator = User::first();

        foreach ([
            ['name' => 'Cash In Hand', 'code' => 'CASH-DEFAULT', 'type' => 'cash'],
            ['name' => 'Main Bank Account', 'code' => 'BANK-DEFAULT', 'type' => 'bank'],
            ['name' => 'Mobile Banking', 'code' => 'MOBILE-BANKING-DEFAULT', 'type' => 'mobile_banking'],
        ] as $account) {
            AccountAccount::firstOrCreate(
                ['code' => $account['code']],
                [
                    'name' => $account['name'],
                    'type' => $account['type'],
                    'currency_code' => 'BDT',
                    'opening_balance' => 0,
                    'current_balance' => 0,
                    'is_default' => true,
                    'is_active' => true,
                    'created_by' => $creator?->id,
                ]
            );
        }

        foreach ([
            ['system_key' => 'sales-income', 'name' => 'Sales Income', 'slug' => 'sales-income', 'type' => 'income'],
            ['system_key' => 'product-purchase', 'name' => 'Product Purchase', 'slug' => 'product-purchase', 'type' => 'cost_of_goods_sold'],
            ['system_key' => 'investment', 'name' => 'Investment', 'slug' => 'investment', 'type' => 'asset'],
            ['system_key' => 'sales-refunds', 'name' => 'Sales Refunds', 'slug' => 'sales-refunds', 'type' => 'expense'],
            ['system_key' => 'bank-charge', 'name' => 'Bank Charge', 'slug' => 'bank-charge', 'type' => 'expense'],
            ['system_key' => 'rent', 'name' => 'Rent', 'slug' => 'rent', 'type' => 'expense'],
            ['system_key' => 'salary', 'name' => 'Salary', 'slug' => 'salary', 'type' => 'expense'],
            ['system_key' => 'delivery-cost', 'name' => 'Delivery Cost', 'slug' => 'delivery-cost', 'type' => 'expense'],
            ['system_key' => 'packaging', 'name' => 'Packaging', 'slug' => 'packaging', 'type' => 'expense'],
            ['system_key' => 'marketing', 'name' => 'Marketing', 'slug' => 'marketing', 'type' => 'expense'],
            ['system_key' => 'utility', 'name' => 'Utility Bill', 'slug' => 'utility', 'type' => 'expense'],
        ] as $category) {
            AccountCategory::updateOrCreate(
                ['system_key' => $category['system_key']],
                $category + ['is_system' => true, 'is_active' => true]
            );
        }

        $bankAccount = AccountAccount::where('code', 'BANK-DEFAULT')->first();
        $cashAccount = AccountAccount::where('code', 'CASH-DEFAULT')->first();
        $mobileAccount = AccountAccount::where('code', 'MOBILE-BANKING-DEFAULT')->first();

        $rentCategory = AccountCategory::where('system_key', 'rent')->first();
        $bankChargeCategory = AccountCategory::where('system_key', 'bank-charge')->first();
        $salaryCategory = AccountCategory::where('system_key', 'salary')->first();
        $marketingCategory = AccountCategory::where('system_key', 'marketing')->first();

        $investmentCategory = AccountCategory::where('system_key', 'investment')->first();

        foreach ([
            [
                'expense_no' => 'EXP-0001',
                'account_id' => $bankAccount?->id,
                'category_id' => $rentCategory?->id,
                'amount' => 125000.00,
                'expense_date' => now()->subDays(6)->toDateString(),
                'vendor_name' => 'Downtown Rent Agency',
                'reference_no' => 'RENT-2026-08',
                'note' => 'Monthly office rent for August.',
            ],
            [
                'expense_no' => 'EXP-0002',
                'account_id' => $bankAccount?->id,
                'category_id' => $bankChargeCategory?->id,
                'amount' => 1_250.00,
                'expense_date' => now()->subDays(4)->toDateString(),
                'vendor_name' => 'ABC Bank',
                'reference_no' => 'BNK-FEE-241',
                'note' => 'Bank service charge for wire transfer.',
            ],
            [
                'expense_no' => 'EXP-0003',
                'account_id' => $cashAccount?->id,
                'category_id' => $salaryCategory?->id,
                'amount' => 85_750.00,
                'expense_date' => now()->subDays(3)->toDateString(),
                'vendor_name' => 'Payroll Services',
                'reference_no' => 'SAL-2026-08',
                'note' => 'Staff salary payment for the current pay period.',
            ],
            [
                'expense_no' => 'EXP-0004',
                'account_id' => $bankAccount?->id,
                'category_id' => $marketingCategory?->id,
                'amount' => 47_500.00,
                'expense_date' => now()->subDays(2)->toDateString(),
                'vendor_name' => 'Media House',
                'reference_no' => 'MKT-2026-08',
                'note' => 'Marketing campaign spend for product launch.',
            ],
            [
                'expense_no' => 'EXP-0005',
                'account_id' => $bankAccount?->id,
                'category_id' => $investmentCategory?->id,
                'amount' => 300000.00,
                'expense_date' => now()->subDays(1)->toDateString(),
                'vendor_name' => 'Equipment Capital',
                'reference_no' => 'INV-2026-08',
                'note' => 'Investment in new equipment and assets.',
            ],
        ] as $expenseData) {
            if (!$expenseData['account_id'] || !$expenseData['category_id']) {
                continue;
            }

            AccountExpense::updateOrCreate(
                ['expense_no' => $expenseData['expense_no']],
                $expenseData + [
                    'currency_code' => 'BDT',
                    'created_by' => $creator?->id,
                ]
            );
        }

        foreach ([
            [
                'transfer_no' => 'TRF-0001',
                'from_account_id' => $bankAccount?->id,
                'to_account_id' => $cashAccount?->id,
                'amount' => 50_000.00,
                'transfer_fee' => 150.00,
                'transferred_at' => now()->subDays(5)->toDateTimeString(),
                'reference_no' => 'BANK-CASH-01',
                'note' => 'Cash top-up from bank account.',
            ],
            [
                'transfer_no' => 'TRF-0002',
                'from_account_id' => $bankAccount?->id,
                'to_account_id' => $mobileAccount?->id,
                'amount' => 75_000.00,
                'transfer_fee' => 75.00,
                'transferred_at' => now()->subDays(1)->toDateTimeString(),
                'reference_no' => 'BANK-MOBILE-01',
                'note' => 'Mobile wallet top-up for payments.',
            ],
        ] as $transferData) {
            if (!$transferData['from_account_id'] || !$transferData['to_account_id']) {
                continue;
            }

            AccountTransfer::updateOrCreate(
                ['transfer_no' => $transferData['transfer_no']],
                $transferData + [
                    'currency_code' => 'BDT',
                    'created_by' => $creator?->id,
                ]
            );
        }

        if ($bankAccount) {
            AccountDailySummary::updateOrCreate(
                [
                    'summary_date' => now()->subDays(1)->toDateString(),
                    'account_id' => $bankAccount->id,
                    'store_id' => null,
                ],
                [
                    'opening_balance' => 250_000.00,
                    'closing_balance' => 238_250.00,
                    'total_income' => 320_000.00,
                    'total_expense' => 81_750.00,
                    'total_sales' => 300_000.00,
                    'total_refunds' => 15_000.00,
                    'total_cogs' => 120_000.00,
                    'gross_profit' => 180_000.00,
                    'net_profit' => 98_250.00,
                    'metadata' => ['notes' => 'Sample daily summary for bank account.'],
                ]
            );
        }
    }
}
