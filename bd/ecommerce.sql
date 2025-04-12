

USE ecommerce_project;

-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    admin ENUM('0', '1') DEFAULT '0'
);

-- Products Table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    material VARCHAR(100),
    warranty VARCHAR(50),
    weight VARCHAR(50),
    color VARCHAR(50)
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10,2),
    status ENUM('en-attente', 'terminee', 'annulee') DEFAULT 'en-attente',
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE order_items (
    order_id INT,
    product_id INT,
    quantity INT,
    price DECIMAL(10,2),
    PRIMARY KEY (order_id, product_id),
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE order_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    action VARCHAR(50),
    action_date DATETIME DEFAULT CURRENT_TIMESTAMP
);
-- panier table
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    UNIQUE KEY unique_cart_item (user_id, product_id), 
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Afficher les détails commande
DELIMITER $$
CREATE PROCEDURE DisplayOrderDetails(IN orderId INT)
BEGIN
    SELECT o.id, o.order_date, o.total, 
           p.name, oi.quantity, oi.price
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE o.id = orderId;
END$$
DELIMITER ;

-- Finaliser commande
DELIMITER $$
CREATE PROCEDURE FinalizeOrder(IN userId INT)
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE productId INT;
    DECLARE qty INT;
    DECLARE cur CURSOR FOR 
        SELECT c.product_id, c.quantity 
        FROM cart c 
        WHERE c.user_id = userId;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    START TRANSACTION;

    -- Vérification du stock
    CREATE TEMPORARY TABLE IF NOT EXISTS stock_check AS
    SELECT p.id, p.stock, c.quantity
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = userId;

    IF EXISTS(SELECT 1 FROM stock_check WHERE quantity > stock) THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Un produit n a pas assez de stock';
    END IF;

    -- Création de la commande
    INSERT INTO orders (user_id, total)
    SELECT userId, SUM(p.price * c.quantity)
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = userId;

    SET @orderId = LAST_INSERT_ID();

    -- Insertion des articles
    INSERT INTO order_items (order_id, product_id, quantity, price)
    SELECT @orderId, c.product_id, c.quantity, p.price
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = userId;

    -- Mise à jour manuelle du stock
    OPEN cur;
    read_loop: LOOP
        FETCH cur INTO productId, qty;
        IF done THEN
            LEAVE read_loop;
        END IF;
        UPDATE products SET stock = stock - qty WHERE id = productId;
    END LOOP;
    CLOSE cur;

    -- Nettoyage
    DELETE FROM cart WHERE user_id = userId;
    DROP TEMPORARY TABLE stock_check;

    COMMIT;
END$$
DELIMITER ;
-- Historique commandes
DELIMITER $$
CREATE PROCEDURE DisplayOrderHistory(IN userId INT)
BEGIN
    SELECT id, order_date, total, status
    FROM orders
    WHERE user_id = userId
    ORDER BY order_date DESC;
END$$
DELIMITER ;

-- Mise à jour stock
DELIMITER $$
CREATE TRIGGER UpdateStockAfterOrder
AFTER INSERT ON order_items
FOR EACH ROW
BEGIN
    UPDATE products 
    SET stock = stock - NEW.quantity 
    WHERE id = NEW.product_id;
END$$
DELIMITER ;

-- Vérification stock
DELIMITER $$
CREATE TRIGGER PreventOverOrder
BEFORE INSERT ON cart  
FOR EACH ROW
BEGIN
    DECLARE available_stock INT;
    
    SELECT stock INTO available_stock 
    FROM products 
    WHERE id = NEW.product_id;
    
    IF NEW.quantity > available_stock THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Quantité en stock insuffisante';
    END IF;
END$$
DELIMITER ;

-- Annulation commande
DELIMITER $$
CREATE TRIGGER RestoreStockOnCancel
AFTER UPDATE ON orders
FOR EACH ROW
BEGIN
    IF NEW.status = 'annulee' AND OLD.status != 'annulee' THEN
        UPDATE products p
        JOIN order_items oi ON p.id = oi.product_id
        SET p.stock = p.stock + oi.quantity
        WHERE oi.order_id = NEW.id;
        
        INSERT INTO order_history (order_id, action)
        VALUES (NEW.id, 'Annulation client');
    END IF;
END$$
DELIMITER ;
-- cancel order
DELIMITER $$
CREATE PROCEDURE CancelOrder(IN orderId INT, IN userId INT)
BEGIN
    DECLARE currentStatus VARCHAR(20);
    
    SELECT status INTO currentStatus 
    FROM orders 
    WHERE id = orderId AND user_id = userId;
    
    IF currentStatus IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Commande non trouvée';
    ELSEIF currentStatus != 'en-attente' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Annulation impossible pour ce statut';
    ELSE
        UPDATE orders 
        SET status = 'annulee' 
        WHERE id = orderId AND user_id = userId;
    END IF;
END$$
DELIMITER ;