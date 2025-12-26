# Testing Guide

دليل شامل للاختبارات في المشروع

---

## 📋 نظرة عامة

المشروع يستخدم **Pest Testing Framework** لكتابة اختبارات واضحة وسهلة القراءة.

### أنواع الاختبارات:
1. **Unit Tests** - اختبار الوحدات (Models, Services)
2. **Feature Tests** - اختبار المميزات (API Endpoints, Commands)

---

## 🚀 تشغيل الاختبارات

### تشغيل جميع الاختبارات
```bash
php artisan test
```

### تشغيل Unit Tests فقط
```bash
php artisan test --testsuite=Unit
```

### تشغيل Feature Tests فقط
```bash
php artisan test --testsuite=Feature
```

### تشغيل ملف معين
```bash
php artisan test tests/Unit/ProductTest.php
```

### تشغيل test معين
```bash
php artisan test --filter="can add product to cart"
```

### مع تقرير مفصل
```bash
php artisan test --coverage
```

---

## 📁 هيكل الاختبارات

```
tests/
├── Unit/
│   ├── ProductTest.php                         # اختبار Product Model
│   ├── CartTest.php                            # اختبار Cart Model
│   ├── StockServiceTest.php                    # اختبار Stock Service
│   ├── CartServiceTest.php                     # اختبار Cart Service
│   ├── OrderServiceTest.php                    # اختبار Order Service
│   ├── SendLowStockNotificationJobTest.php     # اختبار Low Stock Job
│   └── SendDailySalesReportJobTest.php         # اختبار Sales Report Job
│
└── Feature/
    ├── ProductApiTest.php                      # اختبار Products API
    ├── CartApiTest.php                         # اختبار Cart API
    └── CommandsTest.php                        # اختبار Artisan Commands
```

---

## 📝 Unit Tests

### 1. ProductTest.php
**يختبر:**
- ✅ Product attributes
- ✅ Stock checking (`hasStock`, `isLowStock`)
- ✅ Stock increment/decrement
- ✅ Price casting

**مثال:**
```php
test('product can check if it has sufficient stock', function () {
    $product = Product::factory()->create(['stock_quantity' => 10]);

    expect($product->hasStock(5))->toBeTrue()
        ->and($product->hasStock(11))->toBeFalse();
});
```

---

### 2. CartTest.php
**يختبر:**
- ✅ Cart relationships (user, items)
- ✅ Cart total calculation
- ✅ Cart isEmpty check
- ✅ Cart clear functionality

**مثال:**
```php
test('cart can calculate total', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price' => 10.00]);

    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    expect($cart->getTotal())->toBe(20.0);
});
```

---

### 3. StockServiceTest.php
**يختبر:**
- ✅ Stock decrement/increment
- ✅ Low stock notification dispatch
- ✅ Get low stock products
- ✅ Check and notify logic

**مثال:**
```php
test('decrement stock dispatches low stock notification when threshold is met', function () {
    config(['cart.low_stock_threshold' => 5]);
    $product = Product::factory()->create(['stock_quantity' => 7]);

    $this->stockService->decrementStock($product, 3);

    Queue::assertPushed(SendLowStockNotification::class);
});
```

---

### 4. CartServiceTest.php
**يختبر:**
- ✅ Get/Create user cart
- ✅ Add to cart
- ✅ Update cart item
- ✅ Remove from cart
- ✅ Clear cart
- ✅ Stock validation
- ✅ InsufficientStockException

**مثال:**
```php
test('add to cart throws exception when insufficient stock', function () {
    $product = Product::factory()->create(['stock_quantity' => 5]);

    $this->cartService->addToCart($this->user, $product->id, 10);
})->throws(InsufficientStockException::class);
```

---

### 5. OrderServiceTest.php
**يختبر:**
- ✅ Create order from cart
- ✅ Order items creation
- ✅ Stock decrement on order
- ✅ Cart clearing after order
- ✅ Get today's sales data
- ✅ Sales data aggregation

**مثال:**
```php
test('create order from cart creates order with correct total', function () {
    $cart = Cart::factory()->create(['user_id' => $this->user->id]);
    $product = Product::factory()->create(['price' => 50.00, 'stock_quantity' => 10]);

    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    $order = $this->orderService->createOrderFromCart($this->user);

    expect($order->total)->toBe('100.00');
});
```

---

### 6. Job Tests
**يختبر:**
- ✅ SendLowStockNotification sends email
- ✅ SendDailySalesReport sends email
- ✅ Mail content validation
- ✅ Job dispatching to queue

---

## 🌐 Feature Tests

### 1. ProductApiTest.php
**يختبر:**
- ✅ Get paginated products
- ✅ Pagination parameters
- ✅ Per page limits (max 100)
- ✅ View single product
- ✅ 404 for non-existent product
- ✅ Products sorted by name

**مثال:**
```php
test('can get paginated products list', function () {
    Product::factory()->count(20)->create();

    $response = $this->getJson('/api/products');

    $response->assertStatus(200)
        ->assertJson(['success' => true])
        ->assertJsonStructure([
            'data' => [
                'items',
                'pagination'
            ]
        ]);
});
```

---

### 2. CartApiTest.php
**يختبر:**
- ✅ View cart (auth required)
- ✅ Add to cart
- ✅ Update cart item
- ✅ Remove from cart
- ✅ Clear cart
- ✅ Checkout
- ✅ Stock validation
- ✅ API response format

**مثال:**
```php
test('can add product to cart', function () {
    $product = Product::factory()->create(['stock_quantity' => 10]);

    $response = $this->actingAs($this->user)->postJson('/api/cart/add', [
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Product added to cart',
        ]);
});
```

---

### 3. CommandsTest.php
**يختبر:**
- ✅ `sales:report-daily` command
- ✅ `stock:notify-low` command
- ✅ Command output messages
- ✅ Job dispatching

**مثال:**
```php
test('stock notify low command dispatches jobs for low stock products', function () {
    config(['cart.low_stock_threshold' => 5]);
    Product::factory()->create(['stock_quantity' => 2]);

    $this->artisan('stock:notify-low --queue')
        ->expectsOutput('Found 1 low stock product(s):')
        ->assertSuccessful();

    Queue::assertPushed(SendLowStockNotification::class);
});
```

---

## 🎯 Coverage Summary

### Models
- ✅ Product
- ✅ Cart
- ✅ CartItem (covered in Cart tests)
- ✅ Order (covered in OrderService tests)
- ✅ OrderItem (covered in OrderService tests)

### Services
- ✅ StockService
- ✅ CartService
- ✅ OrderService

### Jobs
- ✅ SendLowStockNotification
- ✅ SendDailySalesReport

### API Endpoints
- ✅ GET /api/products
- ✅ GET /api/products/{id}
- ✅ GET /api/cart
- ✅ POST /api/cart/add
- ✅ PUT /api/cart/items/{id}
- ✅ DELETE /api/cart/items/{id}
- ✅ DELETE /api/cart/clear
- ✅ POST /api/cart/checkout

### Commands
- ✅ sales:report-daily
- ✅ stock:notify-low

---

## 🧪 نصائح للاختبار

### 1. استخدام Factories
```php
$product = Product::factory()->create(['stock_quantity' => 10]);
```

### 2. Faking Queues
```php
Queue::fake();
// ... code that dispatches jobs
Queue::assertPushed(SendLowStockNotification::class);
```

### 3. Faking Mail
```php
Mail::fake();
// ... code that sends emails
Mail::assertSent(LowStockNotification::class);
```

### 4. Acting as User
```php
$user = User::factory()->create();
$response = $this->actingAs($user)->getJson('/api/cart');
```

### 5. Testing Exceptions
```php
test('throws exception', function () {
    // code that should throw exception
})->throws(ExceptionClass::class);
```

---

## 🐛 Debugging Tests

### عرض Output مفصل
```bash
php artisan test --verbose
```

### Debug test معين
```bash
php artisan test --filter="test name" --verbose
```

### استخدام dd() داخل Test
```php
test('debug test', function () {
    $product = Product::factory()->create();
    dd($product); // سيوقف التنفيذ ويعرض المحتوى
});
```

---

## 📊 تشغيل Tests في Docker

```bash
# تشغيل جميع الاختبارات
docker exec -it cart_app php artisan test

# تشغيل Unit tests فقط
docker exec -it cart_app php artisan test --testsuite=Unit

# تشغيل Feature tests فقط
docker exec -it cart_app php artisan test --testsuite=Feature
```

---

## ✅ Best Practices

1. **اكتب test لكل feature جديدة**
2. **استخدم أسماء واضحة للـ tests**
   ```php
   test('can add product to cart')  // ✅ Good
   test('test1')                     // ❌ Bad
   ```

3. **استخدم beforeEach للـ setup المشترك**
   ```php
   beforeEach(function () {
       $this->user = User::factory()->create();
   });
   ```

4. **Test one thing at a time**
5. **استخدم Factories بدلاً من إنشاء البيانات يدوياً**
6. **Clean up after tests** (Laravel يفعل هذا تلقائياً مع database transactions)

---

## 📈 إحصائيات

**إجمالي عدد الاختبارات:** ~80+ test
**وقت التنفيذ المتوقع:** ~10-15 ثانية

---

## 🎉 الخلاصة

المشروع لديه **تغطية شاملة** للاختبارات تشمل:
- ✅ جميع Models
- ✅ جميع Services
- ✅ جميع API Endpoints
- ✅ جميع Background Jobs
- ✅ جميع Artisan Commands

**تشغيل الاختبارات:**
```bash
php artisan test
```

**كل شيء جاهز! 🚀**
