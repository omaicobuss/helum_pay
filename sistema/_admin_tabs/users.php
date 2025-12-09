<!-- Aba de Usuários -->
<div id="users" class="tab-content active section">
    <h2>Gerenciar Usuários</h2>
    <table>
        <thead><tr><th>ID</th><th>Usuário</th><th>Nome</th><th>E-mail</th><th>Documento</th><th>Perfil</th><th>Ações</th></tr></thead>
        <tbody>
            <?php foreach($users as $user): ?>
            <tr>
                <form action="update_role.php" method="POST" style="display: contents;">
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['document']); ?> (<?php echo htmlspecialchars(strtoupper($user['user_type'])); ?>)</td>
                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                    <td><select name="role"><option value="cliente" <?php echo ($user['role'] == 'cliente') ? 'selected' : ''; ?>>Cliente</option><option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option></select></td>
                    <td>
                        <button type="submit" class="btn-save">Salvar Perfil</button>
                        <a href="user_edit.php?id=<?php echo $user['id']; ?>" class="btn-edit" style="margin-left: 5px;">Editar Usuário</a>
                        <button type="button" class="btn-notify" style="margin-left: 5px;" onclick="openEmailModal('<?php echo $user['id']; ?>')">Enviar E-mail</button>
                    </td>
                </form>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
