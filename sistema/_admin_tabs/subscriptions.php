<!-- Aba de Assinaturas -->
<div id="subscriptions" class="tab-content section">
    <h2>Gerenciar Assinaturas</h2>
    <table>
        <thead><tr><th>ID</th><th>Usuário</th><th>Produto</th><th>Próx. Vencimento</th><th>Status</th><th>Notas</th><th>Ações</th></tr></thead>
        <tbody>
            <?php foreach($subscriptions as $sub): ?>
            <tr>
                <td><?php echo $sub['id']; ?></td>
                <td><?php echo htmlspecialchars($sub['username']); ?></td>
                <td><?php echo htmlspecialchars($sub['product_name']); ?></td>
                <td><?php echo date("d/m/Y", strtotime($sub['next_due_date'])); ?></td>
                <td><?php echo htmlspecialchars($sub['status']); ?></td>
                <td style="white-space: pre-wrap;"><?php echo htmlspecialchars($sub['notes']); ?></td>
                <td>
                    <button type="button" class="btn-notify" onclick="openSubscriptionEmailModal('<?php echo $sub['id']; ?>')">Notificar</button>
                    <a href="subscription_edit.php?id=<?php echo $sub['id']; ?>" class="btn-edit">Editar</a> 
                    <a href="handle_subscription.php?action=delete&id=<?php echo $sub['id']; ?>" class="btn-delete" onclick="return confirmDelete();">Excluir</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <h3>Adicionar Nova Assinatura</h3>
    <form action="handle_subscription.php" method="POST">
        <input type="hidden" name="action" value="create">
        <div class="form-grid">
            <div class="form-group"><label>Usuário</label><select name="user_id" required><?php foreach($users as $user) echo "<option value='{$user['id']}'>".htmlspecialchars($user['username'])."</option>"; ?></select></div>
            <div class="form-group"><label>Produto</label><select name="product_id" required><?php foreach($products as $product) echo "<option value='{$product['id']}'>".htmlspecialchars($product['name'])."</option>"; ?></select></div>
            <div class="form-group"><label>Data de Início</label><input type="date" name="start_date" value="<?php echo date('Y-m-d'); ?>" required></div>
            <div class="form-group"><label>Próximo Vencimento</label><input type="date" name="next_due_date" value="<?php echo date('Y-m-d', strtotime('+1 month')); ?>" required></div>
            <div class="form-group"><label>Status</label><select name="status" required><option value="active">Ativa</option><option value="paid">Paga</option><option value="canceled">Cancelada</option></select></div>
        </div>
        <div class="form-group">
            <label>Notas Complementares</label>
            <textarea name="notes" rows="3"></textarea>
        </div>
        <button type="submit" class="btn btn-save">Adicionar Assinatura</button>
    </form>
</div>
