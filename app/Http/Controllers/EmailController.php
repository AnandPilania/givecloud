<?php

namespace Ds\Http\Controllers;

use Ds\Models\Email;
use Illuminate\Database\Eloquent\Builder;

class EmailController extends Controller
{
    public function destroy()
    {
        user()->canOrRedirect('email.edit');

        $email = Email::findOrFail(request('id'));
        $email->is_deleted = 1;
        $email->save();

        $this->flash->success('Email deleted successfully.');

        return redirect()->to('jpanel/emails');
    }

    public function index()
    {
        return redirect()->to('/jpanel/settings/email');
    }

    public function save()
    {
        user()->canOrRedirect('email.edit');

        // create record if it doesn't exist
        if (! request('id')) {
            $email = new \Ds\Models\Email;
            $email->type = request('email_type');
        } else {
            $email = \Ds\Models\Email::find(request('id'));
        }

        if (! $email->is_protected) {
            $email->name = request('name');
            $email->hint = request('hint');
        }

        $email->subject = request('subject');
        $email->to = request('to');
        $email->cc = request('cc');
        $email->bcc = request('bcc');
        $email->body_template = request('body_template');
        $email->is_active = (request('is_active') == 1) ? true : false;
        $email->disables_generic = request('disables_generic') == 1;

        if (request()->filled('emailtype')) {
            $email->type = request('emailtype');

            if (request('day_offset_type') == 'after') {
                $email->day_offset = abs(request('day_offset'));
            } elseif (request('day_offset_type') == 'before') {
                $email->day_offset = -abs(request('day_offset'));
            } else {
                $email->day_offset = 0;
            }
        }

        $email->save();

        // Clear existing relations and reattach
        $email->products()->detach();
        $email->variants()->detach();
        $email->memberships()->detach();

        if ($email->type == 'product_purchase') {
            $email->products()->sync(request('product_id'));
        } elseif ($email->type == 'membership_expired') {
            $email->memberships()->sync(request('membership_id'));
        } elseif ($email->type == 'variant_purchase') {
            $email->variants()->sync(request('variant_id'));
        }

        $this->flash->success('Email saved successfully.');

        return redirect()->to('jpanel/emails');
    }

    public function view()
    {
        user()->canOrRedirect('email');

        $__menu = 'admin.emails';

        $email = Email::where('id', request('i'))
            ->withTrashed()
            ->firstOr(function () {
                return false;
            });

        if (request('i')) {
            $isNew = 0;
            $emailModel = \Ds\Models\Email::query()->with([
                'memberships', 'products', 'variants',
            ])->findOrFail(request('i'));

            $title = $emailModel->name;
        } else {
            $isNew = 1;
            $title = 'Add Email Notification';
            $emailModel = new \Ds\Models\Email;
        }

        $memberships = \Ds\Models\Membership::where('deleted_at', '=', null)->orderBy('name')->get();

        pageSetup($title, 'jpanel');

        $overlappingOnProducts = $overlappingOnVariants = collect([]);

        if ($email) {
            $overlappingOnProducts = Email::query()
                ->whereKeyNot($emailModel->id)
                ->whereHas('products', function (Builder $query) use ($emailModel) {
                    $query->whereKey($emailModel->products->pluck('id'));
                })->get();

            $overlappingOnVariants = Email::query()
                ->whereKeyNot($emailModel->id)
                ->whereHas('variants', function (Builder $query) use ($emailModel) {
                    $query->whereKey($emailModel->variants->pluck('id'));
                })->get();
        }

        $is_to_customer = ($email !== false && $email->type !== 'admin_order_received' && $email->type !== 'product_purchase' && $email->type !== 'variant_purchase');
        $to = ($email === false) ? '[[bill_email]]' : $email->to;

        return $this->getView('emails/view', compact(
            '__menu',
            'email',
            'memberships',
            'isNew',
            'title',
            'emailModel',
            'is_to_customer',
            'to',
            'overlappingOnVariants',
            'overlappingOnProducts',
        ));
    }
}
