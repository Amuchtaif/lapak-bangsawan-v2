<?php

use Phinx\Db\Adapter\MysqlAdapter;

class SchemaInitial extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->execute("ALTER DATABASE CHARACTER SET 'utf8mb4';");
        $this->execute("ALTER DATABASE COLLATE='utf8mb4_general_ci';");
        $this->table('categories', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => true,
            ])
            ->addColumn('name', 'string', [
                'null' => false,
                'limit' => 100,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'id',
            ])
            ->addColumn('slug', 'string', [
                'null' => false,
                'limit' => 100,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ])
            ->addColumn('created_at', 'timestamp', [
                'null' => false,
                'default' => 'current_timestamp()',
                'after' => 'slug',
            ])
            ->addIndex(['slug'], [
                'name' => 'slug',
                'unique' => true,
            ])
            ->create();
        $this->table('customers', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => true,
            ])
            ->addColumn('name', 'string', [
                'null' => false,
                'limit' => 100,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'id',
            ])
            ->addColumn('email', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 100,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ])
            ->addColumn('phone', 'string', [
                'null' => false,
                'limit' => 20,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'email',
            ])
            ->addColumn('address', 'text', [
                'null' => false,
                'limit' => 65535,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'phone',
            ])
            ->addColumn('created_at', 'timestamp', [
                'null' => false,
                'default' => 'current_timestamp()',
                'after' => 'address',
            ])
            ->create();
        $this->table('daily_sales_targets', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => true,
            ])
            ->addColumn('product_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'id',
            ])
            ->addColumn('target_date', 'date', [
                'null' => false,
                'after' => 'product_id',
            ])
            ->addColumn('target_qty_kg', 'decimal', [
                'null' => false,
                'default' => '0.00',
                'precision' => 10,
                'scale' => 2,
                'after' => 'target_date',
            ])
            ->addColumn('created_at', 'timestamp', [
                'null' => true,
                'default' => 'current_timestamp()',
                'after' => 'target_qty_kg',
            ])
            ->addIndex(['product_id', 'target_date'], [
                'name' => 'unique_target',
                'unique' => true,
            ])
            ->addIndex(['target_date'], [
                'name' => 'target_date',
                'unique' => false,
            ])
            ->create();
        $this->table('messages', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => true,
            ])
            ->addColumn('name', 'string', [
                'null' => false,
                'limit' => 255,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'id',
            ])
            ->addColumn('email', 'string', [
                'null' => false,
                'limit' => 255,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ])
            ->addColumn('message', 'text', [
                'null' => false,
                'limit' => 65535,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'email',
            ])
            ->addColumn('is_read', 'boolean', [
                'null' => true,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'message',
            ])
            ->addColumn('created_at', 'timestamp', [
                'null' => false,
                'default' => 'current_timestamp()',
                'after' => 'is_read',
            ])
            ->create();
        $this->table('operational_expenses', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => true,
            ])
            ->addColumn('expense_date', 'date', [
                'null' => false,
                'after' => 'id',
            ])
            ->addColumn('category', 'enum', [
                'null' => false,
                'limit' => 20,
                'values' => ['Pembelian Bahan Baku', 'Sewa & Utilitas', 'Gaji Karyawan', 'Marketing', 'Perlengkapan', 'Lainnya'],
                'after' => 'expense_date',
            ])
            ->addColumn('description', 'text', [
                'null' => true,
                'default' => null,
                'limit' => 65535,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'category',
            ])
            ->addColumn('amount', 'decimal', [
                'null' => false,
                'precision' => 15,
                'scale' => 2,
                'after' => 'description',
            ])
            ->addColumn('proof_image', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 255,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'amount',
            ])
            ->addColumn('created_by', 'integer', [
                'null' => true,
                'default' => null,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'proof_image',
            ])
            ->addColumn('created_at', 'timestamp', [
                'null' => false,
                'default' => 'current_timestamp()',
                'after' => 'created_by',
            ])
            ->create();
        $this->table('orders', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => true,
            ])
            ->addColumn('order_number', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 50,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'id',
            ])
            ->addColumn('customer_id', 'integer', [
                'null' => true,
                'default' => null,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'order_number',
            ])
            ->addColumn('customer_name', 'string', [
                'null' => false,
                'limit' => 100,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'customer_id',
            ])
            ->addColumn('customer_phone', 'string', [
                'null' => false,
                'limit' => 20,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'customer_name',
            ])
            ->addColumn('customer_address', 'text', [
                'null' => false,
                'limit' => 65535,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'customer_phone',
            ])
            ->addColumn('destination_area_id', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 255,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'customer_address',
            ])
            ->addColumn('destination_latitude', 'decimal', [
                'null' => true,
                'default' => null,
                'precision' => 10,
                'scale' => 8,
                'after' => 'destination_area_id',
            ])
            ->addColumn('destination_longitude', 'decimal', [
                'null' => true,
                'default' => null,
                'precision' => 11,
                'scale' => 8,
                'after' => 'destination_latitude',
            ])
            ->addColumn('total_amount', 'decimal', [
                'null' => false,
                'precision' => 10,
                'scale' => 2,
                'after' => 'destination_longitude',
            ])
            ->addColumn('shipping_cost', 'decimal', [
                'null' => true,
                'default' => '0.00',
                'precision' => 15,
                'scale' => 2,
                'after' => 'total_amount',
            ])
            ->addColumn('payment_method', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 50,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'shipping_cost',
            ])
            ->addColumn('payment_proof', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 255,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'payment_method',
            ])
            ->addColumn('payment_status', 'enum', [
                'null' => true,
                'default' => 'unpaid',
                'limit' => 20,
                'values' => ['unpaid', 'waiting_verification', 'paid'],
                'after' => 'payment_proof',
            ])
            ->addColumn('order_notes', 'text', [
                'null' => true,
                'default' => null,
                'limit' => 65535,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'payment_status',
            ])
            ->addColumn('status', 'enum', [
                'null' => true,
                'default' => 'pending',
                'limit' => 9,
                'values' => ['pending', 'completed', 'cancelled', 'unpaid', 'confirmed', 'shipping'],
                'after' => 'order_notes',
            ])
            ->addColumn('created_at', 'timestamp', [
                'null' => false,
                'default' => 'current_timestamp()',
                'after' => 'status',
            ])
            ->addColumn('order_token', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 64,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'created_at',
            ])
            ->addColumn('manual_discount', 'decimal', [
                'null' => true,
                'default' => '0.00',
                'precision' => 15,
                'scale' => 2,
                'after' => 'order_token',
            ])
            ->addColumn('biteship_order_id', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 255,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'manual_discount',
            ])
            ->addColumn('courier_company', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 100,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'biteship_order_id',
            ])
            ->addColumn('courier_type', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 100,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'courier_company',
            ])
            ->addColumn('tracking_id', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 255,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'courier_type',
            ])
            ->addColumn('weight_total', 'integer', [
                'null' => true,
                'default' => '0',
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'tracking_id',
            ])
            ->addIndex(['order_token'], [
                'name' => 'unique_order_token',
                'unique' => true,
            ])
            ->addIndex(['order_number'], [
                'name' => 'order_number_unique',
                'unique' => true,
            ])
            ->create();
        $this->table('order_items', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => true,
            ])
            ->addColumn('order_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'id',
            ])
            ->addColumn('product_name', 'string', [
                'null' => false,
                'limit' => 100,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'order_id',
            ])
            ->addColumn('weight', 'decimal', [
                'null' => false,
                'precision' => 10,
                'scale' => 2,
                'after' => 'product_name',
            ])
            ->addColumn('price_per_kg', 'decimal', [
                'null' => false,
                'precision' => 10,
                'scale' => 2,
                'after' => 'weight',
            ])
            ->addColumn('buy_price', 'decimal', [
                'null' => true,
                'default' => '0.00',
                'precision' => 10,
                'scale' => 2,
                'after' => 'price_per_kg',
            ])
            ->addColumn('subtotal', 'decimal', [
                'null' => false,
                'precision' => 10,
                'scale' => 2,
                'after' => 'buy_price',
            ])
            ->create();
        $this->table('products', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => true,
            ])
            ->addColumn('category_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'id',
            ])
            ->addColumn('short_code', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 10,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'category_id',
            ])
            ->addColumn('name', 'string', [
                'null' => false,
                'limit' => 100,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'short_code',
            ])
            ->addColumn('slug', 'string', [
                'null' => false,
                'limit' => 100,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ])
            ->addColumn('description', 'text', [
                'null' => true,
                'default' => null,
                'limit' => 65535,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'slug',
            ])
            ->addColumn('price', 'decimal', [
                'null' => false,
                'precision' => 10,
                'scale' => 2,
                'after' => 'description',
            ])
            ->addColumn('buy_price', 'decimal', [
                'null' => true,
                'default' => '0.00',
                'precision' => 10,
                'scale' => 2,
                'after' => 'price',
            ])
            ->addColumn('stock', 'decimal', [
                'null' => false,
                'default' => '0.00',
                'precision' => 10,
                'scale' => 2,
                'after' => 'buy_price',
            ])
            ->addColumn('unit', 'string', [
                'null' => true,
                'default' => 'kg',
                'limit' => 20,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'stock',
            ])
            ->addColumn('image', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 255,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'unit',
            ])
            ->addColumn('created_at', 'timestamp', [
                'null' => false,
                'default' => 'current_timestamp()',
                'after' => 'image',
            ])
            ->addColumn('weight', 'integer', [
                'null' => true,
                'default' => '1000',
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'created_at',
            ])
            ->addIndex(['short_code'], [
                'name' => 'short_code',
                'unique' => true,
            ])
            ->addIndex(['category_id'], [
                'name' => 'category_id',
                'unique' => false,
            ])
            ->create();
        $this->table('site_settings', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => true,
            ])
            ->addColumn('setting_key', 'string', [
                'null' => false,
                'limit' => 50,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'id',
            ])
            ->addColumn('setting_value', 'text', [
                'null' => true,
                'default' => null,
                'limit' => 65535,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'setting_key',
            ])
            ->addColumn('setting_type', 'enum', [
                'null' => false,
                'default' => 'text',
                'limit' => 8,
                'values' => ['text', 'textarea', 'image', 'number'],
                'after' => 'setting_value',
            ])
            ->addIndex(['setting_key'], [
                'name' => 'setting_key',
                'unique' => true,
            ])
            ->create();
        $this->table('stock_adjustments', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => true,
            ])
            ->addColumn('product_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'id',
            ])
            ->addColumn('qty_adjusted', 'decimal', [
                'null' => false,
                'precision' => 10,
                'scale' => 2,
                'after' => 'product_id',
            ])
            ->addColumn('reason', 'enum', [
                'null' => false,
                'limit' => 9,
                'values' => ['Shrinkage', 'Spoilage', 'Opname'],
                'after' => 'qty_adjusted',
            ])
            ->addColumn('admin_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'reason',
            ])
            ->addColumn('date', 'timestamp', [
                'null' => false,
                'default' => 'current_timestamp()',
                'after' => 'admin_id',
            ])
            ->addIndex(['product_id'], [
                'name' => 'product_id',
                'unique' => false,
            ])
            ->addIndex(['admin_id'], [
                'name' => 'admin_id',
                'unique' => false,
            ])
            ->create();
        $this->table('users', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => true,
            ])
            ->addColumn('username', 'string', [
                'null' => false,
                'limit' => 50,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'id',
            ])
            ->addColumn('full_name', 'string', [
                'null' => true,
                'default' => 'Admin User',
                'limit' => 100,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'username',
            ])
            ->addColumn('password', 'string', [
                'null' => false,
                'limit' => 255,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'full_name',
            ])
            ->addColumn('email', 'string', [
                'null' => false,
                'limit' => 100,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'password',
            ])
            ->addColumn('created_at', 'timestamp', [
                'null' => false,
                'default' => 'current_timestamp()',
                'after' => 'email',
            ])
            ->addIndex(['username'], [
                'name' => 'username',
                'unique' => true,
            ])
            ->create();
        $this->table('weekly_sales_targets', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => true,
            ])
            ->addColumn('product_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'id',
            ])
            ->addColumn('start_date', 'date', [
                'null' => false,
                'comment' => 'Monday of the week',
                'after' => 'product_id',
            ])
            ->addColumn('target_qty_kg', 'decimal', [
                'null' => false,
                'default' => '0.00',
                'precision' => 10,
                'scale' => 2,
                'after' => 'start_date',
            ])
            ->addColumn('created_at', 'timestamp', [
                'null' => true,
                'default' => 'current_timestamp()',
                'after' => 'target_qty_kg',
            ])
            ->addIndex(['product_id', 'start_date'], [
                'name' => 'unique_weekly_target',
                'unique' => true,
            ])
            ->addIndex(['start_date'], [
                'name' => 'start_date',
                'unique' => false,
            ])
            ->create();
        $this->table('wholesale_rules', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => true,
            ])
            ->addColumn('category_name', 'string', [
                'null' => false,
                'limit' => 100,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'id',
            ])
            ->addColumn('min_weight_kg', 'decimal', [
                'null' => true,
                'default' => '5.00',
                'precision' => 10,
                'scale' => 2,
                'after' => 'category_name',
            ])
            ->addColumn('discount_per_kg', 'decimal', [
                'null' => false,
                'precision' => 15,
                'scale' => 2,
                'after' => 'min_weight_kg',
            ])
            ->addColumn('is_active', 'boolean', [
                'null' => true,
                'default' => '1',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'discount_per_kg',
            ])
            ->addColumn('created_at', 'timestamp', [
                'null' => false,
                'default' => 'current_timestamp()',
                'after' => 'is_active',
            ])
            ->addColumn('updated_at', 'timestamp', [
                'null' => false,
                'default' => 'current_timestamp()',
                'update' => 'CURRENT_TIMESTAMP',
                'after' => 'created_at',
            ])
            ->create();
    }
}
