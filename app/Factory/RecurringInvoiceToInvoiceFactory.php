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

namespace App\Factory;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\RecurringInvoice;
use App\Utils\Helpers;
use Carbon\Carbon;

class RecurringInvoiceToInvoiceFactory
{
    public static function create(RecurringInvoice $recurring_invoice, Client $client): Invoice
    {
        $invoice = new Invoice();
        $invoice->status_id = Invoice::STATUS_DRAFT;
        $invoice->discount = $recurring_invoice->discount;
        $invoice->is_amount_discount = $recurring_invoice->is_amount_discount;
        $invoice->po_number = $recurring_invoice->po_number;
        $invoice->footer = $recurring_invoice->footer ? self::tranformObject($recurring_invoice->footer, $client) : null;
        $invoice->terms = $recurring_invoice->terms ? self::tranformObject($recurring_invoice->terms, $client) : null;
        $invoice->public_notes = $recurring_invoice->public_notes ? self::tranformObject($recurring_invoice->public_notes, $client) : null;
        $invoice->private_notes = $recurring_invoice->private_notes;
        $invoice->is_deleted = $recurring_invoice->is_deleted;
        $invoice->line_items = self::transformItems($recurring_invoice, $client);
        $invoice->tax_name1 = $recurring_invoice->tax_name1;
        $invoice->tax_rate1 = $recurring_invoice->tax_rate1;
        $invoice->tax_name2 = $recurring_invoice->tax_name2;
        $invoice->tax_rate2 = $recurring_invoice->tax_rate2;
        $invoice->tax_name3 = $recurring_invoice->tax_name3;
        $invoice->tax_rate3 = $recurring_invoice->tax_rate3;
        $invoice->total_taxes = $recurring_invoice->total_taxes;
        $invoice->subscription_id = $recurring_invoice->subscription_id;
        $invoice->custom_value1 = $recurring_invoice->custom_value1;
        $invoice->custom_value2 = $recurring_invoice->custom_value2;
        $invoice->custom_value3 = $recurring_invoice->custom_value3;
        $invoice->custom_value4 = $recurring_invoice->custom_value4;
        $invoice->amount = $recurring_invoice->amount;
        $invoice->uses_inclusive_taxes = $recurring_invoice->uses_inclusive_taxes;
        $invoice->is_proforma = $recurring_invoice->is_proforma;

        $invoice->custom_surcharge1 = $recurring_invoice->custom_surcharge1;
        $invoice->custom_surcharge2 = $recurring_invoice->custom_surcharge2;
        $invoice->custom_surcharge3 = $recurring_invoice->custom_surcharge3;
        $invoice->custom_surcharge4 = $recurring_invoice->custom_surcharge4;
        $invoice->custom_surcharge_tax1 = $recurring_invoice->custom_surcharge_tax1;
        $invoice->custom_surcharge_tax2 = $recurring_invoice->custom_surcharge_tax2;
        $invoice->custom_surcharge_tax3 = $recurring_invoice->custom_surcharge_tax3;
        $invoice->custom_surcharge_tax4 = $recurring_invoice->custom_surcharge_tax4;

        // $invoice->balance = $recurring_invoice->balance;
        $invoice->user_id = $recurring_invoice->user_id;
        $invoice->assigned_user_id = $recurring_invoice->assigned_user_id;
        $invoice->company_id = $recurring_invoice->company_id;
        $invoice->recurring_id = $recurring_invoice->id;
        $invoice->client_id = $client->id;
        $invoice->auto_bill_enabled = $recurring_invoice->auto_bill_enabled;
        $invoice->paid_to_date = 0;
        $invoice->design_id = $recurring_invoice->design_id;
        $invoice->e_invoice = self::transformEInvoice($recurring_invoice);

        return $invoice;
    }
    
    /**
     * transformEInvoice
     *
     * @param  \App\Models\RecurringInvoice $recurring_invoice
     * @return \stdClass|null
     */
    private static function transformEInvoice($recurring_invoice)
    {
        if(!$recurring_invoice->e_invoice) 
            return null;

        if(isset($recurring_invoice->e_invoice->Invoice)) {
            
            if(isset($recurring_invoice->e_invoice->Invoice->InvoicePeriod) && is_array($recurring_invoice->e_invoice->Invoice->InvoicePeriod)) {
                $period = $recurring_invoice->e_invoice->Invoice->InvoicePeriod[0];
                
                if($description = $period->Description)
                {
                    $parts = explode('|', $description);

                    if(count($parts) == 2) 
                    {
                        $start_template = explode(',', $parts[0]);
                        $end_template = explode(',', $parts[1]);

                        $start_date = date_create('now', new \DateTimeZone($recurring_invoice->client->timezone()->name));

                        foreach($start_template as $template)
                        {
                            $start_date->modify($template);
                        }
                        
                        $start_date = $start_date->format('Y-m-d');

                        $end_date = date_create('now', new \DateTimeZone($recurring_invoice->client->timezone()->name));

                        foreach($end_template as $template)
                        {
                            $end_date->modify($template);
                        }

                        $end_date = $end_date->format('Y-m-d');
                        
                        $einvoice = new \InvoiceNinja\EInvoice\Models\Peppol\Invoice();

                        $ip = new \InvoiceNinja\EInvoice\Models\Peppol\PeriodType\InvoicePeriod();
                        $ip->StartDate = new \DateTime($start_date);
                        $ip->EndDate = new \DateTime($end_date);
                        $einvoice->InvoicePeriod = [$ip];

                        
                        $stub = new \stdClass();
                        $stub->Invoice = $einvoice;

                        return $stub;

                    }
                }
            
            }

            
        }

        return null;
    }

    private static function transformItems($recurring_invoice, $client)
    {
        $currentDateTime = null;
        $line_items = $recurring_invoice->line_items;

        if (isset($recurring_invoice->next_send_date)) {
            $currentDateTime = Carbon::parse($recurring_invoice->next_send_date)->timezone($client->timezone()->name);
        }

        foreach ($line_items as $key => $item) {
            if (property_exists($line_items[$key], 'notes')) {
                $line_items[$key]->notes = Helpers::processReservedKeywords($item->notes, $client, $currentDateTime);
            }
        }

        return $line_items;
    }

    private static function tranformObject($object, $client)
    {
        return Helpers::processReservedKeywords($object, $client);
    }
}
