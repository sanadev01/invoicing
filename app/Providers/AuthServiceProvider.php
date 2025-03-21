<?php
/**
 * Invoice Ninja (https://invoiceninja.com).
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2025. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://www.elastic.co/licensing/elastic-license
 */

namespace App\Providers;

use App\Models\Task;
use App\Models\User;
use App\Models\Quote;
use App\Models\Client;
use App\Models\Credit;
use App\Models\Design;
use App\Models\Vendor;
use App\Models\Company;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Project;
use App\Models\TaxRate;
use App\Models\Webhook;
use App\Models\Activity;
use App\Models\Document;
use App\Models\Location;
use App\Models\Scheduler;
use App\Models\TaskStatus;
use App\Models\PaymentTerm;
use App\Models\CompanyToken;
use App\Models\GroupSetting;
use App\Models\Subscription;
use App\Policies\TaskPolicy;
use App\Policies\UserPolicy;
use App\Models\PurchaseOrder;
use App\Policies\QuotePolicy;
use App\Models\CompanyGateway;
use App\Models\RecurringQuote;
use App\Policies\ClientPolicy;
use App\Policies\CreditPolicy;
use App\Policies\DesignPolicy;
use App\Policies\VendorPolicy;
use App\Models\BankIntegration;
use App\Models\BankTransaction;
use App\Models\ExpenseCategory;
use App\Policies\CompanyPolicy;
use App\Policies\ExpensePolicy;
use App\Policies\InvoicePolicy;
use App\Policies\PaymentPolicy;
use App\Policies\ProductPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\TaxRatePolicy;
use App\Policies\WebhookPolicy;
use App\Models\RecurringExpense;
use App\Models\RecurringInvoice;
use App\Policies\ActivityPolicy;
use App\Policies\DocumentPolicy;
use App\Policies\LocationPolicy;
use App\Policies\SchedulerPolicy;
use App\Policies\TaskStatusPolicy;
use App\Models\BankTransactionRule;
use App\Policies\PaymentTermPolicy;
use App\Policies\CompanyTokenPolicy;
use App\Policies\GroupSettingPolicy;
use App\Policies\SubscriptionPolicy;
use Illuminate\Support\Facades\Gate;
use App\Policies\PurchaseOrderPolicy;
use App\Policies\CompanyGatewayPolicy;
use App\Policies\RecurringQuotePolicy;
use App\Policies\BankIntegrationPolicy;
use App\Policies\BankTransactionPolicy;
use App\Policies\ExpenseCategoryPolicy;
use App\Policies\RecurringExpensePolicy;
use App\Policies\RecurringInvoicePolicy;
use App\Policies\BankTransactionRulePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Activity::class => ActivityPolicy::class,
        BankIntegration::class => BankIntegrationPolicy::class,
        BankTransaction::class => BankTransactionPolicy::class,
        BankTransactionRule::class => BankTransactionRulePolicy::class,
        Client::class => ClientPolicy::class,
        Company::class => CompanyPolicy::class,
        CompanyToken::class => CompanyTokenPolicy::class,
        CompanyGateway::class => CompanyGatewayPolicy::class,
        Credit::class => CreditPolicy::class,
        Design::class => DesignPolicy::class,
        Document::class => DocumentPolicy::class,
        Expense::class => ExpensePolicy::class,
        ExpenseCategory::class => ExpenseCategoryPolicy::class,
        GroupSetting::class => GroupSettingPolicy::class,
        Invoice::class => InvoicePolicy::class,
        Location::class => LocationPolicy::class,
        Payment::class => PaymentPolicy::class,
        PaymentTerm::class => PaymentTermPolicy::class,
        Product::class => ProductPolicy::class,
        Project::class => ProjectPolicy::class,
        PurchaseOrder::class => PurchaseOrderPolicy::class,
        Quote::class => QuotePolicy::class,
        RecurringExpense::class => RecurringExpensePolicy::class,
        RecurringInvoice::class => RecurringInvoicePolicy::class,
        RecurringQuote::class => RecurringQuotePolicy::class,
        Scheduler::class => SchedulerPolicy::class,
        Subscription::class => SubscriptionPolicy::class,
        Task::class => TaskPolicy::class,
        TaskStatus::class => TaskStatusPolicy::class,
        TaxRate::class => TaxRatePolicy::class,
        User::class => UserPolicy::class,
        Vendor::class => VendorPolicy::class,
        Webhook::class => WebhookPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('view-list', function ($user, $entity) {
            $entity = strtolower(class_basename($entity));

            return $user->hasPermission('view_'.$entity) || $user->isAdmin();
        });
    }
}
