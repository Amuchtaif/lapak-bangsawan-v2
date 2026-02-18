<?php
use Phinx\Migration\AbstractMigration;

class CreatePartners extends AbstractMigration
{
    public function up()
    {
        // 1. Create Partners Table
        $table = $this->table('partners');
        $table->addColumn('name', 'string', ['limit' => 100])
              ->addColumn('contact', 'string', ['limit' => 50, 'null' => true])
              ->addColumn('address', 'text', ['null' => true])
              ->addColumn('status', 'enum', ['values' => ['active', 'inactive'], 'default' => 'active'])
              ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
              ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
              ->create();

        // 2. Add Columns to Products Table
        $products = $this->table('products');
        
        if (!$products->hasColumn('partner_id')) {
            $products->addColumn('partner_id', 'integer', ['null' => true, 'after' => 'category_id'])
                     ->addForeignKey('partner_id', 'partners', 'id', ['delete'=> 'SET_NULL', 'update'=> 'NO_ACTION']);
        }

        if (!$products->hasColumn('product_type')) {
            $products->addColumn('product_type', 'enum', ['values' => ['internal', 'consignment'], 'default' => 'internal', 'after' => 'partner_id']);
        }

        // Ensure buy_price exists (if not already present)
        if (!$products->hasColumn('buy_price')) {
             $products->addColumn('buy_price', 'decimal', ['precision' => 15, 'scale' => 2, 'default' => 0, 'after' => 'price']);
        }

        $products->save();
    }

    public function down()
    {
        $products = $this->table('products');
        if ($products->hasColumn('buy_price')) {
            // Be careful removing buy_price if it existed before! 
            // I'll assume for rollback of *this* migration we only remove if we added it, but Phinx doesn't track that granularity easily.
            // I'll leave buy_price alone in down() to be safe.
        }
        
        if ($products->hasColumn('product_type')) {
            $products->removeColumn('product_type');
        }

        if ($products->hasForeignKey('partner_id')) {
            $products->dropForeignKey('partner_id');
        }

        if ($products->hasColumn('partner_id')) {
            $products->removeColumn('partner_id');
        }
        
        $products->save();

        $this->table('partners')->drop()->save();
    }
}
