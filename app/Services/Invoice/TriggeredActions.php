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

namespace App\Services\Invoice;

use App\Utils\Ninja;
use App\Models\Invoice;
use App\Models\Webhook;
use Illuminate\Http\Request;
use App\Jobs\Entity\EmailEntity;
use App\Services\AbstractService;
use App\Utils\Traits\GeneratesCounter;
use App\Events\Invoice\InvoiceWasEmailed;

class TriggeredActions extends AbstractService
{
    use GeneratesCounter;

    private bool $updated = false;

    public function __construct(private Invoice $invoice, private Request $request)
    {
    }

    public function run()
    {
        if ($this->request->has('auto_bill') && $this->request->input('auto_bill') == 'true') {
            try {
                $this->invoice->service()->autoBill();
            } catch (\Exception $e) {
                nlog("Exception:: TriggeredActions::" . $e->getMessage());
            } 
        }

        if ($this->request->has('paid') && $this->request->input('paid') == 'true') {
            $this->invoice = $this->invoice->service()->markPaid($this->request->input('reference'))->save(); //update notification sends automatically for this.
        }

        if ($this->request->has('mark_sent') && $this->request->input('mark_sent') == 'true' && $this->invoice->status_id == Invoice::STATUS_DRAFT) {
            $this->invoice = $this->invoice->service()->markSent()->save(); //update notification NOT sent
            $this->updated = true;
        }

        if ($this->request->has('amount_paid') && is_numeric($this->request->input('amount_paid'))) {
            $this->invoice = $this->invoice->service()->applyPaymentAmount($this->request->input('amount_paid'), $this->request->input('reference'))->save();
            // $this->updated = false;
        }

        if ($this->request->has('send_email') && $this->request->input('send_email') == 'true') {
            $this->invoice->service()->markSent()->save();
            $this->sendEmail();
            $this->updated = false;
        }

        if ($this->request->has('cancel') && $this->request->input('cancel') == 'true') {
            $this->invoice = $this->invoice->service()->handleCancellation()->save();
            $this->updated = false;
        }

        if ($this->request->has('save_default_footer') && $this->request->input('save_default_footer') == 'true') {
            $company = $this->invoice->company;
            $settings = $company->settings;
            $settings->invoice_footer = $this->invoice->footer;
            $company->settings = $settings;
            $company->save();
        }

        if ($this->request->has('save_default_terms') && $this->request->input('save_default_terms') == 'true') {
            $company = $this->invoice->company;
            $settings = $company->settings;
            $settings->invoice_terms = $this->invoice->terms;
            $company->settings = $settings;
            $company->save();
        }

        if($this->request->has('retry_e_send') && $this->request->input('retry_e_send') == 'true' && !isset($this->invoice->backup->guid) && $this->invoice->client->peppolSendingEnabled()) {    
            \App\Services\EDocument\Jobs\SendEDocument::dispatch(get_class($this->invoice), $this->invoice->id, $this->invoice->company->db);
        }

        if($this->request->has('redirect')) {
        
            $redirectUrl = urldecode($this->request->input('redirect'));

            if (filter_var($redirectUrl, FILTER_VALIDATE_URL)) {
                $backup = ($this->invoice->backup && is_object($this->invoice->backup)) ? $this->invoice->backup : new \stdClass();
                $backup->redirect = $redirectUrl;
                $this->invoice->backup = $backup;
                $this->invoice->saveQuietly();
            }
            
        }

        if ($this->updated) {
            $this->invoice->sendEvent(Webhook::EVENT_SENT_INVOICE, "client");

        }


        return $this->invoice;
    }

    private function sendEmail()
    {
        $reminder_template = $this->invoice->calculateTemplate('invoice');

        $this->invoice->invitations->load('contact.client.country', 'invoice.client.country', 'invoice.company')->each(function ($invitation) use ($reminder_template) {
            EmailEntity::dispatch($invitation->withoutRelations(), $this->invoice->company->db, $reminder_template);
        });

        if ($this->invoice->invitations->count() > 0) {
            $this->invoice->entityEmailEvent($this->invoice->invitations->first(), $reminder_template);
            $this->invoice->sendEvent(Webhook::EVENT_SENT_INVOICE, "client");
        }
    }
}
