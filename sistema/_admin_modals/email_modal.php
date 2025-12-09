<!-- Modal de Envio de E-mail -->
<div id="emailModal" class="modal">
    <div class="modal-content">
        <span class="close-button">&times;</span>
        <h3>Enviar E-mail para Usuário</h3>
        <form action="handle_email.php" method="GET">
            <input type="hidden" name="action" value="send_custom">
            <input type="hidden" id="modalUserId" name="id" value="">
            <div class="form-group">
                <label for="template">Modelo de E-mail:</label>
                <select id="template" name="template" class="form-control" required>
                    <option value="new_system_welcome">Boas-vindas ao Novo Sistema</option>
                    <option value="new_login_method">Novo Método de Login</option>
                    <!-- Outros templates podem ser adicionados aqui -->
                </select>
            </div>
            <button type="submit" class="btn btn-save">Enviar</button>
        </form>
    </div>
</div>
