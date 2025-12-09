<!-- Aba de Pagamentos -->
<div id="payments" class="tab-content section">
    <h2>Gerenciar Pagamentos</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Usuário</th>
                <th>Produto</th>
                <th>Data</th>
                <th>Valor</th>
                <th>Status</th>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($payments as $payment): ?>
            <tr>
                <form action="handle_payment.php" method="POST" style="display: contents;">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                    <td><?php echo $payment['id']; ?></td>
                    <td><?php echo htmlspecialchars($payment['username']); ?></td>
                    <td><?php echo htmlspecialchars($payment['product_name']); ?></td>
                    <td><?php echo $payment['payment_date'] ? date("d/m/Y H:i", strtotime($payment['payment_date'])) : 'N/A'; ?></td>
                    <td>R$ <?php echo number_format($payment['amount'], 2, ',', '.'); ?></td>
                    <td><span class="status status-info"><?php echo htmlspecialchars($payment['status']); ?></span></td>
                    <td>
                        <select name="new_status" style="width: auto; margin-right: 10px;">
                            <option value="<?php echo $payment['status']; ?>" selected><?php echo ucfirst($payment['status']); ?></option>
                            <option value="initiated">Iniciado</option><option value="pending">Pendente</option><option value="approved">Aprovado</option><option value="rejected">Rejeitado</option><option value="cancelled">Cancelado</option><option value="refunded">Devolvido</option></select>
                        <button type="submit" class="btn-save">Salvar</button>
                        <?php if ($payment['status'] === 'approved'): ?>
                            <button type="button" class="btn-notify" style="margin-left: 10px;" onclick="openPaymentEmailModal('<?php echo $payment['id']; ?>')">Confirmar Pagamento</button>
                        <?php endif; ?>
                    </td>
                </form>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
