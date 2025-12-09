<!-- Modal de E-mail de Pagamento -->
<div id="paymentEmailModal" class="modal">
    <div class="modal-content">
        <span class="close-button">&times;</span>
        <h3>Confirmar Pagamento</h3>
        <form action="handle_email.php" method="GET">
            <input type="hidden" name="action" value="confirm_payment">
            <input type="hidden" id="modalPaymentId" name="id" value="">
            <div class="form-group">
                <label for="payment_template">Modelo de E-mail:</label>
                <select id="payment_template" name="template" class="form-control" required>
                    <option value="payment_confirmation">Confirmação de Pagamento</option>
                </select>
            </div>
            <button type="submit" class="btn btn-save">Enviar Confirmação</button>
        </form>
    </div>
</div>
