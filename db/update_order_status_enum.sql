ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'confirmed', 'ready_to_ship', 'shipped', 'delivered', 'completed', 'cancelled') DEFAULT 'pending';
