<?php

use Modules\Account\Models\AccountAccount;
use Modules\Account\Models\AccountCategory;
use Modules\Account\Models\AccountDailySummary;
use Modules\Account\Models\AccountExpense;
use Modules\Account\Models\AccountInvestment;
use Modules\Account\Models\AccountProductProfitSnapshot;
use Modules\Account\Models\AccountTransaction;
use Modules\Account\Models\AccountTransactionLine;
use Modules\Account\Models\AccountTransfer;
use Modules\Cart\Models\Campaign;
use Modules\Cart\Models\CampaignProduct;
use Modules\Cart\Models\Cart;
use Modules\Cart\Models\CartItem;
use Modules\Cart\Models\Coupon;
use Modules\Cart\Models\Wishlist;
use Modules\Catalog\Models\Brand;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\ProductCategory;
use Modules\Catalog\Models\ProductImage;
use Modules\Catalog\Models\ProductRequest;
use Modules\Catalog\Models\ProductVariant;
use Modules\Catalog\Models\Size;
use Modules\Catalog\Models\TaxRate;
use Modules\Catalog\Models\Unit;
use Modules\Catalog\Models\VariantOption;
use Modules\Frontend\Models\AnnouncementBar;
use Modules\Frontend\Models\Banner;
use Modules\Frontend\Models\HomepageCta;
use Modules\Frontend\Models\NavbarItem;
use Modules\Frontend\Models\Setting;
use Modules\Frontend\Models\SubnavbarItem;
use Modules\Identity\Models\Permission;
use Modules\Identity\Models\Role;
use Modules\Identity\Models\User;
use Modules\Identity\Models\UserSession;
use Modules\Inventory\Models\InventoryLocation;
use Modules\Inventory\Models\InventoryMovement;
use Modules\Inventory\Models\InventoryStock;
use Modules\Inventory\Models\PurchaseOrder;
use Modules\Inventory\Models\PurchaseOrderItem;
use Modules\Inventory\Models\PurchaseReturn;
use Modules\Inventory\Models\Supplier;
use Modules\Inventory\Models\SupplierPayment;
use Modules\Order\Models\Delivery;
use Modules\Order\Models\Order;
use Modules\Order\Models\OrderItem;
use Modules\Order\Models\Payment;
use Modules\Order\Models\Refund;
use Modules\Pos\Models\PosRegister;
use Modules\Pos\Models\PosSale;
use Modules\Pos\Models\PosSaleItem;
use Modules\Pos\Models\PosShift;
use Modules\Reviews\Models\Notification;
use Modules\Reviews\Models\ProductReview;
use Modules\Reviews\Models\Webhook;
use Modules\Reviews\Models\WebhookDelivery;
use Modules\Shipping\Models\DeliveryDriver;
use Modules\Shipping\Models\DeliveryZone;
use Modules\Shipping\Models\Shipment;
use Modules\Shipping\Models\ShipmentEvent;
use Modules\Store\Models\Address;
use Modules\Store\Models\AppSetting;
use Modules\Store\Models\Country;
use Modules\Store\Models\Store;
use Modules\Store\Models\StoreStaff;

return [
    'name' => 'History',
    'models' => [
        User::class,
        Role::class,
        Permission::class,
        UserSession::class,
        AccountAccount::class,
        AccountCategory::class,
        AccountDailySummary::class,
        AccountExpense::class,
        AccountInvestment::class,
        AccountProductProfitSnapshot::class,
        AccountTransaction::class,
        AccountTransactionLine::class,
        AccountTransfer::class,
        Campaign::class,
        CampaignProduct::class,
        Cart::class,
        CartItem::class,
        Coupon::class,
        Wishlist::class,
        Brand::class,
        Category::class,
        Product::class,
        ProductCategory::class,
        ProductImage::class,
        ProductRequest::class,
        ProductVariant::class,
        Size::class,
        TaxRate::class,
        Unit::class,
        VariantOption::class,
        AnnouncementBar::class,
        Banner::class,
        HomepageCta::class,
        NavbarItem::class,
        Setting::class,
        SubnavbarItem::class,
        InventoryLocation::class,
        InventoryMovement::class,
        InventoryStock::class,
        PurchaseOrder::class,
        PurchaseOrderItem::class,
        PurchaseReturn::class,
        Supplier::class,
        SupplierPayment::class,
        Delivery::class,
        Order::class,
        OrderItem::class,
        Payment::class,
        Refund::class,
        DeliveryDriver::class,
        DeliveryZone::class,
        Shipment::class,
        ShipmentEvent::class,
        Address::class,
        AppSetting::class,
        Country::class,
        Store::class,
        StoreStaff::class,
        PosRegister::class,
        PosSale::class,
        PosSaleItem::class,
        PosShift::class,
        Notification::class,
        ProductReview::class,
        Webhook::class,
        WebhookDelivery::class,
    ],
];
