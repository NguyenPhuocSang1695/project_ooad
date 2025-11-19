<?php

class ImportReceipt
{
    protected $receiptId;
    protected $importDate;
    protected $totalAmount;
    protected $note;
    protected $products; // Mỗi product sẽ chứa: product_id, supplier_id, quantity, import_price, subtotal
    protected $supplierId;



    /**
     * Constructor
     */
    public function __construct($importDate = null, $totalAmount = 0, $note = '')
    {
        $this->importDate = $importDate;
        $this->totalAmount = $totalAmount;
        $this->note = $note;
        $this->products = [];
    }

    // ==================== GETTERS ====================

    public function getReceiptId()
    {
        return $this->receiptId;
    }

    public function getImportDate()
    {
        return $this->importDate;
    }

    public function getTotalAmount()
    {
        return $this->totalAmount;
    }

    public function getNote()
    {
        return $this->note;
    }

    public function getProducts()
    {
        return $this->products;
    }

    // ==================== SETTERS ====================

    public function setReceiptId($receiptId)
    {
        $this->receiptId = $receiptId;
        return $this;
    }

    public function setImportDate($importDate)
    {
        $this->importDate = $importDate;
        return $this;
    }

    public function setTotalAmount($totalAmount)
    {
        $this->totalAmount = $totalAmount;
        return $this;
    }

    public function setNote($note)
    {
        $this->note = $note;
        return $this;
    }

    public function setProducts($products)
    {
        $this->products = $products;
        return $this;
    }

    public function setSupplierId($supplierId)
    {
        $this->supplierId = $supplierId;
        return $this;
    }


    // ==================== METHODS ====================

    /**
     * Thêm sản phẩm vào phiếu nhập
     */
    public function addProduct($productId, $supplierId, $quantity, $importPrice, $productName = null)
    {
        $subtotal = $quantity * $importPrice;

        $this->products[] = [
            'product_id' => $productId,
            'supplier_id' => $supplierId,        // vẫn giữ supplier theo sản phẩm
            'product_name' => $productName,
            'quantity' => $quantity,
            'import_price' => $importPrice,
            'subtotal' => $subtotal
        ];

        return $this;
    }
    /**
     * Xóa sản phẩm khỏi phiếu nhập theo index
     */
    public function removeProduct($index)
    {
        if (isset($this->products[$index])) {
            unset($this->products[$index]);
            $this->products = array_values($this->products);
        }
        return $this;
    }

    /**
     * Tính tổng tiền của phiếu nhập
     */
    public function calculateTotal()
    {
        $total = 0;
        foreach ($this->products as $product) {
            $total += $product['subtotal'];
        }
        $this->totalAmount = $total;
        return $total;
    }

    /**
     * Kiểm tra phiếu nhập có hợp lệ không
     */
    public function isValid($checkSupplier = true)
    {
        if (empty($this->products)) {
            return ['valid' => false, 'message' => 'Vui lòng chọn ít nhất một sản phẩm!'];
        }

        if (empty($this->importDate)) {
            return ['valid' => false, 'message' => 'Vui lòng chọn ngày nhập!'];
        }


        // Chỉ check supplier nếu $checkSupplier = true
        if ($checkSupplier && empty($this->supplierId)) {
            return ['valid' => false, 'message' => 'Vui lòng chọn nhà cung cấp!'];
        }


        return ['valid' => true];
    }

    /**
     * Chuyển object thành array
     */
    public function toArray()
    {
        return [
            'receipt_id' => $this->receiptId,
            'import_date' => $this->importDate,
            'total_amount' => $this->totalAmount,
            'note' => $this->note,
            'supplier_id'  => $this->supplierId,
            'products' => $this->products
        ];
    }

    /**
     * Load dữ liệu từ array
     */
    public function fromArray($data)
    {
        if (isset($data['receipt_id'])) $this->receiptId = $data['receipt_id'];
        if (isset($data['import_date'])) $this->importDate = $data['import_date'];
        if (isset($data['total_amount'])) $this->totalAmount = $data['total_amount'];
        if (isset($data['note'])) $this->note = $data['note'];
        if (isset($data['supplier_id']))  $this->supplierId = $data['supplier_id'];
        if (isset($data['products'])) $this->products = $data['products'];

        return $this;
    }

    // Format số và ngày
    public function formatAmount($amount = null)
    {
        $value = $amount !== null ? $amount : $this->totalAmount;
        return number_format($value, 0, ',', '.') . ' VNĐ';
    }

    public function formatDate($format = 'd/m/Y H:i')
    {
        if (empty($this->importDate)) {
            return '';
        }
        return date($format, strtotime($this->importDate));
    }

    public function getProductCount()
    {
        return count($this->products);
    }
}
