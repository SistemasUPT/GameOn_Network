USE gameon;

CREATE TABLE IF NOT EXISTS password_recovery_tokens (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_type enum('deportista','instalacion') NOT NULL,
  user_id int(11) NOT NULL,
  email varchar(100) NOT NULL,
  token varchar(255) NOT NULL,
  expires_at datetime NOT NULL,
  used tinyint(1) NOT NULL DEFAULT 0,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  used_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY token (token),
  KEY idx_user_type_id (user_type, user_id),
  KEY idx_token_expires (token, expires_at),
  KEY idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;