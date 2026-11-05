-- REVIEW ONLY. Apply only after explicit client approval and verified local backup.
-- Sandbox-only; additive and rerunnable. Never included by SchemaManager.
CREATE TABLE IF NOT EXISTS finance_orders (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 reference VARCHAR(48) NOT NULL UNIQUE,
 buyer_id INT NOT NULL, school_id INT NULL, pack_id INT NULL, creator_id INT NULL,
 kind VARCHAR(24) NOT NULL, buyer_name VARCHAR(255) NOT NULL, item_name VARCHAR(255) NOT NULL,
 amount BIGINT NOT NULL, currency CHAR(3) NOT NULL DEFAULT 'PHP', test_mode TINYINT NOT NULL DEFAULT 1,
 status VARCHAR(24) NOT NULL DEFAULT 'PENDING', access_status VARCHAR(32) NOT NULL DEFAULT 'PENDING',
 checkout_id VARCHAR(100) CHARACTER SET ascii COLLATE ascii_bin NULL UNIQUE, checkout_url TEXT NULL, checkout_state VARCHAR(24) NOT NULL DEFAULT 'NEW',
 payment_id VARCHAR(100) CHARACTER SET ascii COLLATE ascii_bin NULL UNIQUE, paid_at DATETIME NULL,
 created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL,
 INDEX (buyer_id, created_at), INDEX (school_id, kind),
 CHECK (amount > 0), CHECK (currency = 'PHP'), CHECK (test_mode = 1),
 CHECK (kind IN ('institutional','subscription','pack'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS finance_events (
 event_id VARCHAR(100) CHARACTER SET ascii COLLATE ascii_bin PRIMARY KEY, body_hash CHAR(64) NOT NULL,
 event_type VARCHAR(80) NOT NULL, received_at DATETIME NOT NULL,
 order_id BIGINT UNSIGNED NULL,
 FOREIGN KEY (order_id) REFERENCES finance_orders(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS finance_pack_pricing (
 pack_id INT PRIMARY KEY, creator_id INT NOT NULL,
 proposed_amount BIGINT NOT NULL DEFAULT 0, approved_amount BIGINT NULL,
 review_status VARCHAR(24) NOT NULL DEFAULT 'PENDING', reviewed_by INT NULL,
 reviewed_at DATETIME NULL, updated_at DATETIME NOT NULL,
 CHECK (proposed_amount = 0 OR proposed_amount BETWEEN 5900 AND 9900),
 CHECK (approved_amount IS NULL OR approved_amount = 0 OR approved_amount BETWEEN 5900 AND 9900)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS finance_entitlements (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 order_id BIGINT UNSIGNED NOT NULL UNIQUE, buyer_id INT NOT NULL,
 school_id INT NULL, pack_id INT NULL, kind VARCHAR(24) NOT NULL,
 starts_at DATETIME NOT NULL, ends_at DATETIME NULL,
 state VARCHAR(24) NOT NULL DEFAULT 'ACTIVE', activated_by INT NULL, activated_at DATETIME NULL,
 INDEX (buyer_id, kind, starts_at, ends_at), INDEX (school_id, kind, ends_at),
 FOREIGN KEY (order_id) REFERENCES finance_orders(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS finance_usage (
 period_id BIGINT UNSIGNED NOT NULL, pack_id INT NOT NULL, creator_id INT NOT NULL,
 used_at DATETIME NOT NULL, PRIMARY KEY (period_id, pack_id),
 FOREIGN KEY (period_id) REFERENCES finance_entitlements(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS finance_accounting (
 order_id BIGINT UNSIGNED PRIMARY KEY, gross BIGINT NOT NULL,
 fee BIGINT NULL, net BIGINT NULL, signed_share BIGINT NULL, creator_pool BIGINT NULL,
 undistributed BIGINT NULL, rounding_numerator INT NOT NULL DEFAULT 0,
 fee_basis VARCHAR(255) NULL, status VARCHAR(24) NOT NULL DEFAULT 'PENDING_FEES',
 pool_closed_at DATETIME NULL, updated_at DATETIME NOT NULL,
 FOREIGN KEY (order_id) REFERENCES finance_orders(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS finance_refunds (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, order_id BIGINT UNSIGNED NOT NULL,
 amount BIGINT NOT NULL, reason VARCHAR(255) NOT NULL,
 status VARCHAR(32) NOT NULL DEFAULT 'REQUESTED', requested_by INT NOT NULL,
 reviewed_by INT NULL, decision_note VARCHAR(255) NULL,
 provider_id VARCHAR(100) CHARACTER SET ascii COLLATE ascii_bin NULL UNIQUE, provider_status VARCHAR(24) NULL,
 fee_returned BIGINT NULL, signed_adjustment BIGINT NULL, creator_adjustment BIGINT NULL,
 undistributed_adjustment BIGINT NULL, accounting_status VARCHAR(32) NOT NULL DEFAULT 'PENDING', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL,
 FOREIGN KEY (order_id) REFERENCES finance_orders(id), CHECK(amount > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS finance_earnings (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, order_id BIGINT UNSIGNED NOT NULL,
 creator_id INT NOT NULL, amount BIGINT NOT NULL,
 basis VARCHAR(255) NOT NULL, refund_id BIGINT UNSIGNED NULL,
 created_at DATETIME NOT NULL, UNIQUE (order_id, creator_id, basis),
 FOREIGN KEY (order_id) REFERENCES finance_orders(id),
 FOREIGN KEY (refund_id) REFERENCES finance_refunds(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS finance_payouts (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, creator_id INT NOT NULL,
 amount BIGINT NOT NULL, method VARCHAR(100) NOT NULL,
 reference VARCHAR(100) NOT NULL UNIQUE, transferred_at DATETIME NOT NULL,
 status VARCHAR(24) NOT NULL DEFAULT 'CONFIRMED', recorded_by INT NOT NULL,
 created_at DATETIME NOT NULL, CHECK(amount > 0), CHECK(status = 'CONFIRMED')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS finance_payout_items (
 payout_id BIGINT UNSIGNED NOT NULL, earning_id BIGINT UNSIGNED NOT NULL,
 PRIMARY KEY (payout_id, earning_id), UNIQUE (earning_id),
 FOREIGN KEY (payout_id) REFERENCES finance_payouts(id),
 FOREIGN KEY (earning_id) REFERENCES finance_earnings(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS finance_audit (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, order_id BIGINT UNSIGNED NULL,
 actor_id INT NULL, action VARCHAR(64) NOT NULL, detail VARCHAR(500) NOT NULL,
 created_at DATETIME NOT NULL, FOREIGN KEY (order_id) REFERENCES finance_orders(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Append-only provider evidence; no raw billing or credential payloads.
CREATE TABLE IF NOT EXISTS finance_fee_evidence (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, order_id BIGINT UNSIGNED NOT NULL,
 payment_id VARCHAR(100) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
 evidence_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
 payload_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NULL,
 source VARCHAR(255) NOT NULL, status VARCHAR(24) NOT NULL,
 fee BIGINT NULL, net BIGINT NULL, balance_transaction_id VARCHAR(100) NULL,
 provider_updated_at BIGINT NULL, reason VARCHAR(255) NOT NULL, retrieved_at DATETIME NOT NULL,
 UNIQUE(order_id,evidence_hash), FOREIGN KEY(order_id) REFERENCES finance_orders(id),
 CHECK(fee IS NULL OR fee >= 0), CHECK(net IS NULL OR net >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS finance_media_assets (
 token CHAR(64) CHARACTER SET ascii COLLATE ascii_bin PRIMARY KEY,
 owner_id INT NOT NULL, original_name VARCHAR(255) NOT NULL,
 mime_type VARCHAR(100) NOT NULL, byte_size BIGINT NOT NULL, sha256 CHAR(64) NOT NULL,
 created_at DATETIME NOT NULL, CHECK(byte_size > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS finance_pack_media (
 pack_id INT NOT NULL, token CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
 PRIMARY KEY(pack_id,token), FOREIGN KEY(token) REFERENCES finance_media_assets(token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
