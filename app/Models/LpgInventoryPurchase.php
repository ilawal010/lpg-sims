<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class LpgInventoryPurchase extends Model
{
    protected $fillable = ['lpg_stock_receipt_id','branch_id','lpg_supplier_id','purchase_date','quantity_kg','unit_cost','total_cost','payment_status','created_by'];
    protected $casts = ['purchase_date'=>'date','quantity_kg'=>'decimal:3','unit_cost'=>'decimal:4','total_cost'=>'decimal:4'];
    public function receipt() { return $this->belongsTo(LpgStockReceipt::class,'lpg_stock_receipt_id'); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function supplier() { return $this->belongsTo(LpgSupplier::class,'lpg_supplier_id'); }
    public function payments() { return $this->hasMany(\App\Models\LpgPurchasePayment::class, 'lpg_inventory_purchase_id'); }

}
