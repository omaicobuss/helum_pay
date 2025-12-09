<!-- Modal de E-mail de Assinatura -->
<div id="subscriptionEmailModal" class="modal">
    <div class="modal-content">
        <span class="close-button">&times;</span>
        <h3>Notificar Assinatura</h3>
        <form action="handle_email.php" method="GET">
            <input type="hidden" name="action" value="notify_subscription">
            <input type="hidden" id="modalSubscriptionId" name="id" value="">
            <div class="form-group">
                <label for="sub_template">Modelo de E-mail:</label>
                <select id="sub_template" name="template" class="form-control" required>
                    <option value="invoice_available">Fatura Disponível</option>
                    <option value="invoice_due">Lembrete de Vencimento</option>
                </select>
            </div>
            <button type="submit" class="btn btn-save">Enviar Notificação</button>
        </form>
    </div>
</div>
