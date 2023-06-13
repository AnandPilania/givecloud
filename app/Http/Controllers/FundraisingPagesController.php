<?php

namespace Ds\Http\Controllers;

use Ds\Domain\Shared\DataTable;
use Ds\Jobs\CalculateLifetimeMemberGiving;
use Ds\Models\FundraisingPage;
use Ds\Models\Member;
use Ds\Models\OrderItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use LiveControl\EloquentDataTable\ExpressionWithName;

class FundraisingPagesController extends Controller
{
    /**
     * Register controller middleware.
     */
    protected function registerMiddleware()
    {
        $this->middleware('auth');
        $this->middleware('requires.feature:fundraising_pages');
    }

    /**
     * View tax receipt list.
     */
    public function index()
    {
        // tax receipts
        user()->canOrRedirect('fundraisingpages');

        $closed = FundraisingPage::websiteType()->closed()->select([
            DB::raw('sum(1) as closed_count'),
            DB::raw('sum(functional_amount_raised) as closed_amount_raised'),
            DB::raw('sum(functional_goal_amount) as closed_goal_amount'),
            DB::raw('sum(case when functional_amount_raised >= functional_goal_amount then 1 else 0 end) as closed_success_count'),
        ])->get()->first();

        $active = FundraisingPage::websiteType()->active()->select([
            DB::raw('sum(1) as open_count'),
            DB::raw('sum(case when report_count > 0 then 1 else 0 end) as open_reported_count'),
            DB::raw('sum(functional_amount_raised) as open_amount_raised'),
            DB::raw('sum(functional_goal_amount) as open_goal_amount'),
            DB::raw('sum(case when functional_amount_raised >= functional_goal_amount then 1 else 0 end) as open_success_count'),
        ])->get()->first();

        $stats = [
            'open_page_count' => $active->open_count,
            'open_page_reported_count' => $active->open_reported_count,
            'open_page_success_count' => $active->open_success_count,
            'open_page_goal' => $active->open_goal_amount,
            'open_page_amount_raised' => $active->open_amount_raised,
            'open_page_progress_percent' => ($active->open_goal_amount && $active->open_goal_amount != 0) ? ($active->open_amount_raised / $active->open_goal_amount) * 100 : 0,
            'all_pages_count' => $active->open_count + $closed->closed_count,
            'all_pages_goal' => $active->open_goal_amount + $closed->closed_goal_amount,
            'all_pages_amount_raised' => $active->open_amount_raised + $closed->closed_amount_raised,
            'closed_pages_count' => $closed->closed_count,
            'closed_pages_goal' => $closed->closed_goal_amount,
            'closed_pages_success_count' => $closed->closed_success_count,
            'closed_pages_amount_raised' => $closed->closed_amount_raised,
            'closed_pages_progress_percent' => ($closed->closed_goal_amount && $closed->closed_goal_amount != 0) ? ($closed->closed_amount_raised / $closed->closed_goal_amount) * 100 : 0,
            'closed_pages_success_percent' => ($closed->closed_count && $closed->closed_count != 0) ? ($closed->closed_success_count / $closed->closed_count) * 100 : 0,
            'closed_pages_fail_count' => $closed->closed_count - $closed->closed_success_count,
            'closed_pages_fail_percent' => ($closed->closed_count && $closed->closed_count != 0) ? (($closed->closed_count - $closed->closed_success_count) / $closed->closed_count) * 100 : 0,
        ];

        // return view
        return $this->getView('fundraising_pages/index', [
            '__menu' => 'products.fundraising_pages',
            'fundraisers' => Member::query()->select(['id', 'display_name'])->whereHas('fundraisingPages')->orderBy('display_name')->get(),
            'pageTitle' => 'Fundraising Pages',
            'stats' => $stats,
            'page_types' => \Ds\Models\Product::whereAllowFundraisingPages(true)->orderBy('fundraising_page_name')->get(),
        ]);
    }

    /**
     * Ajax data for receipt list.
     */
    public function index_json()
    {
        // tax receipts
        user()->canOrRedirect('fundraisingpages');

        // generate data table
        $dataTable = new DataTable($this->_baseQueryWithFilters()->with('memberOrganizer.accountType'), [
            'photo_id',
            'title',
            'category',
            'member_organizer_id',
            'goal_amount',
            'donation_count',
            'amount_raised',
            'goal_deadline',
            new ExpressionWithName('goal_deadline', 'col8'),

            // extras
            'id',
            'url',
            'product_id',
            'progress_percent',
            'activated_date',
            'status',
            'report_count',
            'currency_code',
        ]);

        // format results
        $dataTable->setFormatRowFunction(function ($page) {
            $status = '';
            if ($page->memberOrganizer->isDenied) {
                $status .= '<span class="pull-right label label-pill label-outline label-danger">Denied</span> ';
            } elseif ($page->memberOrganizer->isPending || $page->memberOrganizer->isUnverified) {
                $status .= '<span class="pull-right label label-pill label-outline label-warning">Pending</span> ';
            } elseif ($page->status == 'active') {
                $status = '<span class="pull-right label label-pill label-outline label-success">Active</span> ';
            } elseif ($page->status == 'closed') {
                $status = '<span class="pull-right label label-pill label-muted label-outline">Closed</span> ';
            } elseif ($page->status == 'suspended') {
                $status = '<span class="pull-right label label-pill label-outline label-danger">Suspended</span> ';
            } else {
                $status = '<span class="pull-right label label-pill label-outline label-warning">' . e(ucwords($page->status)) . '</span> ';
            }

            if ($page->report_count > 0) {
                $status .= '<span style="margin-right:2px;" class="pull-right label label-pill label-danger"><i class="fa fa-exclamation-triangle"></i> ' . e(ucwords($page->report_count)) . '</span> ';
            }

            return [
                dangerouslyUseHTML('<a href="/jpanel/fundraising-pages/' . e($page->id) . '"><div class="avatar-xl" style="background-image:url(\'' . e(media_thumbnail($page->photo, ['200x200', 'crop' => 'entropy'])) . '\');"></div></a>'),
                dangerouslyUseHTML('<div class="meta-pre">') .
                    dangerouslyUseHTML(e($page->product->fundraising_page_name ?: 'No Name') . '</div><div class="title"><a href="/jpanel/fundraising-pages/' . e($page->id) . '">' . dangerouslyUseHTML($status) . e($page->title) . '</a></div><div class="meta-post"><a href="' . e($page->absolute_url) . '" target="_blank">/fundraiser/' . e($page->url) . '</a></div>'),
                e($page->category),
                dangerouslyUseHTML('<div class="meta-pre">&nbsp;</div><div class="stat-val"><a href="' . route('backend.member.edit', $page->memberOrganizer->id) . '"><i class="fa ' . e($page->memberOrganizer->fa_icon) . '"></i> ' . e($page->memberOrganizer->display_name) . '</a></div><div class="stat-lbl">' . e($page->memberOrganizer->accountType->name) . '</div>'),
                dangerouslyUseHTML('<div class="stat-val">' . e(money($page->goal_amount, $page->currency_code)) . '</div>'),
                dangerouslyUseHTML('<div class="stat-val"><a href="/jpanel/fundraising-pages/' . e($page->id) . '">' . e(number_format($page->donation_count, 0)) . '</a></div>'),
                dangerouslyUseHTML('<div class="text-center top-gutter"><div class="progress"><div class="progress-bar progress-bar-primary" style="width:' . e($page->progress_percent * 100) . '%;"></div></div><div class="goal-amt">' . e(money($page->amount_raised, $page->currency_code)) . '</div></div>'),
                dangerouslyUseHTML('<div class="text-center top-gutter"><div class="progress"><div class="progress-bar progress-bar-default" style="width:' . e($page->days_elapsed_percent * 100) . '%;"></div></div><div class="goal-amt">Day ' . e(number_format($page->days_elapsed)) . ' of ' . e(number_format($page->total_days)) . '</div></div>'),
                e(($page->goal_deadline) ? toLocalFormat($page->goal_deadline) : ''),
            ];
        });

        // return datatable JSON
        return response($dataTable->make());
    }

    /**
     * CSV output
     */
    public function index_csv()
    {
        // tax receipts
        user()->canOrRedirect('fundraisingpages');

        // generate data table
        $pages = $this->_baseQueryWithFilters()->get();

        // output CSV
        header('Content-type: text/csv');
        header('Cache-Control: no-store, no-cache');
        header('Content-Disposition: attachment; filename="fundraising-pages.csv"');
        $outstream = fopen('php://output', 'w');
        fputcsv($outstream, [
            'Category',
            'Page Name',
            'Type',
            'Description',
            'Url',
            'Status',
            'Author',
            'Author Email',
            'Goal',
            'Deadline',
            'Total Days',
            'Days Left',
            'Time Lapsed (Percent)',
            'Donations',
            'Amount Raised',
            'Goal Progress (Percent)',
            'Currency',
        ], ',', '"');
        foreach ($pages as $page) {
            fputcsv($outstream, [
                $page->category,
                $page->title,
                $page->product->fundraising_page_name,
                $page->description,
                $page->absolute_url,
                $page->status,
                $page->memberOrganizer->display_name,
                $page->memberOrganizer->email ?? $page->memberOrganizer->bill_email,
                number_format($page->goal_amount, 2),
                fromLocalFormat($page->goal_deadline, 'csv'),
                $page->total_days,
                $page->days_left,
                number_format($page->days_progress_percent * 100, 1),
                $page->donation_count,
                number_format($page->amount_raised, 2),
                number_format($page->progress_percent * 100, 1),
                $page->currency_code,
            ], ',', '"');
        }
        fclose($outstream);
        exit;
    }

    /**
     * View tax receipt list.
     */
    public function view($id)
    {
        // tax receipts
        user()->canOrRedirect('fundraisingpages');

        $page = FundraisingPage::websiteType()
            ->withTrashed()
            ->with([
                'memberOrganizer',
                'reports' => function ($q) {
                    $q->orderBy('reported_at', 'desc');
                },
            ])->find($id);

        // return view
        return $this->getView('fundraising_pages/view', [
            '__menu' => 'products.fundraising_pages',
            'pageTitle' => $page->title,
            'fundraising_page' => $page,
            'pendingPages' => $page->memberOrganizer->fundraisingPages()->activeOrPending()->count(),
            'filters' => $this->_baseQueryWithFilters(true),
        ]);
    }

    /**
     * Update the fundraiser
     */
    public function update($id)
    {
        // grab receipt
        $fundraiser = FundraisingPage::find($id);

        if (! $fundraiser) {
            $this->flash->error('Could not find the fundraising page.');

            return redirect()->to('/jpanel/fundraising-pages');
        }

        $fundraiser->amount_raised_offset = request('amount_raised_offset');
        $fundraiser->donation_count_offset = request('donation_count_offset');
        $fundraiser->save();
        $fundraiser->updateAggregates();

        CalculateLifetimeMemberGiving::dispatch($fundraiser->memberOrganizer);

        $this->flash->success("'" . $fundraiser->title . "' page has been saved.");

        return redirect()->to('/jpanel/fundraising-pages/' . $fundraiser->id);
    }

    /**
     * Deactivate the fundraiser
     */
    public function suspend($id)
    {
        // grab receipt
        $fundraiser = FundraisingPage::find($id);

        if (! $fundraiser) {
            $this->flash->error('Could not find the fundraising page.');

            return redirect()->to('/jpanel/fundraising-pages');
        }

        $fundraiser->suspend();

        $this->flash->success("'" . $fundraiser->title . "' page has been suspended.");

        return redirect()->to('/jpanel/fundraising-pages/' . $fundraiser->id);
    }

    /**
     * Destroy the fundraiser
     */
    public function destroy($id)
    {
        // grab receipt
        $fundraiser = FundraisingPage::find($id);

        if (! $fundraiser) {
            $this->flash->error('Could not find the fundraising page.');

            return redirect()->to('/jpanel/fundraising-pages');
        }

        $fundraiser->delete();

        $this->flash->success("'" . $fundraiser->title . "' page has been deleted.");

        return redirect()->to('/jpanel/fundraising-pages');
    }

    /**
     * Restore the fundraiser
     */
    public function restore($id)
    {
        // grab receipt
        $fundraiser = FundraisingPage::withTrashed()->where('id', $id)->first();

        if (! $fundraiser) {
            $this->flash->error('Could not find the fundraising page.');

            return redirect()->to('/jpanel/fundraising-pages');
        }

        $fundraiser->restore();

        $this->flash->success("'" . $fundraiser->title . "' page has been restored.");

        return redirect()->to('/jpanel/fundraising-pages/' . $fundraiser->id);
    }

    /**
     * Restore the fundraiser
     */
    public function activate($id)
    {
        // grab receipt
        $fundraiser = FundraisingPage::find($id);

        if (! $fundraiser) {
            $this->flash->error('Could not find the fundraising page.');

            return redirect()->to('/jpanel/fundraising-pages');
        }

        $fundraiser->markAsPendingOrActivate();

        $this->flash->success("'" . $fundraiser->title . "' page has been activated.");

        return redirect()->to('/jpanel/fundraising-pages/' . $fundraiser->id);
    }

    private function _baseQueryWithFilters(bool $wantsFilters = false)
    {
        $query = FundraisingPage::websiteType()->with('photo', 'memberOrganizer', 'product');

        $filters = (object) [];
        $filters->search = request('search');
        if ($filters->search) {
            $query->where(function ($query) use ($filters) {
                $query->where('title', 'like', "%$filters->search%");
                $query->orWhere('description', 'like', "%$filters->search%");
                $query->orWhere('url', 'like', "%$filters->search%");
                $query->orWhere('team_name', 'like', "%$filters->search%");
                $query->orWhereExists(function ($q) use ($filters) {
                    $q->select(DB::raw(1))
                        ->from('member')
                        ->whereRaw('member.id = fundraising_pages.member_organizer_id')
                        ->where('member.display_name', 'like', "%$filters->search%");
                });
            });
        }

        $filters->status = request('status');
        if ($filters->status === 'closed') {
            $query->closed();
        } elseif ($filters->status === 'active-abuse') {
            $query->active()
                ->where('report_count', '>', 0);
        } elseif (in_array($filters->status, ['draft', 'suspended', 'cancelled'])) {
            $query->where('status', $filters->status);
        } elseif ($filters->status === 'pending') {
            $query->whereHas('memberOrganizer', function (Builder $query) {
                $query->pending()->orWhere->unverified();
            });
        } elseif ($filters->status === 'denied') {
            $query->whereHas('memberOrganizer', function (Builder $query) {
                $query->denied();
            });
        } elseif ($filters->status !== 'any') {
            $query->active();
        }

        $filters->progress = request('progress');
        if ($filters->progress == 'goal-short') {
            $query->where('progress_percent', '<', 1);
        } elseif ($filters->progress == 'goal-reached') {
            $query->where('progress_percent', '=', 1);
        } elseif ($filters->progress == 'goal-exceeded') {
            $query->where('progress_percent', '>', 1);
        }

        $filters->product_id = request('product_id');
        if ($filters->product_id) {
            $query->where('product_id', '=', $filters->product_id);
        }

        $filters->category = request('category');
        if ($filters->category) {
            $query->where('category', '=', $filters->category);
        }

        $filters->created_start = request('created_start');
        $filters->created_end = request('created_end');
        if ($filters->created_start && $filters->created_end) {
            $query->whereBetween('created_at', [
                toUtcFormat($filters->created_start, 'Y-m-d 00:00:00'),
                toUtcFormat($filters->created_end, 'Y-m-d 23:59:59'),
            ]);
        } elseif ($filters->created_start) {
            $query->where('created_at', '>=', toUtc($filters->created_start)->startOfDay());
        } elseif ($filters->created_end) {
            $query->where('created_at', '<=', toUtc($filters->created_end)->endOfDay());
        }

        $filters->activated_start = request('activated_start');
        $filters->activated_end = request('activated_end');
        if ($filters->activated_start && $filters->activated_end) {
            $query->whereBetween('activated_date', [
                $filters->activated_start,
                $filters->activated_end,
            ]);
        } elseif ($filters->activated_start) {
            $query->where('activated_date', '>=', $filters->activated_start);
        } elseif ($filters->activated_end) {
            $query->where('activated_date', '<=', $filters->activated_end);
        }

        $filters->deadline_start = request('deadline_start');
        $filters->deadline_end = request('deadline_end');
        if ($filters->deadline_start && $filters->deadline_end) {
            $query->whereBetween('goal_deadline', [
                $filters->deadline_start,
                $filters->deadline_end,
            ]);
        } elseif ($filters->deadline_start) {
            $query->where('goal_deadline', '>=', $filters->deadline_start);
        } elseif ($filters->deadline_end) {
            $query->where('goal_deadline', '<=', $filters->deadline_end);
        }

        $query->when(request('fundraiser'), function (Builder $query) {
            $query->where('member_organizer_id', request('fundraiser'));
        });

        if ($wantsFilters) {
            return $filters;
        }

        return $query;
    }

    /**
     * Ajax data for receipt list.
     */
    public function orders_json($id)
    {
        $query = $this->_baseOrdersQueryWithFilters($id);

        // generate data table
        $dataTable = new DataTable($query, [
            'productorderid',
            'confirmationdatetime',
            'invoicenumber',
            'member_id',
            'variantname',
            'qty',
            'productorderitem.price',
            new ExpressionWithName('productorderitem.price', 'col8'),
            'productorderitem.recurring_amount',
            'productorder.refunded_amt',
            'productorderitem.recurring_frequency',
            'is_test',
            'iscomplete',
            'currency_code',
        ]);

        // format results
        $dataTable->setFormatRowFunction(function ($item) {
            return [
                dangerouslyUseHTML('<a href="' . e(route('backend.orders.edit', $item->productorderid)) . '"><i class="fa fa-search"></i></a>'),
                dangerouslyUseHTML(e(toLocalFormat($item->order->confirmationdatetime)) . ' <small class="text-muted">' . e(toLocalFormat($item->order->confirmationdatetime, 'g:iA')) . '</small>'),
                dangerouslyUseHTML(e($item->order->invoicenumber) . (($item->order->is_test) ? '&nbsp;<span class="pull-right label label-xs label-warning">TEST</span>' : '')),
                dangerouslyUseHTML($item->order->member->id ? sprintf(
                    '<a href="%s"><i class="fa %s"></i> %s</a>',
                    route('backend.member.edit', $item->order->member->id),
                    e($item->order->member->fa_icon),
                    e($item->order->member->display_name)
                ) : ''),
                dangerouslyUseHTML($item->variantname),
                e(number_format($item->qty)),
                e(($item->productorderitemRecurringFrequency) ? (string) money($item->productorderitemRecurringAmount, $item->currency_code) : (string) money($item->productorderitemPrice, $item->currency_code)),
                dangerouslyUseHTML((($item->productorderRefundedAmt > 0) ? '<i class="fa fa-reply"></i> ' : '') . e(($item->productorderitemRecurringFrequency) ? money($item->productorderitemRecurringAmount * $item->qty, $item->currency_code) : money($item->productorderitemPrice * $item->qty, $item->currency_code))),
                e(number_format($item->productorderRefundedAmt)),

                (bool) $item->order->is_test,
                (bool) $item->order->iscomplete,
            ];
        });

        // return datatable JSON
        return response($dataTable->make());
    }

    /**
     * Ajax data for receipt list.
     */
    public function orders_csv($id)
    {
        $fundraising_page = FundraisingPage::websiteType()->with('product')->find($id);

        $productModel = $fundraising_page->product;

        $orders_items = $this->_baseOrdersQueryWithFilters($id)
            ->with('order', 'fields', 'variant.product');

        // add shippingmethod join
        $orders_items->join('shipping_method', 'shipping_method.id', '=', 'productorder.shipping_method_id', 'left');

        // check-ins
        $orders_items->join(DB::raw('(SELECT ch.order_item_id,
                        COUNT(*) AS check_in_count,
                        MIN(check_in_at) AS first_check_in,
                        MAX(check_in_at) AS last_check_in
                    FROM ticket_check_in ch
                    GROUP BY ch.order_item_id) as check_ins'), function ($join) {
            $join->on('check_ins.order_item_id', '=', 'productorderitem.id');
        }, null, null, 'left');

        // select statement for CSV
        $orders_items->select([
            'productorderitem.*',
            DB::raw('check_ins.check_in_count'),
            DB::raw('check_ins.first_check_in'),
            DB::raw('check_ins.last_check_in'),
        ]);

        $productname_forfilename = preg_replace('/[^A-Za-z0-9]/', '', $productModel->name);
        $field_columns = $productModel->customFields;
        $field_column_headers = [];

        foreach ($field_columns as $column) {
            $field_column_headers[] = $column->name;
        }

        header('Expires: 0');
        header('Cache-control: private');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Description: File Transfer');
        header('Content-Type: text/csv');
        header('Content-disposition: attachment; filename="' . date('Y-m-d') . '_OrdersOf_' . $productname_forfilename . '.csv"');
        $outstream = fopen('php://output', 'w');

        $header = [
            'Date',
            'Contribution No.',
            'Product Name',
            'Option',
            'Qty',
            'Price',
            'Total',
            'Currency',
            'Promocode',
            'Billing Title',
            'Billing First Name',
            'Billing Last Name',
            'Billing Organization Name',
            'Billing Address 1',
            'Address 2',
            'City',
            'State/Province',
            'ZIP/Postal Code',
            'County',
            'Email',
            'Phone',
            'Card Type',
            'Card Number (Last 4 Digits)',
            'Shipping Title', 'Shipping First Name',
            'Shipping Last Name',
            'Shipping Organization Name',
            'Address 1',
            'Address 2',
            'City',
            'State/Province',
            'ZIP/Postal Code',
            'County',
            'Email',
            'Phone',
            'Shipping Method',
            'Special Notes',
            'DPO Donor#',
            'DPO Gift#',
            'Comments',
            'Check-In Count',
            'First Check-In',
            'Last Check-In',
            'Refunded Date',
            'Refunded Amount',
            'Refunded Auth',
        ];

        // add custom field headers
        $header = array_merge($header, $field_column_headers);

        // header row
        fputcsv($outstream, $header, ',', '"');

        // chunk orders in 150
        $orders_items->chunk(150, function ($orders_items_chunk) use ($outstream, $field_columns) {
            foreach ($orders_items_chunk as $order_item) {
                $row = [
                    toLocalFormat($order_item->order->createddatetime, 'csv'),
                    $order_item->order->invoicenumber,
                    $order_item->reference,
                    $order_item->description,
                    $order_item->qty,
                    (($order_item->recurring_frequency) ? $order_item->recurring_amount : $order_item->price),
                    ((($order_item->recurring_frequency) ? $order_item->recurring_amount : $order_item->price) * $order_item->order->qty),
                    $order_item->order->currency_code,
                    $order_item->promocode,
                    $order_item->order->billing_title,
                    $order_item->order->billing_first_name,
                    $order_item->order->billing_last_name,
                    $order_item->order->billing_organization_name,
                    $order_item->order->billingaddress1,
                    $order_item->order->billingaddress2,
                    $order_item->order->billingcity,
                    $order_item->order->billingstate,
                    $order_item->order->billingzip,
                    $order_item->order->billingcountry,
                    $order_item->order->billingemail,
                    $order_item->order->billingphone,
                    $order_item->order->billingcardtype,
                    $order_item->order->billingcardlastfour,
                    $order_item->order->shipping_title,
                    $order_item->order->shipping_first_name,
                    $order_item->order->shipping_last_name,
                    $order_item->order->shipping_organization_name,
                    $order_item->order->shipaddress1,
                    $order_item->order->shipaddress2,
                    $order_item->order->shipcity,
                    $order_item->order->shipstate,
                    $order_item->order->shipzip,
                    $order_item->order->shipcountry,
                    $order_item->order->shipemail,
                    $order_item->order->shipphone,
                    $order_item->order->shipping_method_name,
                    $order_item->order->comments,
                    $order_item->order->alt_contact_id,
                    $order_item->alt_transaction_id,
                    $order_item->order->customer_notes,
                    $order_item->order->check_in_count,
                    $order_item->order->first_check_in,
                    $order_item->order->last_check_in,
                    toLocalFormat($order_item->order->refunded_at, 'csv'),
                    $order_item->order->refunded_amt,
                    $order_item->order->refunded_auth,
                ];

                $fields = $order_item->fields->pluck('value', 'id')->all();
                foreach ($field_columns as $field) {
                    $row[] = $fields[$field->id] ?? null;
                }

                fputcsv($outstream, $row, ',', '"');
            }
        });

        fclose($outstream);
        exit;
    }

    /**
     * Build a base query based on request filter params.
     * Allows us to reuse this for datatables, csv, etc...
     */
    private function _baseOrdersQueryWithFilters($id)
    {
        $orders = OrderItem::where('fundraising_page_id', $id)->with('order.member');

        // joins
        $orders->join('productorder', function ($join) {
            $join->on('productorderitem.productorderid', '=', 'productorder.id')
                ->whereNull('productorder.deleted_at') // filter out soft-deletes
                ->whereRaw('productorder.confirmationdatetime is not null'); // only paid orders
        }, 'inner');

        $orders->join('productinventory', 'productinventory.id', '=', 'productorderitem.productinventoryid', 'inner');
        $orders->join('product', 'product.id', '=', 'productinventory.productid', 'inner');

        // search
        $filters = (object) [];
        $filters->search = request('search');
        if ($filters->search) {
            $orders->where(function ($query) use ($filters) {
                $query->where(DB::raw("concat(productorder.billing_first_name,' ',productorder.billing_last_name)"), 'like', "%$filters->search%");
                $query->orWhere(DB::raw("concat(productorder.shipping_first_name,' ',productorder.shipping_last_name)"), 'like', "%$filters->search%");
                $query->orWhere(DB::raw('productorder.invoicenumber'), 'like', "%$filters->search%");
            });
        }

        // completed
        switch (request('c')) {
            case '0': $orders->where('iscomplete', '=', '0'); break;
            case '1': $orders->where('iscomplete', '=', '1'); break;
            case '2': $orders->where('refunded_amt', '>', 0); break;
        }

        // issued date
        $filters->ordered_at_str = fromLocal(request('ordered_at_str'));
        $filters->ordered_at_end = fromLocal(request('ordered_at_end'));
        if ($filters->ordered_at_str && $filters->ordered_at_end) {
            $orders->whereBetween('productorder.createddatetime', [
                toUtc($filters->ordered_at_str->startOfDay()),
                toUtc($filters->ordered_at_end->endOfDay()),
            ]);
        } elseif ($filters->ordered_at_str) {
            $orders->where('productorder.createddatetime', '>=', toUtc($filters->ordered_at_str->startOfDay()));
        } elseif ($filters->ordered_at_end) {
            $orders->where('productorder.createddatetime', '<=', toUtc($filters->ordered_at_end->endOfDay()));
        }

        // total amount
        $filters->total_str = request('total_str');
        $filters->total_end = request('total_end');
        if ($filters->total_str && $filters->total_end) {
            $orders->whereRaw('(CASE WHEN productorderitem.recurring_amount > 0 THEN productorderitem.recurring_amount*productorderitem.qty ELSE productorderitem.price*productorderitem.qty END) >= ?', [$filters->total_str]);
            $orders->whereRaw('(CASE WHEN productorderitem.recurring_amount > 0 THEN productorderitem.recurring_amount*productorderitem.qty ELSE productorderitem.price*productorderitem.qty END) <= ?', [$filters->total_end]);
        } elseif ($filters->total_str) {
            $orders->whereRaw('(CASE WHEN productorderitem.recurring_amount > 0 THEN productorderitem.recurring_amount*productorderitem.qty ELSE productorderitem.price*productorderitem.qty END) >= ?', [$filters->total_str]);
        } elseif ($filters->total_end) {
            $orders->whereRaw('(CASE WHEN productorderitem.recurring_amount > 0 THEN productorderitem.recurring_amount*productorderitem.qty ELSE productorderitem.price*productorderitem.qty END) <= ?', [$filters->total_end]);
        }

        // return base query
        return $orders;
    }

    /**
     * JSON for fundraising page autocomplete field
     */
    public function autocomplete()
    {
        $query = request('query');
        $pages_json = [];
        $pages = FundraisingPage::where('title', 'like', "%$query%")
            ->websiteType()
            ->orWhere('url', 'like', "%$query%")
            ->orWhereHas('memberOrganizer', function ($q) use ($query) {
                $q->where('display_name', 'like', "%$query%")
                    ->orWhere('email', 'like', "%$query%")
                    ->orWhere('bill_phone', 'like', "%$query%");
            })->with('photo', 'memberOrganizer')
            ->get();

        foreach ($pages as $page) {
            $pages_json[] = (object) [
                'id' => (int) $page->id,
                'thumbnail' => $page->photo->thumbnail_url,
                'title' => $page->title,
                'author' => $page->memberOrganizer->display_name,
            ];
        }

        return response($pages_json);
    }
}
