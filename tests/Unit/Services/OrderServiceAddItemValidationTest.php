<?php

namespace Tests\Unit\Services;

use Closure;
use Database\Factories\OrderItemFactory;
use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Models\Order;
use Ds\Models\OrderItem;
use Ds\Models\Product;
use Ds\Models\Variant;
use Ds\Services\Order\OrderAddItemLogicValidationService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * @group services
 * @group order
 */
class OrderServiceAddItemValidationTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // Avoid other tests to interfere with recurring_* validations.
        sys_set('rpp_default_type', '');
    }

    public function testAddItemFailsDataValidationWhenVariantIsDeleted(): void
    {
        $this->expectException(MessageException::class);

        $requestData = $this->createOrderAndBuildRequest([], ['is_deleted' => true]);

        $this->callAddItem($requestData);
    }

    /**
     * @dataProvider addItemValidationFailsProvider
     */
    public function testAddItemFailsDataValidation(string $field, $fieldValue, string $exceptionMessage): void
    {
        $this->expectException(MessageException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $requestData = $this->createOrderAndBuildRequest([$field => $fieldValue]);

        if ($fieldValue === null) {
            unset($requestData[$field]);
        }

        $this->callAddItem($requestData);
    }

    public function addItemValidationFailsProvider(): array
    {
        return [
            ['variant_id', null, 'No product selected.'],
            ['variant_id', 'not-integer', 'The variant id must be an integer.'],
            ['variant_id', 0, 'The selected variant id is invalid.'],
            // ['amt', null],
            ['amt', 'not-numeric', 'The amt must be a number.'],
            ['amt', '-1', 'The amt must be at least 0.'],
            // ['qty', null],
            ['qty', 'not-integer', 'The qty must be an integer.'],
            ['qty', 0.4, 'The qty must be an integer.'],
            ['recurring_frequency', 'unknown_frequency', 'The selected recurring frequency is invalid.'],
            ['recurring_day', 'not_integer', 'The recurring day must be an integer.'],
            ['recurring_day', 0, 'The recurring day must be at least 1.'],
            ['recurring_day', 1.2, 'The recurring day must be an integer.'],
            ['recurring_day', 32, 'The recurring day may not be greater than 31.'],
            ['recurring_day_of_week', 'not_integer', 'The recurring day of week must be an integer.'],
            ['recurring_day_of_week', 0, 'The recurring day of week must be at least 1.'],
            ['recurring_day_of_week', 1.2, 'The recurring day of week must be an integer.'],
            ['recurring_day_of_week', 8, 'The recurring day of week may not be greater than 7.'],
            ['recurring_with_initial_charge', 'not_boolean', 'The recurring with initial charge field must be true or false.'],
            ['recurring_with_dpo', 'not_boolean', 'The recurring with dpo field must be true or false.'],
            ['is_tribute', 'not_boolean', 'The is tribute field must be true or false.'],
            ['dpo_tribute_id', 'not_integer', 'The dpo tribute id must be an integer.'],
            ['tribute_notify', 'unknown_option', 'The selected tribute notify is invalid.'],
            // ['tribute_notify_email', 'not_email', ''],
            ['fields', 'not_array', 'The fields must be an array.'],
            ['fundraising_page_id', 'not_integer', 'The fundraising page id must be an integer.'],
            ['fundraising_page_id', 0.4, 'The fundraising page id must be an integer.'],
            ['fundraising_member_id', 'not_integer', 'The fundraising member id must be an integer.'],
            ['fundraising_member_id', 0.4, 'The fundraising member id must be an integer.'],
            ['gift_aid', 'not_boolean', 'The gift aid field must be true or false.'],
            ['metadata', 'not_array', 'The metadata must be an array.'],
        ];
    }

    /**
     * @dataProvider addItemValidationFailsWithFrequenceProvider
     */
    public function testAddItemFailsDataValidationWithFrequency(string $recurring, string $field): void
    {
        sys_set('rpp_default_type', 'fixed');

        $this->expectException(MessageException::class);

        $requestData = $this->createOrderAndBuildRequest([], [], function (OrderItemFactory $orderItemFactory) use ($recurring) {
            return $orderItemFactory->recurring($recurring);
        });

        unset($requestData[$field]);

        $this->callAddItem($requestData);
    }

    public function addItemValidationFailsWithFrequenceProvider(): array
    {
        return [
            ['monthly', 'recurring_day'],
            ['weekly', 'recurring_day_of_week'],
        ];
    }

    /**
     * @dataProvider addItemValidationFailsWhenIsTributeProvider
     */
    public function testAddItemFailsDataValidationWhenIsTribute(string $field, $fieldValue): void
    {
        $this->expectException(MessageException::class);

        $requestData = $this->createOrderAndBuildRequest(['is_tribute' => true, 'tribute_name' => 'tribute name']);

        if ($fieldValue === null) {
            unset($requestData[$field]);
        }

        $this->callAddItem($requestData);
    }

    public function addItemValidationFailsWhenIsTributeProvider(): array
    {
        return [
            ['tribute_type_id', null],
            ['tribute_type_id', 'not_integer'],
            ['tribute_type_id', 0],
            ['tribute_type_id', 1.2],
            ['tribute_name', null],
        ];
    }

    /**
     * @dataProvider addItemValidationFailsWithTributeNotifyProvider
     */
    public function testAddItemFailsDataValidationWithTributeNotify(string $notifyMethod, string $field, $fieldValue): void
    {
        $this->expectException(MessageException::class);
        $this->expectExceptionMessage(sprintf(
            'The %s field is required when tribute notify is %s.',
            str_replace('_', ' ', $field),
            $notifyMethod
        ));

        $requestData = $this->createOrderAndBuildRequest([
            'tribute_notify' => $notifyMethod,
            'tribute_notify_name' => 'some tribute notify name',
        ], [], function (OrderItemFactory $orderItemFactory) use ($notifyMethod) {
            return call_user_func([$orderItemFactory, Str::studly("tribute_$notifyMethod")]);
        });

        if ($fieldValue === null) {
            unset($requestData[$field]);
        }

        $this->callAddItem($requestData);
    }

    public function addItemValidationFailsWithTributeNotifyProvider(): array
    {
        return [
            ['email', 'tribute_notify_email', null],
            ['letter', 'tribute_notify_address', null],
            ['letter', 'tribute_notify_city', null],
            ['letter', 'tribute_notify_state', null],
            ['letter', 'tribute_notify_zip', null],
            ['letter', 'tribute_notify_country', null],
        ];
    }

    public function testAddItemFailsDataValidationWithTributeNotifyWrongEmail(): void
    {
        $this->expectException(MessageException::class);
        $this->expectExceptionMessage('The tribute notify email must be a valid email address.');

        $requestData = $this->createOrderAndBuildRequest([
            'tribute_notify' => 'email',
            'tribute_notify_name' => 'some tribute notify name',
            'tribute_notify_email' => 'not_email',
        ], [], function (OrderItemFactory $orderItemFactory) {
            return $orderItemFactory->tributeEmail();
        });

        $this->callAddItem($requestData);
    }

    /**
     * @dataProvider addItemFailsDataValidationWhenRelatedDoesNotExistProvider
     */
    public function testAddItemFailsDataValidationWhenRelatedDoesNotExist(string $field): void
    {
        $this->expectException(MessageException::class);
        $this->expectExceptionMessage('The selected ' . str_replace('_', ' ', $field) . ' is invalid.');

        $requestData = $this->createOrderAndBuildRequest([$field => 999999999999]);

        $this->callAddItem($requestData);
    }

    public function addItemFailsDataValidationWhenRelatedDoesNotExistProvider(): array
    {
        return [
            ['variant_id'],
            ['fundraising_page_id'],
            ['fundraising_member_id'],
        ];
    }

    /** @dataProvider checkItemsAreAvailableThrowsExceptionDataProvider */
    public function testCheckItemsAreAvailableThrowsException(int $inStock, int $qtyRequested, string $message = null): void
    {
        if ($message) {
            $this->expectException(MessageException::class);
            $this->expectExceptionMessage($message);
        }

        $variant = Variant::factory()->forProduct()->create(['quantity' => $inStock]);
        $orderItems = new Collection();
        $variants = new Collection([$variant]);

        // Asserting to null to make sure "good" quantities passes test.
        $this->assertNull(
            $this->app->make(OrderAddItemLogicValidationService::class)->validateAllVariants($variants, $orderItems, $qtyRequested)
        );
    }

    public function checkItemsAreAvailableThrowsExceptionDataProvider(): array
    {
        return [
            [0, 1, 'We have no more of this item available.'],
            [1, 1],
            [1, 2, 'We have limited stocks for this item. Only 1 is available for purchase'],
            [20, 1],
            [40, 10],
            [10, 14, 'We have limited stocks for this item. Only 10 are available for purchase'],
        ];
    }

    private function createOrderAndBuildRequest(
        array $requestOverrides = [],
        array $variantOverrides = [],
        Closure $orderItemFactoryCallback = null
    ): array {
        $orderItemFactory = $orderItemFactoryCallback
            ? $orderItemFactoryCallback(OrderItem::factory())
            : OrderItem::factory();

        $order = Order::factory()->create();
        $product = Product::factory()->create();
        $variant = Variant::factory()->create($variantOverrides);
        $product->variants()->save($variant);

        return array_merge(
            $orderItemFactory->make(['productorderid' => $order->getKey()])->toArray(),
            ['variant_id' => $variant->getKey()],
            $requestOverrides
        );
    }

    private function callAddItem(array $newItemRequestData = []): void
    {
        Order::factory()->create()->addItem($newItemRequestData);
    }
}
