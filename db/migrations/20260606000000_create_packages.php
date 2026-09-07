<?php
use Phinx\Migration\AbstractMigration;

class CreatePackages extends AbstractMigration
{
    public function up()
    {
        // 1. Add columns to products
        $products = $this->table('products');
        if (!$products->hasColumn('is_package')) {
            $products->addColumn('is_package', 'integer', ['limit' => 1, 'default' => 0, 'after' => 'created_at']);
        }
        if (!$products->hasColumn('status')) {
            $products->addColumn('status', 'enum', ['values' => ['active', 'inactive'], 'default' => 'active', 'after' => 'is_package']);
        }
        $products->save();

        // 2. Create package_items table
        $table = $this->table('package_items');
        $table->addColumn('package_id', 'integer')
              ->addColumn('product_id', 'integer')
              ->addColumn('quantity', 'decimal', ['precision' => 10, 'scale' => 2])
              ->addForeignKey('package_id', 'products', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
              ->addForeignKey('product_id', 'products', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
              ->create();
    }

    public function down()
    {
        $this->table('package_items')->drop()->save();

        $products = $this->table('products');
        if ($products->hasColumn('is_package')) {
            $products->removeColumn('is_package');
        }
        if ($products->hasColumn('status')) {
            $products->removeColumn('status');
        }
        $products->save();
    }
}
