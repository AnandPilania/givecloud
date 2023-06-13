<?php

namespace Ds\Http\Controllers;

use Carbon\Carbon;
use Ds\Domain\Commerce\Currency;
use Ds\Domain\Settings\Integrations\Config\GoCardlessIntegrationSettingsConfig;
use Ds\Domain\Settings\Integrations\Config\PayPalIntegrationSettingsConfig;
use Ds\Domain\Settings\Integrations\Config\PaySafeIntegrationSettingsConfig;
use Ds\Domain\Shared\DataTable;
use Ds\Enums\ProductType;
use Ds\Enums\RecurringPaymentProfileStatus;
use Ds\Enums\StockAdjustmentState;
use Ds\Enums\StockAdjustmentType;
use Ds\Models\Category;
use Ds\Models\Email;
use Ds\Models\Media;
use Ds\Models\Product;
use Ds\Models\ProductCustomField;
use Ds\Models\StockAdjustment;
use Ds\Models\TributeType;
use Ds\Models\Variant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use LiveControl\EloquentDataTable\ExpressionWithName;
use Throwable;

class ProductController extends Controller
{
    public function copy()
    {
        user()->canOrRedirect('product.add', '/jpanel/products');

        $product = Product::findOrFail(request('id'));

        // copy the original record
        $newProduct = $product->makeACopy();

        return redirect()->to("jpanel/products/edit?i={$newProduct->id}");
    }

    public function destroy()
    {
        $product = Product::findOrFail(request('id'));

        $rppCount = $product->recurringPaymentProfiles()
            ->whereNotIn('status', [
                RecurringPaymentProfileStatus::EXPIRED,
                RecurringPaymentProfileStatus::CANCELLED,
            ])->count();

        if ($rppCount) {
            $this->flash->error(
                "This product cannot be deleted. There are $rppCount active or suspended recurring " .
                'payment profiles associated with it.'
            );

            return redirect()->to("jpanel/products/edit?i={$product->id}");
        }

        // can the user delete this product?
        $product->userCanOrRedirect('edit', '/jpanel/products');
        $product->is_deleted = 1;
        $product->deleted_at = now();
        $product->deleted_by = user('id');
        $product->save();

        return redirect()->to('jpanel/products');
    }

    public function restore()
    {
        $product = Product::withTrashed()->findOrFail(request('id'));

        // can the user restore this product?
        $product->userCanOrRedirect('edit', '/jpanel/products');
        $product->is_deleted = 0;
        $product->deleted_at = null;
        $product->deleted_by = null;
        $product->save();

        return redirect()->to("jpanel/products/edit?i={$product->id}");
    }

    public function index()
    {
        user()->canOrRedirect('product.view');

        $__menu = 'products.all';

        $templates = \Ds\Models\Product::templates()->get();

        pageSetup('Sell & Fundraise', 'jpanel');

        return $this->getView('products/index', compact('__menu', 'templates'));
    }

    public function index_ajax()
    {
        // deny permission
        if (! user()->can('product.view')) {
            return false;
        }

        $products = $this->_baseQueryWithFilters();

        /* NEW PRODUCT LIST */
        // generate data table
        $dataTable = new DataTable($products->with('variants.file', 'categories.parent.parent'), [
            'name',
            'author',
            'goalamount',
            'code',
            new ExpressionWithName('author', 'col5'),
            'media_id',
            'isenabled',
            'show_in_pos',
            'permalink',
            'lock_count',
            'id',
            'base_currency',
        ]);

        $dataTable->setFormatRowFunction(function ($product) {
            $options = [];
            $product->variants->each(function ($variant) use (&$options) {
                $options[] = '<span style="margin:1px;" class="label label-default label-outline">' . (($variant->isshippable) ? '<i class="fa fa-truck"></i> ' : '') . (($variant->file) ? '<i class="fa fa-file"></i> ' : '') . (($variant->membership_id) ? '<i class="fa fa-heart"></i> ' : '') . (($variant->variantname) ? e($variant->variantname) : 'Default Option') . '</span>';
            });

            $categories = [];
            $product->categories->each(function ($cat) use (&$categories) {
                if ($cat->parent && $cat->parent->parent) {
                    $categories[] = $cat->parent->parent->name . ' > ' . $cat->parent->name . ' > ' . $cat->name;
                } elseif ($cat->parent) {
                    $categories[] = $cat->parent->name . ' > ' . $cat->name;
                } else {
                    $categories[] = $cat->name;
                }
            });

            return [
                dangerouslyUseHTML(
                    '<a class="meta-img" href="/jpanel/products/edit?i=' . e($product->id) . '">' .
                        '<div class="avatar-xl" style="background-image:url(\'' . e(media_thumbnail($product)) . '\');">' . (($product->lock_count > 0) ? '<div class="dot dot-primary"><i class="fa fa-lock fa-fw"></i></div>' : '') . '</div>' .
                    '</a>' .
                    '<div class="meta-desc">' .
                        '<div class="meta-pre">' . e($product->code) . '</div>' .
                        '<div class="title"><a href="/jpanel/products/edit?i=' . e($product->id) . '">' . e($product->name) . '</a></div>' .
                        '<div class="meta-post hidden-xs hidden-sm">' .
                            e(implode(', ', $categories)) .
                        '</div>' .
                    '</div>'
                ),
                dangerouslyUseHTML(implode('', $options)),
                dangerouslyUseHTML('<a target="_blank" href="' . e(secure_site_url($product->permalink)) . '">' . e($product->permalink) . '</a>'),
                e($product->author),
                dangerouslyUseHTML(($product->goalamount) ? '<span class="title">' . e(money($product->goal_progress)) . '</span>' . '<div class="progress"><div class="progress-bar progress-info" style="width:' . e($product->goal_progress_percent) . '%;"></div></div>' : ''),
            ];
        });

        // return datatable JSON
        return response($dataTable->make());
    }

    public function save()
    {
        // require the form
        if (empty(request()->post())) {
            return redirect()->to('jpanel/products');
        }

        // create record if it doesn't already exist
        if (request()->filled('id') && is_numeric(request('id'))) {
            $productModel = Product::findOrFail(request('id'));
        } else {
            $productModel = new Product;
            $productModel->save();
        }

        // product model
        $productModel->code = request('code');
        $productModel->name = request('name');
        $productModel->author = request('author');
        $productModel->permalink = request('permalink');
        $productModel->media_id = Media::exists(request('photo_id')) ? request('photo_id') : null;
        // $productModel->isdonation         = (request('isdonation') == 1);
        $productModel->summary = request('summary');
        $productModel->description = request('description');
        $productModel->base_currency = request('base_currency');
        $productModel->isenabled = (request('isenabled') == 1);
        $productModel->show_in_pos = (request('show_in_pos') == 1);
        $productModel->isfeatured = (request('isfeatured') == 1);
        $productModel->isnew = (request('isnew') == 1);
        $productModel->isclearance = (request('isclearance') == 1);
        // $productModel->isrecurring        = (request('isrecurring') == 1);
        // $productModel->recurringinterval  = request('recurringinterval');
        $productModel->istribute = (request('istribute') == 1);
        $productModel->isfblike = (request('isfblike') == 1);
        $productModel->allow_check_in = (request('allow_check_in') == 1);
        $productModel->outofstock_allow = (request('outofstock_allow') == 1);
        $productModel->outofstock_message = request('outofstock_message');
        $productModel->limit_sales = request('limit_sales');
        $productModel->add_to_label = request('add_to_label');
        $productModel->publish_start_date = (request()->filled('publish_start_date')) ? Carbon::createFromFormat('M j, Y', toUtc(request('publish_start_date'))) : null;
        $productModel->publish_end_date = (request()->filled('publish_end_date')) ? Carbon::createFromFormat('M j, Y', toUtc(request('publish_end_date'))) : null;
        $productModel->goalamount = request('goalamount');
        $productModel->goal_progress_offset = request('goal_progress_offset');
        $productModel->goal_use_dpo = (request('goal_use_dpo') == 1);
        $productModel->goal_deadline = (request()->filled('goal_deadline')) ? Carbon::createFromFormat('M j, Y', request('goal_deadline')) : null;
        $productModel->designation_options = json_decode(request('designation_options'), true);
        $productModel->dpo_nocalc = request('dpo_nocalc');
        $productModel->email_notify = request('email_notify');
        // $productModel->recurring_with_dpo = (request('isrecurring') && sys_get('rpp_donorperfect') ? 1 : 0);
        $productModel->alt_button_label = request('alt_button_label');
        $productModel->alt_button_url = request('alt_button_url');
        $productModel->is_tax_receiptable = (request('is_tax_receiptable') == 1);
        $productModel->is_dcc_enabled = (request('is_dcc_enabled') == 1);
        $productModel->taxcloud_tic_id = request('taxcloud_tic_id');
        $productModel->template_suffix = request('template_suffix');
        $productModel->ach_only = (request('ach_only') == 1);
        $productModel->hide_price = (request('hide_price') == 1);
        $productModel->hide_qty = (request('hide_qty') == 1);
        $productModel->notes = request('notes');

        // price
        // if (request('isdonation') == 1) {
        //  $productModel->min_price                           = request('min_price');
        //  $productModel->hide_price                          = false;
        // } else {
        //  $productModel->min_price                           = null;
        //  $productModel->hide_price                          = (request('hide_price') == 1);
        // }

        // tribute
        $productModel->allow_tributes = request('allow_tributes');
        $productModel->allow_tribute_notification = request('allow_tribute_notification');
        $productModel->tribute_type_ids = request('tribute_type_ids');

        /* DP data */
        if (dpo_is_enabled()) {
            collect([
                'meta1',
                'meta2',
                'meta3',
                'meta4',
                'meta5',
                'meta6',
                'meta7',
                'meta8',
                'meta9',
                'meta10',
                'meta11',
                'meta12',
                'meta13',
                'meta14',
                'meta15',
                'meta16',
                'meta17',
                'meta18',
                'meta19',
                'meta20',
                'meta21',
                'meta22',
                'meta23',
            ])->each(function ($key) use ($productModel) {
                $productModel->{$key} = request($key) ?: null;
            });
        } else {
            $productModel->meta1 = request('gl_code');
        }

        if ($metadata = request('metadata')) {
            $productModel->metadata($metadata);
        }

        // save
        $productModel->save();

        // save categories
        $productModel->categories()->sync(
            collect(request('category'))->reject(function ($category) {
                return empty($category);
            })
        );

        // update taxes
        db_query(sprintf('DELETE FROM producttaxproduct WHERE productid = %d; ', db_real_escape_string($productModel->id)));
        if (request('taxids')) {
            foreach (request('taxids') as $i => $v) {
                db_query(sprintf('INSERT INTO producttaxproduct (productid, taxid) VALUES (%d,%d)', db_real_escape_string($productModel->id), db_real_escape_string($v)));
            }
        }

        if (request('variant_json')) {
            $form_variants = json_decode(request('variant_json'));

            // loop over each variant
            foreach ($form_variants as $form_variant) {
                // deletions
                if ($form_variant->_is_deleted ?? false) {
                    if ($variant = Variant::find($form_variant->id)) {
                        $variant->delete();
                    }

                    continue;
                }

                // create or find existing variant
                if ($form_variant->_is_new ?? false) {
                    $variant = new Variant;
                    $variant->productid = $productModel->id;
                } else {
                    $variant = Variant::find($form_variant->id);
                    if (! $variant) {
                        continue;
                    }
                }

                // save form data
                $variant->variantname = $form_variant->variantname;
                $variant->sequence = $form_variant->sequence;
                $variant->isdefault = $form_variant->isdefault ?? false;
                $variant->billing_period = $form_variant->billing_period;
                $variant->is_donation = $form_variant->is_donation;
                $variant->billing_starts_on = fromUtc($form_variant->billing_starts_on);
                $variant->billing_ends_on = fromUtc($form_variant->billing_ends_on);
                $variant->total_billing_cycles = $form_variant->total_billing_cycles ?: null;
                $variant->price_presets = $form_variant->price_presets;
                $variant->price_minimum = $form_variant->price_minimum;
                $variant->price = $form_variant->price;
                $variant->saleprice = $form_variant->saleprice;
                $variant->cost = $form_variant->cost;
                $variant->sku = $form_variant->sku === '' ? null : $form_variant->sku;
                $variant->fair_market_value = $form_variant->fair_market_value;
                $variant->isshippable = $form_variant->isshippable;
                $variant->is_shipping_free = $form_variant->is_shipping_free;
                $variant->weight = $form_variant->weight;
                $variant->quantityrestock = $form_variant->quantityrestock ?? null;
                $variant->shipping_expectation_threshold = $form_variant->shipping_expectation_threshold ?? null;
                $variant->shipping_expectation_over = $form_variant->shipping_expectation_over ?: null;
                $variant->shipping_expectation_under = $form_variant->shipping_expectation_under ?: null;
                $variant->membership_id = $form_variant->membership_id;

                $metadata = Arr::only((array) $form_variant->metadata, [
                    'redirects_to',
                    'dp_gl_code',
                    'dp_solicit',
                    'dp_subsolicit',
                    'dp_campaign',
                    'dp_gift_narrative',
                    'dp_ty_letter_no',
                    'dp_fair_market_value',
                    'dp_acknowledgepref',
                    'dp_no_calc',
                    'meta9', 'meta10', 'meta11', 'meta12', 'meta13', 'meta14', 'meta15',
                    'meta16', 'meta17', 'meta18', 'meta19', 'meta20', 'meta21', 'meta22',
                ]);

                $metadata = collect($metadata)->map(function ($value) {
                    return $value ?: null;
                });

                $variant->metadata($metadata->all());
                $variant->save();

                if ($form_variant->_update_quantity == true) {
                    $adjustment = new StockAdjustment;
                    $adjustment->type = StockAdjustmentType::PHYSICAL_COUNT;
                    $adjustment->variant_id = $variant->id;
                    $adjustment->state = StockAdjustmentState::IN_STOCK;
                    $adjustment->quantity = $form_variant->quantity;
                    $adjustment->occurred_at = now();
                    $adjustment->user_id = user('id');
                    $adjustment->save();
                }

                $media = collect($form_variant->media)
                    ->filter(function ($media) {
                        return property_exists($media, 'id');
                    });

                if (count($media)) {
                    $variant->media()->sync($media->pluck('id')->all());

                    foreach ($media as $data) {
                        Media::where('id', $data->id)
                            ->update(['caption' => $data->caption]);
                    }
                }

                if (feature('edownloads')) {
                    try {
                        $inputHasDownloadFile = ($form_variant->file) ? true : false;
                        $variantHasExisitingFile = ($variant->file) ? true : false;
                        $variantRetainsExistingFile = ($variantHasExisitingFile && (($form_variant->file->type == 'file' && $variant->file->fileid == $form_variant->file->fileid) || ($form_variant->file->type == 'external' && $variant->file->external_resource_uri == $form_variant->file->external_resource_uri))) ? true : false;

                        if ($variantHasExisitingFile && ! $variantRetainsExistingFile) {
                            $variant->file->delete();
                        }
                        if ($inputHasDownloadFile) {
                            if ($variantRetainsExistingFile) {
                                $file = $variant->file;
                            } else {
                                $file = new \Ds\Models\VariantFile;
                                $file->inventoryid = $variant->id;
                                if ($form_variant->file->type == 'file') {
                                    $file->fileid = $form_variant->file->fileid;
                                } else {
                                    $file->external_resource_uri = $form_variant->file->external_resource_uri;
                                }
                            }
                            $file->description = $form_variant->file->description;
                            $file->expiry_time = $form_variant->file->expiry_time;
                            $file->download_limit = $form_variant->file->download_limit;
                            $file->address_limit = $form_variant->file->address_limit;
                            $file->save();
                        }
                    } catch (Throwable $e) {
                        // do nothing
                    }
                }

                // linked variants
                $linked_variants = $form_variant->linked_variants ?? [];
                foreach ($linked_variants as $linked_variant_id => $linked_variant) {
                    if ($variant->id == $linked_variant_id) {
                        unset($linked_variants[$linked_variant_id]);
                    }
                }
                if (count($linked_variants) > 0) {
                    $variants_to_link = [];
                    foreach ($linked_variants as $linked_variant) {
                        $variants_to_link[] = [
                            'linked_variant_id' => $linked_variant->id,
                            'qty' => $linked_variant->pivot->qty ?? $linked_variant->qty,
                            'price' => $linked_variant->pivot->price ?? $linked_variant->price,
                        ];
                    }
                    $variant->linkedVariants()->sync($variants_to_link); // add all new links
                } else {
                    $variant->linkedVariants()->detach();
                }
            }
        }

        // Ensure there is a default variant
        $productModel->load('defaultVariant');
        if (! $productModel->defaultVariant) {
            $defaultVariant = $productModel->variants->first();
            $defaultVariant->isdefault = true;
            $defaultVariant->save();
        }

        // update fields
        if (request('productfields')) {
            foreach (request('productfields') as $field) {
                if (is_numeric($field['_isnew']) && $field['_isnew'] == 1 && trim($field['name']) !== '') {
                    $fieldModel = new ProductCustomField;
                    $fieldModel->productid = $productModel->id;
                } else {
                    $fieldModel = ProductCustomField::find($field['id']);
                }

                // skip if there's no model
                if (! $fieldModel) {
                    continue;
                }

                // delete
                if (isset($field['_isdelete']) && is_numeric($field['_isdelete']) && $field['_isdelete'] == 1) {
                    $fieldModel->delete();

                    continue;
                }

                // update the field
                $fieldModel->sequence = $field['sequence'];
                $fieldModel->type = $field['type'];
                $fieldModel->name = $field['name'];
                $fieldModel->isrequired = ($field['isrequired'] == 1);
                $fieldModel->format = $field['format'] ?? null;
                $fieldModel->options = $field['options'];
                $fieldModel->default_value = $field['default_value'] ?: null;
                $fieldModel->map_to_product_meta = $field['map_to_product_meta'];
                $fieldModel->body = $field['body'];
                $fieldModel->hint = $field['hint'];

                if (array_key_exists('choices', $field)) {
                    $fieldModel->choices = (new Collection($field['choices']))
                        ->map(function ($choice) {
                            return [
                                'label' => $choice['label'],
                                'value' => $choice['value'],
                            ];
                        })->values();
                }

                $fieldModel->save();
            }
        }

        // if saving as a template for the first time
        if (request('create_template')) {
            $template = $productModel->makeACopy();
            $template->template_name = request('template_name');
            $template->type = ProductType::TEMPLATE;
            $template->isenabled = false;
            $template->show_in_pos = false;
            $template->save();

            $this->flash->success('Template created.');

            return redirect()->to('jpanel/products/edit?s&i=' . $template->id);
        }

        if (request('save_template')) {
            $productModel->template_name = request('template_name');
            $productModel->isenabled = false;
            $productModel->show_in_pos = false;
            $productModel->save();

            $this->flash->success('Template saved.');

            return redirect()->to('jpanel/products/edit?s&i=' . $productModel->id);
        }

        if (request('product_as_homepage')) {
            if ($productModel->code && sys_get('product_as_homepage') !== $productModel->code) {
                sys_set(['product_as_homepage' => $productModel->code]);

                $productModel->isenabled = true;
                $productModel->save();
            }
        } elseif ($productModel->code && sys_get('product_as_homepage') === $productModel->code) {
            sys_set(['product_as_homepage' => null]);
        }

        $this->flash->success('Item saved.');

        return redirect()->to('jpanel/products/edit?s&i=' . $productModel->id);
    }

    public function view(GoCardlessIntegrationSettingsConfig $gocardlessIntegration, PayPalIntegrationSettingsConfig $paypalIntegration, PaySafeIntegrationSettingsConfig $paysafeIntegration)
    {
        $__menu = 'products.all';

        if (request('i')) {
            $productModel = Product::withTrashed()
                ->with([
                    'variants' => function ($qry) {
                        $qry->select('productinventory.*')
                            ->with('membership', 'file.file', 'linkedVariants.product', 'media', 'metadataRelation');
                    },
                ])->where('id', request('i'))
                ->firstOrFail();
            $isNew = 0;
            $title = $productModel->name;
        } else {
            $productModel = Product::newWithPermission();
            $isNew = 1;
            $title = 'Add Product';
        }

        pageSetup($title, 'jpanel');

        if (! $productModel->exists && request('i')) {
            $this->flash->error('Product ID (' . request('i') . ') does not exist.');

            return redirect()->to('jpanel/products');
        }

        /* membership ids required */
        $membership_ids_required = membership_access_get_by_parent('product', $r['id'] ?? null);
        $membership_list = [];
        if (count($membership_ids_required) > 0) {
            foreach ($membership_ids_required as $membership_id) {
                $membership_list[] = membership_get($membership_id);
            }
        }

        /* product fields */
        $qFields = ProductCustomField::where('productid', request('i'))->orderBy('sequence')->get();

        /* category linkages */
        $query = sprintf(
            'SELECT c.name, c.id
                          FROM `productcategorylink` l
                          INNER JOIN `productcategory` c ON c.id = l.categoryid
                          WHERE l.productid = %d',
            request('i')
        );
        $qryC = db_query($query);
        $categories = [];
        while ($z = db_fetch_assoc($qryC)) {
            array_push($categories, $z['id']);
        }

        /* taxes */
        $taxes = DB::select(
            'SELECT t.*, (CASE WHEN tp.id IS NOT NULL THEN 1 ELSE 0 END) AS isselected
                FROM producttax t
                LEFT JOIN producttaxproduct tp ON tp.taxid = t.id AND tp.productid = ?
                WHERE t.deleted_at IS NULL',
            [request('i')]
        );

        // tribute types
        $tributeTypes = TributeType::active()->get();

        // if taxcloud, provide list of tics
        $tics = null;
        if (sys_get('taxcloud_api_key')) {
            $tics = app('cache')->store('app')->remember('taxcloud-tics', now()->addDays(30), function () {
                return app('taxCloud')->getTICs();
            });
        }

        // number of recurring payment profiles associated with product
        $numRecurringPaymentProfiles = \Ds\Models\RecurringPaymentProfile::query()
            ->where('product_id', '=', request('i'))
            ->whereNotIn('status', [
                RecurringPaymentProfileStatus::EXPIRED,
                RecurringPaymentProfileStatus::CANCELLED,
            ])->count();

        $memberships = \Ds\Models\Membership::orderBy('name')->get();

        $schemas = app('theme')->getTemplateMetadata('product');
        $content_editor_classes = app('theme')->getContentEditorClasses($schemas);

        $tinymce_classes = [
            trim('template--product-' . $productModel->template_suffix, '-'),
            "product-{$productModel->id}",
        ];

        $currencies = Currency::getLocalCurrencies();
        $gocardlessInstalled = $gocardlessIntegration->isInstalled();
        $paypalInstalled = $paypalIntegration->isInstalled();
        $paysafeInstalled = $paysafeIntegration->isInstalled();

        $productEmails = $productModel->emails()->get();
        $templates = Product::getTemplates();

        $variantEmails = Email::query()
            ->whereHas('variants', function (Builder $query) use ($productModel) {
                $query->where('productid', $productModel->getKey());
            })->with(['variants' => function (MorphToMany $query) use ($productModel) {
                $query->where('productid', $productModel->getKey());
            }])->get();

        return $this->getView('products/view', compact(
            '__menu',
            'productModel',
            'isNew',
            'title',
            'membership_ids_required',
            'membership_list',
            'qFields',
            'categories',
            'taxes',
            'tributeTypes',
            'tics',
            'numRecurringPaymentProfiles',
            'memberships',
            'schemas',
            'content_editor_classes',
            'tinymce_classes',
            'currencies',
            'gocardlessInstalled',
            'paypalInstalled',
            'paysafeInstalled',
            'productEmails',
            'variantEmails',
            'templates',
        ));
    }

    public function validateSku()
    {
        $matches = Product::whereCode(request('code'))
            ->where('id', '!=', (int) request('product_id'))
            ->withTrashed()
            ->count();

        if ($matches === 0) {
            echo '{"valid":true}';
        } else {
            echo '{"valid":false}';
        }

        exit;
    }

    public function create_from_template($template_id)
    {
        $template = Product::templates()->where('id', $template_id)->first();
        if (! $template) {
            $this->flash->error('Template not found.');

            return redirect()->back();
        }
        $newProduct = $template->makeACopy();
        $newProduct->code = null;
        $newProduct->name = trim(str_replace('(COPY)', '', $newProduct->name));
        $newProduct->template_name = null;
        $newProduct->type = null;
        $newProduct->save();
        $this->flash->success("Item created from '{$template->template_name}' template.");

        return redirect()->to('jpanel/products/edit?i=' . $newProduct->id);
    }

    /**
     * Build a base query based on request filter params.
     * Allows us to reuse this for datatables, csv, etc...
     */
    private function _baseQueryWithFilters()
    {
        // base query
        $products = Product::withLockCount();

        // /////////////////////
        // // FILTERS
        // /////////////////////

        // category
        if (request('fc')) {
            $products->whereRaw('id IN (SELECT productid FROM productcategorylink WHERE categoryid = ?)', [request('fc')]);
        }

        // filter/author
        if (request('fa')) {
            $products->where('author', request('fa'));
        }

        // keyword search (name, email)
        if (request('fb')) {
            $products->where(function ($qry) {
                $qry->where('name', 'like', '%' . request('fb') . '%')
                    ->orWhere('code', 'like', '%' . request('fb') . '%')
                    ->orWhere('summary', 'like', '%' . request('fb') . '%')
                    ->orWhereHas('variants', function ($q) {
                        $q->where('variantname', 'like', '%' . request('fb') . '%');
                    });
            });
        }

        // active/deleted
        if (request('fd') == 0) {
            $products->withoutDonationForms()->withoutTemplates();
        } elseif (request('fd') == 1) {
            $products->withoutDonationForms()->withoutTemplates()->withTrashed()->where('is_deleted', 1);
        } elseif (request('fd') == 2) {
            $products->templates();
        }

        if (request('dp_sync') === '1') {
            $products->where(function (Builder $query) {
                $query->whereHas('metadataRelation', function (Builder $query) {
                    $query->where('key', 'dp_syncable')->where('value', 1);
                })->orWhere(function (Builder $query) {
                    $query->whereDoesntHave('metadataRelation', function (Builder $query) {
                        $query->where('key', 'dp_syncable');
                    });
                });
            });
        }

        if (request('dp_sync') === '0') {
            $products->whereHas('metadataRelation', function (Builder $query) {
                $query->where('key', 'dp_syncable')->where('value', 0);
            });
        }

        if (request('is_shippable') === 'Yes') {
            $products->whereHas('variants', function ($q) {
                $q->where('isshippable', true);
            });
        } elseif (request('is_shippable') === 'No') {
            $products->whereDoesntHave('variants', function ($q) {
                $q->where('isshippable', true);
            });
        }

        return $products;
    }

    public function export()
    {
        // deny permission
        user()->canOrRedirect('product');

        return response()->streamDownload(function () {
            $outstream = fopen('php://output', 'w');

            $headers = [
                'Code', 'Name', 'Summary', 'Permalink', 'Categories', 'Goal Amount', 'Show on Website', 'Show in POS',
                'Is Featured', 'Is New', 'Is DCC Enabled', 'Is Tax Receiptable', 'Allow Out of Stock',  'Limit # of Sales',
                'Allow Tributes', 'Enabled Sales Tax', 'Staff Notifications',
            ];

            if (dpo_is_enabled()) {
                array_push(
                    $headers,
                    'Sync this Item',
                    'DP General Ledger',
                    'DP Campaign',
                    'DP Solicitation',
                    'DP Sub Solicitation',
                    'DP Gift Type',
                    'DP TY Letter Code',
                    'DP Fair Mkt. Value',
                    'DP Gift Memo',
                    'DP Acknowledge Preference',
                    'DP NoCalc'
                );
            }

            array_push($headers, 'Created', 'Last Modified');

            fputcsv($outstream, $headers, ',', '"');

            $products = $this->_baseQueryWithFilters()->with(['taxes', 'categories']);

            $products->orderBy('id')->lazy(250)->each(function ($product) use (&$outstream) {
                // basic fields
                $row = [
                    $product->code,
                    $product->name,
                    $product->summary,
                    $product->permalink,
                    $product->categories->pluck('name')->implode(', '),
                    $product->goalamount == 0 ? null : money($product->goalamount, $product->base_currency ?? currency()->code),
                    $product->isenabled ? 'Yes' : 'No',
                    $product->show_in_pos ? 'Yes' : 'No',
                    $product->isfeatured ? 'Yes' : 'No',
                    $product->isnew ? 'Yes' : 'No',
                    $product->is_dcc_enabled ? 'Yes' : 'No',
                    $product->is_tax_receiptable ? 'Yes' : 'No',
                    $product->outofstock_allow ? 'Yes' : 'No',
                    $product->limit_sales === 0 ? null : $product->limit_sales,
                    $product->allow_tributes ? 'Yes' : 'No',
                    $product->taxes->pluck('code')->implode(', '),
                    $product->email_notify,
                ];

                if (dpo_is_enabled()) {
                    array_push(
                        $row,
                        $product->metadata('dp_syncable') ? 'Yes' : 'No',
                        $product->meta1, // General Ledger
                        $product->meta2, // Campaign
                        $product->meta3, // Solicitation
                        $product->meta4, // Sub Solicitation
                        $product->meta5, // Gift Type
                        $product->meta7, // TY Letter Code
                        $product->meta6, // Fair Mkt. Value
                        $product->meta8, // Gift Memo
                        $product->meta23, // Acknowledge Preference
                        $product->dpo_nocalc
                    );
                }

                $row[] = toLocalFormat($product->createddatetime, 'csv');
                $row[] = toLocalFormat($product->modifieddatetime, 'csv');

                // write it
                fputcsv($outstream, $row);
            });

            fclose($outstream);
        }, 'products.csv', [
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-Description' => 'File Transfer',
            'Content-type' => 'text/csv',
            'Expires' => '0',
            'Pragma' => 'public',
        ]);
    }

    public function exportVariants()
    {
        // deny permission
        user()->canOrRedirect('product');

        return response()->streamDownload(function () {
            $outstream = fopen('php://output', 'w');

            $headers = [
                'Product Code', 'Product Name', 'SKU', 'Variant Name', 'Is Donation', 'Price Presets',
                'Price', 'Sale Price', 'Fair Market Value', 'Minimum Price', 'Billing Period',
                'Billing Starts On', 'Billing Ends On', 'Total Billing Cycles', 'Is Default',
                'Is Shippable', 'Is Shipping Free', 'Shipping Expectation', 'Quantity', 'Group / Membership',
            ];

            if (dpo_is_enabled()) {
                array_push(
                    $headers,
                    'DP General Ledger Override',
                    'DP Campaign Override',
                    'DP Solicitation Override',
                    'DP Sub Solicitation Override',
                    'DP TY Letter Code Override',
                    'DP Fair Mkt. Value Override',
                    'DP Gift Memo Override',
                    'DP Acknowledge Preference Override',
                    'DP NoCalc Override'
                );
            }

            fputcsv($outstream, $headers, ',', '"');

            $products = $this->_baseQueryWithFilters();

            $variants = Variant::whereIn('productid', $products->pluck('id'))->with(['product', 'membership', 'metadataRelation']);

            $variants->orderBy('productid')->lazy(250)->each(function ($variant) use (&$outstream) {
                $currency = $variant->product->base_currency ?? currency()->code;
                // basic fields
                $row = [
                    $variant->product->code,
                    $variant->product->name,
                    $variant->sku,
                    $variant->variantname,
                    $variant->is_donation ? 'Yes' : 'No',
                    $variant->price_presets,
                    $variant->price,
                    is_numeric($variant->saleprice) ? money($variant->saleprice, $currency) : null,
                    is_numeric($variant->fair_market_value) ? money($variant->fair_market_value, $currency) : null,
                    is_numeric($variant->price_minimum) ? money($variant->price_minimum, $currency) : null,
                    $variant->billing_period,
                    $variant->billing_starts_on,
                    $variant->billing_ends_on,
                    $variant->total_billing_cycles,
                    $variant->isdefault ? 'Yes' : 'No',
                    $variant->isshippable ? 'Yes' : 'No',
                    $variant->isshippable ? ($variant->is_shipping_free ? 'Yes' : 'No') : null,
                    $variant->shipping_expectation,
                    $variant->quantity,
                    $variant->membership ? $variant->membership->name : null,
                ];

                if (dpo_is_enabled()) {
                    array_push(
                        $row,
                        $variant->metadata->dp_gl_code,
                        $variant->metadata->dp_campaign,
                        $variant->metadata->dp_solicit,
                        $variant->metadata->dp_subsolicit,
                        $variant->metadata->dp_ty_letter_no,
                        $variant->metadata->dp_fair_market_value,
                        $variant->metadata->dp_gift_narrative,
                        $variant->metadata->dp_acknowledgepref,
                        $variant->metadata->dp_no_calc,
                    );
                }

                // write it
                fputcsv($outstream, $row);
            });

            fclose($outstream);
        }, 'variants.csv', [
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-Description' => 'File Transfer',
            'Content-type' => 'text/csv',
            'Expires' => '0',
            'Pragma' => 'public',
        ]);
    }
}
