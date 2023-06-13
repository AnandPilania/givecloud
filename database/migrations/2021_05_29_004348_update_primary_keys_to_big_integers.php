<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdatePrimaryKeysToBigIntegers extends Migration
{
    /** @var array */
    private $tables = [
        // tables without any foreign key constraints
        'aliases' => ['integer'],
        'audits' => ['unsignedInteger'],
        'autologin_tokens' => ['integer'],
        'categories' => ['unsignedInteger11'],
        'configs' => ['integer'],
        'emails' => ['integer'],
        'failed_jobs' => ['unsignedInteger'],
        'fundraising_page_members' => ['unsignedInteger11'],
        'fundraising_page_reports' => ['unsignedInteger11'],
        'group_account' => ['unsignedInteger'],
        'hook_deliveries' => ['integer'],
        'imports' => ['unsignedInteger'],
        'kiosk_sessions' => ['unsignedInteger'],
        'member_login' => ['integer'],
        'membership_access' => ['integer'],
        'membership_promocodes' => ['unsignedInteger'],
        'migrations_data' => ['unsignedInteger'],
        'migrations' => ['unsignedInteger'],
        'nodecontent' => ['integer'],
        'order_promocodes' => ['unsignedInteger'],
        'payment_option' => ['integer'],
        'pledges' => ['unsignedInteger11'],
        'post' => ['integer'],
        'productcategorylink' => ['integer'],
        'productinventoryfiles' => ['integer'],
        'productorderitemfield' => ['integer'],
        'productorderitemfiles' => ['integer'],
        'productorderitemtax' => ['integer'],
        'productpromocodecategory' => ['integer'],
        'productpromocodeproduct' => ['integer'],
        'producttaxproduct' => ['integer'],
        'producttaxregion' => ['integer'],
        'resumable_conversations' => ['unsignedInteger'],
        'settings' => ['integer'],
        'shipping_value' => ['integer'],
        'sponsors' => ['unsignedInteger'],
        'sponsorship_segments' => ['integer'],
        'tax_receipt_line_items' => ['unsignedInteger'],
        'ticket_check_in' => ['integer'],
        'timelines' => ['integer'],
        'tributes' => ['unsignedInteger'],
        'user_logins' => ['integer'],
        'variant_variant' => ['integer'],

        // tables with foreign key constraints

        'account_types' => ['integer', [
            ['member', 'account_type_id', 'nullable' => true, 'member_ibfk_4'],
            ['productorder', 'account_type_id', 'nullable' => true, 'productorder_ibfk_3'],
        ]],

        'assets' => ['integer', [
            ['assets', 'parent_id', 'nullable' => true, 'assets_ibfk_1', 'onDelete' => 'cascade'],
        ]],

        'conversations' => ['unsignedInteger', [
            ['conversations_pivot', 'conversation_id', 'nullable' => false, 'conversations_pivot_ibfk_1'],
            ['resumable_conversations', 'conversation_id', 'nullable' => false, 'resumable_conversations_ibfk_1'],
        ]],

        'conversation_recipients' => ['unsignedInteger', [
            ['conversations_pivot', 'conversation_recipient_id', 'nullable' => false, 'conversations_pivot_ibfk_2'],
        ]],

        'files' => ['integer', [
            ['productinventoryfiles', 'fileid', 'nullable' => true, 'productinventoryfiles_ibfk_1'],
            ['productorderitemfiles', 'fileid', 'nullable' => true, 'productorderitemfiles_ibfk_1'],
        ]],

        'fundraising_pages' => ['unsignedInteger11', [
            ['fundraising_page_members', 'fundraising_page_id', 'nullable' => false, 'fundraising_page_members_ibfk_1'],
            ['fundraising_page_reports', 'fundraising_page_id', 'nullable' => false, 'fundraising_page_reports_ibfk_1'],
            ['productorderitem', 'fundraising_page_id', 'nullable' => true, 'productorderitem_ibfk_9'],
        ]],

        'hooks' => ['integer', [
            ['hook_deliveries', 'hook_id', 'nullable' => false, 'hook_deliveries_ibfk_1'],
        ]],

        'kiosks' => ['unsignedInteger11', [
            ['kiosk_sessions', 'kiosk_id', 'nullable' => false, 'kiosk_sessions_ibfk_1'],
        ]],

        'media' => ['unsignedInteger11', [
            ['categories', 'media_id', 'nullable' => true, 'categories_ibfk_1'],
            ['fundraising_pages', 'photo_id', 'nullable' => true, 'fundraising_pages_ibfk_4'],
            ['fundraising_pages', 'team_photo_id', 'nullable' => true, 'fundraising_pages_ibfk_5'],
            ['imports', 'media_id', 'nullable' => true],
            ['media', 'parent_id', 'nullable' => true, 'media_ibfk_1', 'onDelete' => 'cascade'],
            ['nodecontent', 'featured_image_id', 'nullable' => true, 'nodecontent_ibfk_5'],
            ['nodecontent', 'alt_image_id', 'nullable' => true, 'nodecontent_ibfk_6'],
            ['post', 'media_id', 'nullable' => true, 'post_ibfk_3', 'onDelete' => 'set null'],
            ['post', 'featured_image_id', 'nullable' => true, 'post_ibfk_4', 'onDelete' => 'set null'],
            ['post', 'alt_image_id', 'nullable' => true, 'post_ibfk_5', 'onDelete' => 'set null'],
            ['posttype', 'media_id', 'nullable' => true, 'posttype_ibfk_1', 'onDelete' => 'set null'],
            ['product', 'media_id', 'nullable' => true, 'product_ibfk_3', 'onDelete' => 'set null'],
            ['productcategory', 'media_id', 'nullable' => true, 'productcategory_ibfk_2', 'onDelete' => 'set null'],
            ['sponsorship', 'media_id', 'nullable' => true, 'sponsorship_ibfk_3', 'onDelete' => 'set null'],
        ]],

        'member' => ['integer', [
            ['fundraising_pages', 'member_organizer_id', 'nullable' => false, 'fundraising_pages_ibfk_3'],
            ['fundraising_page_members', 'member_id', 'nullable' => false, 'fundraising_page_members_ibfk_2'],
            ['fundraising_page_reports', 'member_id', 'nullable' => true, 'fundraising_page_reports_ibfk_2'],
            ['member', 'referred_by', 'nullable' => true, 'member_referred_by_ibfk_1'],
            ['member_login', 'member_id', 'nullable' => true, 'member_login_ibfk_2', 'onDelete' => 'cascade'],
            ['payments', 'source_account_id', 'nullable' => true, 'payments_ibfk_2'],
            ['payment_methods', 'member_id', 'nullable' => false, 'payment_methods_ibfk_2', 'onDelete' => 'cascade'],
            ['productorder', 'member_id', 'nullable' => true, 'productorder_ibfk_2'],
            ['productorder', 'referred_by', 'nullable' => true, 'productorder_referred_by_ibfk_1'],
            ['productorderitem', 'fundraising_member_id', 'nullable' => true, 'productorderitem_ibfk_10'],
            ['recurring_payment_profiles', 'member_id', 'nullable' => false, 'recurring_payment_profiles_ibfk_4'],
            ['resumable_conversations', 'account_id', 'nullable' => true, 'resumable_conversations_ibfk_2'],
            ['tax_receipts', 'account_id', 'nullable' => true, 'onDelete' => 'cascade'],
        ]],

        'membership' => ['integer', [
            ['membership_access', 'membership_id', 'nullable' => true, 'membership_access_ibfk_1'],
            ['productinventory', 'membership_id', 'nullable' => true, 'productinventory_ibfk_3'],
        ]],

        'node' => ['integer', [
            ['nodecontent', 'nodeid', 'nullable' => false, 'nodecontent_ibfk_3', 'onDelete' => 'cascade'],
        ]],

        'payment_methods' => ['integer', [
            ['payments', 'source_payment_method_id', 'nullable' => true, 'payments_ibfk_3'],
            ['productorder', 'payment_method_id', 'nullable' => true, 'productorder_ibfk_4'],
            ['recurring_payment_profiles', 'payment_method_id', 'nullable' => true, 'recurring_payment_profiles_ibfk_3', 'onDelete' => 'set null'],
            ['transactions', 'payment_method_id', 'nullable' => true, 'transactions_ibfk_4'],
        ]],

        'payment_option_group' => ['integer', [
            ['payment_option', 'group_id', 'nullable' => true, 'payment_option_ibfk_3'],
        ]],

        'payment_providers' => ['unsignedInteger11', [
            ['productorder', 'payment_provider_id', 'nullable' => true, 'productorder_ibfk_5'],
            ['payment_methods', 'payment_provider_id', 'nullable' => false, 'payment_methods_ibfk_3'],
        ]],

        'pledge_campaigns' => ['unsignedInteger11', [
            ['virtual_events', 'campaign_id', 'nullable' => false, 'virtual_events_campaign_id_foreign', 'columnType' => 'unsignedInteger'],
        ]],

        'posttype' => ['integer', [
            ['post', 'type', 'nullable' => false, 'post_ibfk_2'],
        ]],

        'product' => ['integer', [
            ['fundraising_pages', 'product_id', 'nullable' => false, 'fundraising_pages_ibfk_1'],
            ['kiosks', 'product_id', 'nullable' => false, 'kiosks_ibfk_1'],
            ['productcategorylink', 'productid', 'nullable' => false, 'productcategorylink_ibfk_3', 'onDelete' => 'cascade'],
            ['productfields', 'productid', 'nullable' => true, 'productfields_ibfk_1'],
            ['productinventory', 'productid', 'nullable' => true, 'productinventory_ibfk_4', 'onDelete' => 'cascade'],
            ['productorderitem', 'DELETE_productid', 'nullable' => true, 'productorderitem_ibfk_2'],
            ['productpromocodeproduct', 'productid', 'nullable' => false, 'productpromocodeproduct_ibfk_2'],
            ['producttaxproduct', 'productid', 'nullable' => false, 'producttaxproduct_ibfk_1'],
            ['recurring_payment_profiles', 'product_id', 'nullable' => true, 'recurring_payment_profiles_ibfk_8'],
            ['virtual_events', 'tab_one_product_id', 'nullable' => true],
            ['virtual_events', 'tab_three_product_id', 'nullable' => true],
            ['virtual_events', 'tab_two_product_id', 'nullable' => true],
        ]],

        'productcategory' => ['integer', [
            ['productcategory', 'parent_id', 'nullable' => true, 'productcategory_ibfk_1'],
            ['productcategorylink', 'categoryid', 'nullable' => false, 'productcategorylink_ibfk_4', 'onDelete' => 'cascade'],
            ['productpromocodecategory', 'categoryid', 'nullable' => false, 'productpromocodecategory_ibfk_3', 'onDelete' => 'cascade'],
        ]],

        'productfields' => ['integer', [
            ['productorderitemfield', 'fieldid', 'nullable' => true, 'productorderitemfield_ibfk_3'],
        ]],

        'productinventory' => ['integer', [
            ['productinventoryfiles', 'inventoryid', 'nullable' => false, 'productinventoryfiles_ibfk_3', 'onDelete' => 'cascade'],
            ['productorderitem', 'productinventoryid', 'nullable' => true, 'productorderitem_ibfk_3'],
            ['productorderitem', 'original_variant_id', 'nullable' => true, 'productorderitem_ibfk_8', 'onDelete' => 'no action'],
            ['recurring_payment_profiles', 'productinventory_id', 'nullable' => true, 'recurring_payment_profiles_ibfk_7'],
            ['stock_adjustments', 'variant_id', 'nullable' => false, 'stock_adjustments_ibfk_1'],
            ['variant_variant', 'variant_id', 'nullable' => false, 'variant_variant_ibfk_1'],
            ['variant_variant', 'linked_variant_id', 'nullable' => false, 'variant_variant_ibfk_2'],
        ]],

        'productorder' => ['integer', [
            ['order_promocodes', 'order_id', 'nullable' => false, 'onDelete' => 'cascade'],
            ['payments_pivot', 'order_id', 'nullable' => true, 'payments_pivot_ibfk_2'],
            ['productorderitem', 'productorderid', 'nullable' => false, 'productorderitem_ibfk_5', 'onDelete' => 'cascade'],
            ['recurring_payment_profiles', 'productorder_id', 'nullable' => false, 'recurring_payment_profiles_ibfk_5'],
            ['tax_receipts', 'order_id', 'nullable' => true, 'tax_receipts_ibfk_1'],
            ['tax_receipt_line_items', 'order_id', 'nullable' => true],
            ['ticket_check_in', 'order_id', 'nullable' => true, 'ticket_check_in_ibfk_1'],
        ]],

        'productorderitem' => ['integer', [
            ['productorderitem', 'locked_to_item_id', 'nullable' => true, 'productorderitem_ibfk_6', 'onDelete' => 'cascade'],
            ['productorderitemfield', 'orderitemid', 'nullable' => true, 'productorderitemfield_ibfk_2', 'onDelete' => 'cascade'],
            ['productorderitemfiles', 'orderitemid', 'nullable' => false, 'productorderitemfiles_ibfk_4', 'onDelete' => 'cascade'],
            ['productorderitemtax', 'orderitemid', 'nullable' => false, 'productorderitemtax_ibfk_3', 'onDelete' => 'cascade'],
            ['recurring_payment_profiles', 'productorderitem_id', 'nullable' => false, 'recurring_payment_profiles_ibfk_6'],
            ['ticket_check_in', 'order_item_id', 'nullable' => true, 'ticket_check_in_ibfk_2'],
            ['tributes', 'order_item_id', 'nullable' => true, 'tributes_ibfk_1'],
        ]],

        'productpromocode' => ['integer', [
            ['productpromocodecategory', 'promocodeid', 'nullable' => false, 'productpromocodecategory_ibfk_4', 'onDelete' => 'cascade'],
            ['productpromocodeproduct', 'promocodeid', 'nullable' => false, 'productpromocodeproduct_ibfk_3', 'onDelete' => 'cascade'],
        ]],

        'producttax' => ['integer', [
            ['producttaxproduct', 'taxid', 'nullable' => false, 'producttaxproduct_ibfk_2'],
            ['producttaxregion', 'taxid', 'nullable' => false, 'producttaxregion_ibfk_1'],
            ['productorderitemtax', 'taxid', 'nullable' => false, 'productorderitemtax_ibfk_2'],
        ]],

        'recurring_payment_profiles' => ['integer', [
            ['payments_pivot', 'recurring_payment_profile_id', 'nullable' => true, 'payments_pivot_ibfk_4'],
            ['transactions', 'recurring_payment_profile_id', 'nullable' => true, 'transactions_ibfk_2'],
        ]],

        'region' => ['integer', [
            ['producttaxregion', 'regionid', 'nullable' => false, 'producttaxregion_ibfk_2'],
        ]],

        'segments' => ['integer', [
            ['segment_items', 'segment_id', 'nullable' => true, 'segment_items_ibfk_1'],
            ['sponsorship_segments', 'segment_id', 'nullable' => true, 'sponsorship_segments_ibfk_2'],
        ]],

        'segment_items' => ['integer', [
            ['sponsorship_segments', 'segment_item_id', 'nullable' => true, 'sponsorship_segments_ibfk_3'],
        ]],

        'shipping_method' => ['integer', [
            ['shipping_value', 'method_id', 'nullable' => true, 'shipping_value_ibfk_1'],
            ['productorder', 'shipping_method_id', 'nullable' => true, 'productorder_ibfk_1'],
        ]],

        'shipping_tier' => ['integer', [
            ['shipping_value', 'tier_id', 'nullable' => true, 'shipping_value_ibfk_2'],
        ]],

        'sponsorship' => ['integer', [
            ['sponsorship_segments', 'sponsorship_id', 'nullable' => true, 'sponsorship_segments_ibfk_1'],
        ]],

        'stock_adjustments' => ['integer', [
            ['productinventory', 'last_physical_count_id', 'nullable' => true, 'productinventory_ibfk_5'],
        ]],

        'tags' => ['unsignedInteger11', [
            ['taggables', 'tag_id', 'nullable' => false, 'taggables_ibfk_1'],
        ]],

        'tax_receipts' => ['unsignedInteger', [
            ['tax_receipt_line_items', 'tax_receipt_id', 'nullable' => false, 'onDelete' => 'cascade'],
        ]],

        'tax_receipt_templates' => ['unsignedInteger', [
            ['tax_receipt_templates', 'latest_revision_id', 'nullable' => true],
            ['tax_receipt_templates', 'parent_id', 'nullable' => true, 'onDelete' => 'cascade'],
            ['tax_receipts', 'tax_receipt_template_id', 'nullable' => true],
        ]],

        'themes' => ['integer', [
            ['assets', 'theme_id', 'nullable' => false, 'assets_ibfk_2', 'onDelete' => 'cascade'],
            ['settings', 'theme_id', 'nullable' => true, 'settings_ibfk_1', 'onDelete' => 'cascade'],
        ]],

        'transactions' => ['integer', [
            ['payments_pivot', 'transaction_id', 'nullable' => true, 'payments_pivot_ibfk_5'],
            ['tax_receipts', 'transaction_id', 'nullable' => true, 'tax_receipts_ibfk_2'],
            ['tax_receipt_line_items', 'transaction_id', 'nullable' => true],
        ]],

        'tribute_types' => ['unsignedInteger', [
            ['productorderitem', 'tribute_type_id', 'nullable' => true, 'productorderitem_ibfk_7'],
            ['tributes', 'tribute_type_id', 'nullable' => true, 'tributes_ibfk_2'],
        ]],

        'user' => ['integer', [
            ['comments', 'created_by', 'nullable' => true],
            ['comments', 'updated_by', 'nullable' => true],
            ['emails', 'created_by', 'nullable' => true, 'emails_ibfk_1'],
            ['emails', 'updated_by', 'nullable' => true, 'emails_ibfk_2'],
            ['fundraising_pages', 'created_by', 'nullable' => false, 'fundraising_pages_ibfk_6'],
            ['fundraising_pages', 'updated_by', 'nullable' => false, 'fundraising_pages_ibfk_7'],
            ['fundraising_pages', 'deleted_by', 'nullable' => true, 'fundraising_pages_ibfk_8'],
            ['kiosk_sessions', 'user_id', 'nullable' => false, 'kiosk_sessions_ibfk_2'],
            ['member', 'updated_by', 'nullable' => true, 'member_ibfk_2'],
            ['membership', 'created_by', 'nullable' => true, 'membership_ibfk_1'],
            ['membership', 'updated_by', 'nullable' => true, 'membership_ibfk_2'],
            ['nodecontent', 'createdbyuserid', 'nullable' => false, 'nodecontent_ibfk_1'],
            ['nodecontent', 'modifiedbyuserid', 'nullable' => false, 'nodecontent_ibfk_2'],
            ['payment_option', 'created_by', 'nullable' => true, 'payment_option_ibfk_1'],
            ['payment_option', 'updated_by', 'nullable' => true, 'payment_option_ibfk_2'],
            ['payment_option_group', 'created_by', 'nullable' => true, 'payment_option_group_ibfk_1'],
            ['payment_option_group', 'updated_by', 'nullable' => true, 'payment_option_group_ibfk_2'],
            ['post', 'modifiedbyuserid', 'nullable' => false, 'post_ibfk_1'],
            ['product', 'createdbyuserid', 'nullable' => false, 'product_ibfk_1'],
            ['product', 'modifiedbyuserid', 'nullable' => true, 'product_ibfk_2'],
            ['productfields', 'deleted_by', 'nullable' => true, 'productfields_ibfk_2'],
            ['productinventory', 'quantitymodifiedbyuserid', 'nullable' => true, 'productinventory_ibfk_2'],
            ['productpromocode', 'createdbyuserid', 'nullable' => false, 'productpromocode_ibfk_1'],
            ['productpromocode', 'modifiedbyuserid', 'nullable' => false, 'productpromocode_ibfk_2'],
            ['producttax', 'createdbyuserid', 'nullable' => false, 'producttax_ibfk_1'],
            ['producttax', 'modifiedbyuserid', 'nullable' => false, 'producttax_ibfk_2'],
            ['producttax', 'deleted_by', 'nullable' => true, 'producttax_ibfk_3'],
            ['refunds', 'refunded_by_id', 'nullable' => false, 'refunds_ibfk_2'],
            ['segments', 'created_by', 'nullable' => true, 'segments_ibfk_1'],
            ['segments', 'updated_by', 'nullable' => true, 'segments_ibfk_2'],
            ['segment_items', 'created_by', 'nullable' => true, 'segment_items_ibfk_2'],
            ['segment_items', 'updated_by', 'nullable' => true, 'segment_items_ibfk_3'],
            ['shipping_method', 'created_by', 'nullable' => true, 'shipping_method_ibfk_1'],
            ['shipping_method', 'updated_by', 'nullable' => true, 'shipping_method_ibfk_2'],
            ['shipping_method', 'deleted_by', 'nullable' => true, 'shipping_method_ibfk_3'],
            ['shipping_tier', 'created_by', 'nullable' => true, 'shipping_tier_ibfk_1'],
            ['shipping_tier', 'updated_by', 'nullable' => true, 'shipping_tier_ibfk_2'],
            ['sponsorship', 'created_by', 'nullable' => true, 'sponsorship_ibfk_1'],
            ['sponsorship', 'updated_by', 'nullable' => true, 'sponsorship_ibfk_2'],
            ['sponsorship_segments', 'created_by', 'nullable' => true, 'sponsorship_segments_ibfk_4'],
            ['sponsorship_segments', 'updated_by', 'nullable' => true, 'sponsorship_segments_ibfk_5'],
            ['stock_adjustments', 'user_id', 'nullable' => true, 'stock_adjustments_ibfk_2'],
            ['ticket_check_in', 'check_in_by', 'nullable' => true, 'ticket_check_in_ibfk_3'],
            ['user', 'deleted_by', 'nullable' => true, 'user_ibfk_1'],
            ['user_logins', 'user_id', 'nullable' => false, 'user_logins_ibfk_1', 'onDelete' => 'cascade'],
        ]],
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ($this->migrationHasRun('2021_03_30_204116_create_feature_preview_user_states_table')) {
            Schema::table('feature_preview_user_states', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
        }

        if ($this->migrationHasRun('2021_03_30_204431_create_feature_preview_user_state_activities_table')) {
            Schema::table('feature_preview_user_state_activities', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
        }

        $this->makeChangesToPrimaryKeyColumnTypes('unsignedBigInteger');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->makeChangesToPrimaryKeyColumnTypes(null);

        if ($this->migrationHasRun('2021_03_30_204116_create_feature_preview_user_states_table')) {
            Schema::table('feature_preview_user_states', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('user');
            });
        }

        if ($this->migrationHasRun('2021_03_30_204431_create_feature_preview_user_state_activities_table')) {
            Schema::table('feature_preview_user_state_activities', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('user');
            });
        }
    }

    private function makeChangesToPrimaryKeyColumnTypes(?string $columnType): void
    {
        $constraints = $this->getConstraints();

        Schema::disableForeignKeyConstraints();

        $this->dropForeignKeyConstraints($constraints);
        $this->changeColumnsForTables($this->getTableChanges(), $columnType);
        $this->addForeignKeyConstraints($constraints);

        Schema::enableForeignKeyConstraints();
    }

    private function getTables(): Collection
    {
        return collect($this->tables)->map(function ($data, $tableName) {
            $columnType = $data[0];

            return (object) [
                'table' => $tableName,
                'column' => 'id',
                'columnType' => $columnType,
                'nullable' => false,
                'autoIncrement' => true,
                'constraints' => collect($data[1] ?? [])->map(function ($constrait) use ($tableName, $columnType) {
                    return (object) [
                        'table' => $constrait[0],
                        'column' => $constrait[1],
                        'columnType' => $constrait['columnType'] ?? $columnType,
                        'nullable' => $constrait['nullable'] ?? false,
                        'references' => 'id',
                        'on' => $tableName,
                        'index' => $constrait[2] ?? null,
                        'onDelete' => $constrait['onDelete'] ?? null,
                        'onUpdate' => $constrait['onUpdate'] ?? null,
                        'autoIncrement' => false,
                    ];
                }),
            ];
        });
    }

    private function getConstraints(): Collection
    {
        return $this->getTables()->pluck('constraints')->flatten();
    }

    private function getTableChanges(): Collection
    {
        $tableChanges = $this->getTables()->map(function ($data) {
            return collect([(object) Arr::except((array) $data, 'constraints')]);
        });

        $this->getConstraints()->each(function ($constraint) use ($tableChanges) {
            if (empty($tableChanges[$constraint->table])) {
                $tableChanges[$constraint->table] = collect();
            }

            $tableChanges[$constraint->table][] = $constraint;
        });

        return $tableChanges;
    }

    private function dropForeignKeyConstraints(Collection $constraints): void
    {
        foreach ($constraints->groupBy('table') as $tableName => $constraints) {
            $this->dropForeignKeyConstraintsForTable($tableName, $constraints);
        }
    }

    private function dropForeignKeyConstraintsForTable(string $tableName, Collection $constraints): void
    {
        Schema::table($tableName, function (Blueprint $table) use ($constraints) {
            foreach ($constraints as $constraint) {
                $table->dropForeign($constraint->index ?? [$constraint->column]);
            }
        });
    }

    private function changeColumnsForTables(Collection $tableChanges, ?string $columType): void
    {
        foreach ($tableChanges as $tableName => $changes) {
            $this->changeColumnsForTable($tableName, $changes, $columType);
        }
    }

    private function changeColumnsForTable(string $tableName, Collection $changes, ?string $columnType): void
    {
        Schema::table($tableName, function (Blueprint $table) use ($changes, $columnType) {
            foreach ($changes as $data) {
                $table->{$columnType ?? $data->columnType}($data->column, $data->autoIncrement)->nullable($data->nullable)->change();
            }
        });
    }

    private function addForeignKeyConstraints(Collection $constraints): void
    {
        foreach ($constraints->groupBy('table') as $tableName => $constraints) {
            $this->addForeignKeyConstraintsForTable($tableName, $constraints);
        }
    }

    private function addForeignKeyConstraintsForTable(string $tableName, Collection $constraints): void
    {
        Schema::table($tableName, function (Blueprint $table) use ($constraints) {
            foreach ($constraints as $constraint) {
                $table->foreign($constraint->column, $constraint->index)
                    ->references($constraint->references)
                    ->on($constraint->on)
                    ->onUpdate($constraint->onUpdate)
                    ->onDelete($constraint->onDelete);
            }
        });
    }

    private function migrationHasRun(string $migration): bool
    {
        return DB::table('migrations')->where('migration', $migration)->exists();
    }
}
