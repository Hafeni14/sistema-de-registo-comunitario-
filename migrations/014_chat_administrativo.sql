/* Migração 014: Chat privado entre administradores e responsáveis. canal_chave = 'nacional' ou 'm<ID do município>' (ex: m3) */
CREATE TABLE IF NOT EXISTS chat_mensagens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    canal_chave VARCHAR(20) NOT NULL,
    autor_id INT NOT NULL,
    texto TEXT NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (autor_id) REFERENCES utilizadores(id) ON DELETE CASCADE,
    INDEX idx_chat_canal (canal_chave, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/* Última mensagem lida por cada utilizador em cada canal (para o contador de não lidas) */
CREATE TABLE IF NOT EXISTS chat_leituras (
    utilizador_id INT NOT NULL,
    canal_chave VARCHAR(20) NOT NULL,
    ultima_id INT NOT NULL DEFAULT 0,
    PRIMARY KEY (utilizador_id, canal_chave),
    FOREIGN KEY (utilizador_id) REFERENCES utilizadores(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
