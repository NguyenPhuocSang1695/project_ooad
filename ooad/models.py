from django.db import models

# --- Địa lý ---
class Province(models.Model):
    province_id = models.IntegerField(primary_key=True)
    name = models.CharField(max_length=255)

    class Meta:
        db_table = 'province'
        managed = False


class District(models.Model):
    district_id = models.AutoField(primary_key=True)
    name = models.CharField(max_length=255, null=True, blank=True)
    province = models.ForeignKey(Province, on_delete=models.CASCADE, related_name='districts')

    class Meta:
        db_table = 'district'
        managed = False



class Ward(models.Model):
    ward_id = models.AutoField(primary_key=True)
    name = models.CharField(max_length=255, null=True, blank=True)
    district = models.ForeignKey(District, on_delete=models.CASCADE, related_name='wards')

    class Meta:
        db_table = 'ward'
        managed = False



class Address(models.Model):
    address_id = models.AutoField(primary_key=True)
    ward_id = models.ForeignKey(Ward, on_delete=models.CASCADE, related_name='addresses')
    address_detail = models.CharField(max_length=255)

    class Meta:
        db_table = 'address'
        managed = False


# --- Danh mục sản phẩm ---
class Category(models.Model):
    CategoryID = models.AutoField(primary_key=True)
    CategoryName = models.CharField(max_length=255)
    Description = models.CharField(max_length=255)

    class Meta:
        db_table = 'categories'
        managed = False


class Supplier(models.Model):
    supplier_id = models.AutoField(primary_key=True)
    supplier_name = models.CharField(max_length=255)
    phone = models.CharField(max_length=50, null=True, blank=True)
    email = models.EmailField(max_length=255, null=True, blank=True)
    address_detail = models.CharField(max_length=255, null=True, blank=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        db_table = 'suppliers'
        managed = False


class Product(models.Model):
    STATUS_CHOICES = [
        ('hidden', 'Hidden'),
        ('appear', 'Appear'),
    ]


    ProductID = models.AutoField(primary_key=True)
    ProductName = models.CharField(max_length=255)
    Category = models.ForeignKey(Category, on_delete=models.CASCADE, related_name='products')
    Price = models.DecimalField(max_digits=10, decimal_places=0)
    Description = models.TextField()
    ImageURL = models.CharField(max_length=255)
    Status = models.CharField(max_length=10, choices=STATUS_CHOICES, default='appear')
    Supplier = models.ForeignKey(Supplier, on_delete=models.CASCADE, related_name='products')
    quantity = models.IntegerField()

    class Meta:
        db_table = 'products'
        managed = False

# --- Người dùng & Đơn hàng ---
class User(models.Model):
    ROLE_CHOICES = [
        ('customer', 'Customer'),
        ('admin', 'Admin'),
    ]
    STATUS_CHOICES = [
        ('Active', 'Active'),
        ('Block', 'Block'),
    ]

    Username = models.CharField(max_length=255, primary_key=True)
    FullName = models.CharField(max_length=255)
    Email = models.EmailField(max_length=255, null=True, blank=True)
    PasswordHash = models.CharField(max_length=255)
    address = models.ForeignKey(Address, on_delete=models.CASCADE, related_name='users')
    Role = models.CharField(max_length=10, choices=ROLE_CHOICES, default='customer')
    Phone = models.CharField(max_length=100)
    Status = models.CharField(max_length=10, choices=STATUS_CHOICES, default='Active')

    class Meta:
        db_table = 'users'
        managed = False


class Order(models.Model):
    STATUS_CHOICES = [
        ('execute', 'Execute'),
        ('ship', 'Ship'),
        ('success', 'Success'),
        ('fail', 'Fail'),
        ('confirmed', 'Confirmed'),
    ]

    OrderID = models.AutoField(primary_key=True)
    Username = models.ForeignKey(User, on_delete=models.CASCADE, related_name='orders')
    Status = models.CharField(max_length=10, choices=STATUS_CHOICES, default='execute')
    PaymentMethod = models.CharField(max_length=50)
    CustomerName = models.CharField(max_length=255, null=True, blank=True)
    Phone = models.CharField(max_length=100, null=True, blank=True)
    DateGeneration = models.DateTimeField(auto_now_add=True)
    TotalAmount = models.DecimalField(max_digits=10, decimal_places=2)
    Ward_id = models.IntegerField()
    address = models.ForeignKey(Address, on_delete=models.SET_NULL, null=True, related_name='orders')

    class Meta:
        db_table = 'orders'
        managed = False

class OrderDetail(models.Model):
    Order = models.ForeignKey(Order, on_delete=models.CASCADE, related_name='details')
    Product = models.ForeignKey(Product, on_delete=models.CASCADE, related_name='order_details')
    Quantity = models.IntegerField()
    UnitPrice = models.DecimalField(max_digits=10, decimal_places=0)
    TotalPrice = models.DecimalField(max_digits=10, decimal_places=0)

    class Meta:
        db_table = 'orderdetails'
        managed = False

# --- Phiếu nhập ---
class ImportReceipt(models.Model):
    receipt_id = models.AutoField(primary_key=True)
    supplier = models.ForeignKey(Supplier, on_delete=models.CASCADE, related_name='import_receipts')
    import_date = models.DateTimeField(auto_now_add=True)
    total_amount = models.DecimalField(max_digits=15, decimal_places=2, default=0.00)
    note = models.TextField(null=True, blank=True)

    class Meta:
        db_table = 'import_receipts'
        managed = False



class ImportReceiptDetail(models.Model):
    detail_id = models.AutoField(primary_key=True)
    receipt = models.ForeignKey(ImportReceipt, on_delete=models.CASCADE, related_name='details')
    product = models.ForeignKey(Product, on_delete=models.CASCADE, related_name='import_details')
    quantity = models.IntegerField()
    import_price = models.DecimalField(max_digits=15, decimal_places=2)

    class Meta:
        db_table = 'import_receipt_details'
        managed = False